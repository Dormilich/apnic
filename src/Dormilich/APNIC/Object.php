<?php
// Object.php

namespace Dormilich\APNIC;

use Dormilich\APNIC\Exceptions\IncompleteRPSLObjectException;
use Dormilich\APNIC\Exceptions\InvalidAttributeException;
use Dormilich\APNIC\Exceptions\InvalidDataTypeException;
use Dormilich\APNIC\Exceptions\InvalidValueException;

/**
 * The prototype for every RPSL object class. 
 * 
 * A child class must
 *  1) define a primary key and type (which are usually the same)
 *  2) set the class name to thats name using camel case (e.g. domain => Domain, aut-num => AutNum)
 *  3) define the attributes for this RPSL object
 * 
 * A child class should
 *  - set the primary key on instantiation
 *  - set a "VERSION" constant
 */
abstract class Object implements ObjectInterface, ArrayInterface, \ArrayAccess, \IteratorAggregate, \Countable
{
    /**
     * The type of the object.
     * @var string
     */
    private $type = NULL;

    /**
     * The primary lookup key of the object.
     * @var string
     */
    private $primaryKey = NULL;

    /**
     * Name-indexed array of attributes.
     * @var array 
     */
    private $attributes = [];

    /**
     * Name-indexed array of auto-generated attributes, which should not be set by the user.
     * @var array 
     */
    private $generated  = [];

// --- OBJECT SETUP ---------------

    /**
     * Define the attributes for this object according to the RPSL DB docs.
     * 
     * @return void
     */
    abstract protected function init();

