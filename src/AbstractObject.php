<?php
// AbstractObject.php

namespace Dormilich\APNIC;

use ArrayAccess;
use Countable;
use Iterator;
use JsonSerializable;
use Dormilich\APNIC\Exceptions\IncompleteRPSLObjectException;
use Dormilich\APNIC\Exceptions\InvalidAttributeException;
use Dormilich\APNIC\Exceptions\InvalidDataTypeException;
use Dormilich\APNIC\Exceptions\InvalidValueException;

/**
 * The prototype for every RPSL object class. 
 * 
 * A child class must
 *  1) define a primary key and type (which are usually the same)
 *  2) set the class name to type’s name using camel case 
 *      (e.g. domain => Domain, aut-num => AutNum)
 *  3) define the attributes for this RPSL object
 * 
 * A child class should
 *  - set the primary key on instantiation
 *  - set a "VERSION" constant
 */
abstract class AbstractObject implements ObjectInterface, ArrayAccess, Iterator, Countable
{
    /**
     * The type of the object.
     * @var string
     */
    private $type;

    /**
     * The primary lookup key of the object.
     * @var string[]
     */
    private $primaryKey;

    /**
     * Name-indexed array of attributes.
     * @var AttributeInterface[] 
     */
    private $attributes = [];

    /**
     * Name-indexed array of auto-generated attributes, which should not be set by the user.
     * @var AttributeInterface[] 
     */
    private $generated = [];

// --- OBJECT SETUP ---------------

    /**
     * Define the attributes for this object according to the RPSL DB docs.
     * 
     * @return void
     */
    abstract protected function init();

    /**
     * Set name(s) and value(s) of the primary key.
     * 
     * @param array $keys Key name vs. key value.
     * @return self
     */
    protected function setKey( array $keys )
    {
        if ( count( $keys ) > 0 ) {
            $this->primaryKey = array_keys( $keys );
 
            foreach ( $keys as $key => $value) {
                $this->attr( $key )->setValue( $value );
            }
        }

        return $this;
    }

    /**
     * Get the name of the current RPSL object.
     * 
     * @return string RPSL object name.
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set the name of the object type.
     * 
     * @param string $name The name of the primary key.
     * @return self
     */
    protected function setType( $name )
    {
        $this->type = $name;

        return $this;
    }

    /**
     * Create an attribute and add it to the attribute list. If a public 
     * method of a matching name exists, it is registered as callback.
     * 
     * @param string $name Name of the attribute.
     * @param boolean $required If the attribute is mandatory.
     * @param boolean $multiple If the attribute allows multiple values.
     * @return AttributeInterface
     */
    protected function create( $name, $required, $multiple )
    {
        $attr = new Attribute( $name, $required, $multiple );

        $this->attributes[ $attr->getName() ] = $attr;

        $method = $this->name2method( $name );
        // we must only test public methods
        if ( method_exists( get_class( $this ), $method ) ) {
            $attr->apply( [ $this, $method ] );
        }

        return $attr;
    }

    /**
     * Set a generated attribute. These attributes are not serialised. Its values 
     * are only accessible from the object itself. Generated attributes are always 
     * optional.
     * 
     * @param string $name Name of the attribute.
     * @param boolean $multiple If the attribute allows multiple values.
     * @return AttributeInterface
     */
    protected function setGeneratedAttribute( $name, $multiple )
    {
        $attr = new Attribute( $name, AttributeInterface::OPTIONAL, $multiple );

        $this->generated[ $attr->getName() ] = $attr;

        return $attr;
    }

    /**
     * Convert an attribute name into a callable method.
     * 
     * @param string $name 
     * @return string
     */
    private function name2method( $name )
    {
        return preg_replace_callback( '/-([a-z])/', function ( $matches ) {
            return strtoupper( $matches[ 1 ] );
        }, $name );
    }

// --- DATA ACCESS ----------------

    /**
     * Get the value of the attribute(s) defined as primary key.
     * 
     * @return string
     */
    public function getHandle()
    {
        return array_reduce( $this->primaryKey, function ( $value, $key ) {
            return $value . $this->attr( $key )->getValue();
        }, '' );
    }

    /**
     * Useful helper in form templates. This needs to be an array since Route / 
     * Route6 have a composite primary key. 
     * 
     * @return string[]
     */
    public function getPrimaryKeyNames()
    {
        return $this->primaryKey;
    }