    /**
     * Set the name of the primary key.
     * 
     * @param string $value The name of the primary key.
     * @return self
     * @throws LogicException Value is empty
     */
    protected function setKey( $name, $value )
    {
        if ( NULL === $this->primaryKey ) {
            $this->primaryKey = $name;
            $this->getAttribute( $name )->setValue( $value );
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
     * @throws LogicException Value is empty
     */
    protected function setType( $name )
    {
        if ( NULL === $this->type ) {
            $this->type = $name;
        }

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
     * Get the value of the attribute defined as primary key.
     * 
     * @return string
     */
    public function getPrimaryKey()
    {
        return $this->getAttribute( $this->primaryKey )->getValue();
    }

    /**
     * Get the name of the PK via function. 
     * Conformance function to overwrite in the Dummy class, 
     * which can not use a constant to store the PK.
     * 
     * @return string
     */
    public function getPrimaryKeyName()
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
     * Get an attribute specified by name.
     * 
     * @param string $name Name of the attribute.
     * @return Attribute Attribute object.
     * @throws InvalidAttributeException Invalid argument name.
     */
    public function getAttribute( $name )
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
     * Set an attribute’s value(s).
     * 
     * @param string $name Attribute name.
     * @param mixed $value Attibute value(s).
     * @return self
     */
    public function setAttribute( $name, $value )
    {
        $this->getAttribute( $name )->setValue( $value );

        return $this;
    }

    /**
     * Add a value to an attribute.
     * 
     * @param string $name Attribute name.
     * @param mixed $value Attibute value(s).
     * @return self
     */
    public function addAttribute( $name, $value )
    {
        $this->getAttribute( $name )->addValue( $value );

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
     * @param mixed $offset The array key.
     * @return boolean
     */
    public function offsetExists( $offset )
    {
        return isset( $this->attributes[ $offset ] ); 
    }

    /**
     * Get the value of the specified Attribute.
     * 
     * @param string $offset Attribute name.
     * @return string|array Attribute value.
     * @throws OutOfBoundsException Attribute does not exist.
     */
    public function offsetGet( $offset )
    {
        return $this->getAttribute( $offset )->getValue();
    }

    /**
     * Set an Attibute’s value. Existing values will be replaced. 
     * For adding values use Object::addAttribute().
     * 
     * @param string $offset Attribute name.
     * @param type $value New Attribute value.
     * @return void
     * @throws OutOfBoundsException Attribute does not exist.
     */
    public function offsetSet( $offset, $value )
    {
        $this->setAttribute( $offset, $value );
    }

    /**
     * Reset an Attribute’s value.
     * 
     * @param string $offset Attribute name.
     * @return void
     */
    public function offsetUnset( $offset )
    {
        if (isset($this->attributes[ $offset ])) {
            $this->setAttribute( $offset, NULL );
        }
    }

    /**
     * Create an Iterator for use in foreach. Only the populated Attributes are 
     * passed. This creates a clone of the Attributes array and hence does not 
     * modify the original set.
     * 
     * @return Iterator Read-only access to all defined attributes
     */
    public function getIterator()
    {
        return new ObjectIterator( $this );
    }

    /**
     * Return the number of defined Attributes.
     * 
     * @return integer
     */
    public function count()
    {
        return count( $this->getDefinedAttributes() );
    }

    /**
     * Convert the list of attributes into a name+value array.
     * 
     * @return array
     */
    public function toArray()
    {
        return array_reduce( $this->getDefinedAttributes(), function ( array $list, AttributeInterface $attr ) {
            return array_merge( $list, $attr->toArray() );
        }, [] );
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
    protected function validateEmail( $email )
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
    protected function validatePhone( $phone )
    {
        if ( preg_match( '~^\+[1-9]\d*([ .-]\d+)*( ext\. \d+)?$~', $phone ) ) {
            return $phone;
        }

        throw new InvalidValueException( 'Invalid phone/fax number' );
    }

    /**
     * Validation function for all attributes that require a Mntner object. 
     * (And there are a lot of them so I consider this justified)
     * 
     * @param mixed $input Handle string or object.
     * @param string $type The validating attribute’s name.
     * @return string
     * @throws InvalidValueException
     */
    protected function validateMntner( $input, $type )
    {
        if ( $input instanceof RPSL\Mntner ) {
            return $input->getPrimaryKey();
        }

        return $this->validateReference( $input, $type, [ 'Mntner' ] );
    }

    /**
     * Validate object references. Any valid object must be handled before this 
     * as at this point only valid strings will pass validation.
     * 
     * @param string|object $input Handle string or (invalid) object.
     * @param string $type The attribute requesting validation.
     * @param array $allowed The allowed object types.
     * @return string
     * @throws InvalidValueException
     */
    protected function validateReference( $input, $type, array $allowed )
    {
        if ( $input instanceof ObjectInterface ) {
            $msg = sprintf( 'Only %s objects are allowed as %s', implode( '/', $allowed ), $type );
            throw new InvalidValueException( $msg );
        }

        if ( is_string( $input ) ) {
            return $this->validateHandle( strtoupper( $input ) );
        }

        throw new InvalidValueException( 'Invalid handle for ' . $type );
    }

    /**
     * Validation function for RPSL object handles.
     * 
     * @param string $handle 
     * @return string
     * @throws InvalidValueException
     */
    protected function validateHandle( $handle )
    {
        if ( ! preg_match( '~[^A-Z0-9-]~', $handle ) ) {
            return $handle;
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
     * Helper callback for the 'changed' attribute. If a valid email is given, 
     * append the current date. If the input somewhat matches the required 
     * format, pass it on.
     * 
     * @param string $input 
     * @return string
     * @throws InvalidValueException
     */
    public function changed( $input )
    {
        $input = trim( $input );

        if ( filter_var( $input, \FILTER_VALIDATE_EMAIL ) ) {
            return $input . date( ' Ymd' );
        }

        if ( preg_match( '~^\S+@\S+ (19|20)?\d\d[01]\d[0-3]\d$~', $input ) ) {
            return $input;
        }

        throw new InvalidValueException( 'Invalid email or date format' );
    }

    /**
     * Helper callback for the 'admin-c' attribute. The input is valid for a 
     * Person object or an RPSL object handle.
     * 
     * @param string|Person $input 
     * @return string
     * @throws InvalidValueException
     */
    public function adminC( $input )
    {
        if ( $input instanceof RPSL\Person ) {
            return $input->getPrimaryKey();
        }

        return $this->validateReference( $input, 'admin-c', [ 'Person' ] );
    }

    /**
     * Helper callback for the 'tech-c' attribute. The input is valid for a 
     * Person or Role object or an RPSL object handle.
     * 
     * @param string|Person|Role $input 
     * @return string
     * @throws InvalidValueException
     */
    public function techC( $input )
    {
        if ( $input instanceof RPSL\Person ) {
            return $input->getPrimaryKey();
        }
        if ( $input instanceof RPSL\Role ) {
            return $input->getPrimaryKey();
        }

        return $this->validateReference( $input, 'tech-c', [ 'Person', 'Role' ] );
    }

    /**
     * Helper callback for the 'mnt-by' attribute. The input is valid for a 
     * Mntner object or an RPSL object handle.
     * 
     * @param string|Mntner $input 
     * @return string
     * @throws InvalidValueException
     */
    public function mntBy( $input )
    {
        return $this->validateMntner( $input, 'mnt-by' );
    }

    // there are various other mnt-* and *-c attributes, but they or their 
    // objects are hardly used so it’s not yet worth the effort
}