    /**
     * Get the keys for the attributes (no matter whether they’re defined or not).
     * 
     * @return array
     */
    public function getAttributeNames()
    {
        return array_keys( $this->attributes + $this->generated );
    }

    /**
     * Check if a specific attribute exists in this object.
     * 
     * @param string $name Name of the attribute.
     * @return boolean Whether the attribute exists
     */
    public function has( $name )
    {
        return isset( $this->attributes[ $name ] ) 
            or isset( $this->generated[ $name ] ); 
    }

    /**
     * Get an attribute specified by name.
     * 
     * @param string $name Name of the attribute.
     * @return AttributeInterface Attribute object.
     * @throws InvalidAttributeException Invalid argument name.
     */
    public function attr( $name )
    {
        if ( isset( $this->attributes[ $name ] ) ) {
            return $this->attributes[ $name ];
        }
        if ( isset( $this->generated[ $name ] ) ) {
            return $this->generated[ $name ];
        }

        $msg = sprintf( 'Attribute "%s" is not defined for the %s object.', 
            $name, strtoupper( $this->type ) );
        throw new InvalidAttributeException( $msg );
    }

    /**
     * Get an attribute’s value(s).
     * 
     * @param string $name Attribute name.
     * @return string[]|string|NULL Attribute value(s).
     */
    public function get( $name )
    {
        return $this->attr( $name )->getValue();
    }

    /**
     * Set an attribute’s value(s).
     * 
     * @param string $name Attribute name.
     * @param mixed $value Attibute value(s).
     * @return self
     */
    public function set( $name, $value )
    {
        $this->attr( $name )->setValue( $value );

        return $this;
    }

    /**
     * Add a value to an attribute.
     * 
     * @param string $name Attribute name.
     * @param mixed $value Attibute value(s).
     * @return self
     */
    public function add( $name, $value )
    {
        $this->attr( $name )->addValue( $value );

        return $this;
    }

    /**
     * Output the object as a textual list of its defined attributes.
     * 
     * @return string
     */
    public function __toString()
    {
        $max = 3 + max( array_map( 'strlen', $this->getAttributeNames() ) );

        return array_reduce( $this->toArray(), function ( $output, array $item ) use ( $max ) {
            return $output . sprintf( "%-{$max}s %s\n", $item[ 'name' ] . ':', $item[ 'value' ] );
        }, '' );
    }

// --- INTERFACES -----------------

    /**
     * Checks if an Attribute exists, but not if it is populated.
     * 
     * @see http://php.net/ArrayAccess
     * @param mixed $offset The array key.
     * @return boolean
     */
    public function offsetExists( $offset )
    {
        return $this->has( $offset );
    }

    /**
     * Get the value of the specified Attribute.
     * 
     * @see http://php.net/ArrayAccess
     * @param string $offset Attribute name.
     * @return string|array Attribute value.
     */
    public function offsetGet( $offset )
    {
        if ( $this->has( $offset ) ) {
            return $this->attr( $offset )->getValue();
        }

        return NULL;
    }

    /**
     * Set an Attibute’s value. Existing values will be replaced. 
     * For adding values use AbstractObject::add().
     * 
     * @see http://php.net/ArrayAccess
     * @param string $offset Attribute name.
     * @param type $value New Attribute value.
     * @return void
     */
    public function offsetSet( $offset, $value )
    {
        if ( $this->has( $offset ) ) {
            $this->attr( $offset )->setValue( $value );
        }
    }

    /**
     * Reset an Attribute’s value.
     * 
     * @see http://php.net/ArrayAccess
     * @param string $offset Attribute name.
     * @return void
     */
    public function offsetUnset( $offset )
    {
        if ( $this->has( $offset ) ) {
            $this->attr( $offset )->setValue( NULL );
        }
    }

    /**
     * Return the number of defined Attributes.
     * 
     * @see http://php.net/Countable
     * @return integer
     */
    public function count()
    {
        return count( $this->getDefinedAttributes() );
    }

    /**
     * @see http://php.net/Iterator
     * @return void
     */
    public function rewind()
    {
        reset( $this->attributes );
    }
    
    /**
     * @see http://php.net/Iterator
     * @return string
     */
    public function current()
    {
        return current( $this->attributes );
    }
    
    /**
     * @see http://php.net/Iterator
     * @return integer
     */
    public function key()
    {
        return key( $this->attributes );
    }
    
    /**
     * @see http://php.net/Iterator
     * @return void
     */
    public function next()
    {
        next( $this->attributes );
    }
    
    /**
     * @see http://php.net/Iterator
     * @return boolean
     */
    public function valid()
    {
        return NULL !== key( $this->attributes );
    }

    /**
     * Convert object into an array, where all objects are converted into their 
     * array equivalent.
     * 
     * @return array
     */
    public function toArray()
    {
        $json  = json_encode($this->jsonAttributes());
        $array = json_decode($json, true);

        return $array;
    }

    /**
     * Get the array representation of all attributes that are populated with 
     * values. Generated attributes are ignored since they are always generated 
     * by the APNIC DB.
     * 
     * @return array JSON compatible array.
     */
    protected function jsonAttributes()
    {
        $defined = array_filter( $this->attributes, function ( AttributeInterface $attr ) {
            return $attr->isDefined();
        } );

        $json = array_map( function ( JsonSerializable $attr ) {
            return $attr->jsonSerialize();
        }, $defined );

        return array_reduce( $json, 'array_merge', [] );
    }

// --- VALIDATION HELPERS ---------

    /**
     * Filter all attributes that are defined.
     * 
     * @return array
     */
    protected function getDefinedAttributes()
    {
        return array_filter( $this->attributes, function ( AttributeInterface $attr ) {
            return $attr->isDefined();
        });
    }

    /**
     * Check if any of the required Attributes is undefined.
     * 
     * @return boolean
     */
    public function isValid()
    {
        return array_reduce( $this->attributes, function ( $bool, AttributeInterface $attr ) {
            return $bool and ( ! $attr->isRequired() or $attr->isDefined() );
        }, true);
    }

    /**
     * Validation function for email addresses.
     * 
     * @param string $email 
     * @return string
     * @throws InvalidValueException
     */
    public function validateEmail( $email )
    {
        if ( filter_var( $email, \FILTER_VALIDATE_EMAIL ) ) {
            return $email;
        }

        throw new InvalidValueException( 'Invalid email address' );
    }

    /**
     * Validation function for phone & fax numbers.
     * 
     * @param string $phone 
     * @return string
     * @throws InvalidValueException
     */
    public function validatePhone( $phone )
    {
        if ( preg_match( '~^\+[1-9]\d*([ .-]\d+)*( ext\. \d+)?$~', $phone ) ) {
            return $phone;
        }

        throw new InvalidValueException( 'Invalid phone/fax number' );
    }

    /**
     * Validation function for RPSL object handles.
     * 
     * @param string $handle 
     * @return string
     * @throws InvalidValueException
     */
    public function validateHandle( $handle )
    {
        if ( ! preg_match( '~[^A-Za-z0-9-]~', $handle ) ) {
            return strtoupper( trim( $handle, '-' ) );
        }

        throw new InvalidValueException( 'Invalid RPSL object handle' );
    }

// --- COMMON VALIDATORS ----------

    /**
     * Helper callback for the 'country' attribute. The input is valid for a 
     * 2-letter coutry code.
     * 
     * @param string $input 
     * @return string
     * @throws InvalidValueException
     */
    public function country( $input )
    {
        if ( preg_match( '~^[A-Za-z]{2}$~', $input ) ) {
            return strtoupper( $input );
        }

        throw new InvalidValueException( 'Invalid country code' );
    }

    /**
     * Helper callback for the 'notify' attribute. The input is valid for a 
     * syntactically correct email address.
     * 
     * @param string $input 
     * @return string
     * @throws InvalidValueException
     */
    public function notify( $input )
    {
        return $this->validateEmail( $input );
    }

    /**
     * Helper callback for the 'admin-c' attribute. 
     * 
     * @param string $input 
     * @return string
     * @throws InvalidValueException
     */
    public function adminC( $input )
    {
        return $this->validateHandle( $input );
    }

    /**
     * Helper callback for the 'tech-c' attribute. 
     * 
     * @param string $input 
     * @return string
     * @throws InvalidValueException
     */
    public function techC( $input )
    {
        return $this->validateHandle( $input );
    }

    /**
     * Helper callback for the 'mnt-by' attribute. The input is valid for a 
     * Mntner object or an RPSL object handle.
     * 
     * @param string $input 
     * @return string
     * @throws InvalidValueException
     */
    public function mntBy( $input )
    {
        return $this->validateHandle( $input );
    }

    // there are various other mnt-* and *-c attributes, but they or their 
    // objects are hardly used so it’s not yet worth the effort
}
