<?php
// Attribute.php

namespace Dormilich\APNIC;

use Countable;
use Iterator;
use JsonSerializable;
use Traversable;
use Dormilich\APNIC\Exceptions\InvalidDataTypeException;

class Attribute implements AttributeInterface, Countable, Iterator, JsonSerializable
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var array
     */
    protected $value = [];

    /**
     * @var boolean
     */
    protected $mandatory;

    /**
     * @var boolean
     */
    protected $multiple;

    /**
     * @var callable 
     */
    protected $callback;

    /**
     * Object constructor.
     * 
     * Note:
     *      While the last two parameters can be of any type, you’re 
     *      encouraged to use the class constants for better readability.
     * 
     * @param string $name Attribute name.
     * @param boolean $mandatory If the attribute is mandatory/required.
     * @param boolean $multiple If the attribute allows multiple values.
     * @return self
     */
    public function __construct( $name, $mandatory, $multiple )
    {
        $this->name      = (string) $name;
        $this->mandatory = (bool) $mandatory;
        $this->multiple  = (bool) $multiple;
    }

    /**
     * Get the name of the attribute.
     * 
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Whether the attribute is populated with data (i.e. not empty).
     * 
     * @return boolean
     */
    public function isDefined()
    {
        return ( count( $this->value ) > 0 );
    }

    /**
     * Whether the attribute is required/mandatory.
     * 
     * @return boolean
     */
    public function isRequired()
    {
        return $this->mandatory;
    }

    /**
     * Whether the attribute allows multiple values.
     * 
     * @return boolean
     */
    public function isMultiple()
    {
        return $this->multiple;
    }

    /**
     * Set the transformer/validator callback that the input value is applied to. 
     * Validating callbacks should throw an exception if the input is invalid. 
     * The callback must return the value if it is valid.
     * 
     * @param callable $callback The callback function is passed the input value 
     *          as parameter and must return a value unless it throws an exception. 
     * @return self
     */
    public function apply( callable $callback )
    {
        if ( ! is_callable( $this->callback ) ) {
            $this->callback = $callback;
        }

        return $this;
    }

    /**
     * Get the current value(s) of the attribute.
     * If the value is unset NULL is returned, if the attribute
     * only allows a single value, that value is returned, otherwise an array.
     * 
     * @return mixed
     */
    public function getValue()
    {
        if ( count( $this->value ) === 0 ) {
            return NULL;
        }

        if ( $this->multiple ) {
            return $this->value;
        }

        return reset( $this->value );
    }

    /**
     * Set the value(s) of the attribute. Each value must be either a scalar 
     * or a stringifiable object. Passing an array to a single-valued attribute 
     * will cause a data type error. 
     * 
     * @param mixed $value A string or stringifyable object or an array thereof.
     * @return self
     * @throws InvalidDataTypeException Invalid data type of the value(s).
     */
    public function setValue( $value )
    {
        $this->value = [];
        $this->addValue( $value );

        return $this;
    }

    /**
     * Add value(s) to the attribute. If the attribute does not allow multiple 
     * values the value is replaced instead. The value(s) must be stringifiable. 
     * 
     * If NULL is passed, execution is skipped. That is, `setValue(NULL)` will 
     * reset the Attribute while `addValue(NULL)` has no effect. Passing an 
     * array to a single-valued attribute will cause a data type error. 
     * 
     * If a multiline block of text is passed, treat it as an array of text lines.
     * 
     * For single-valued attributes `addValue()` and `setValue()` work identically. 
     * 
     * @param mixed $value A string or stringifyable object or an array thereof.
     * @return self
     * @throws InvalidDataTypeException Invalid data type of the value(s).
     */
    public function addValue( $value )
    {
        if ( NULL === $value ) {
            return $this;
        }
        // split block text regardless of attribute type 
        // otherwise the created RPSL block text is likely to be invalid
        if ( is_string( $value ) and strpos( $value, "\n" ) !== false ) {
            $value = explode( "\n", $value );
        }
        // wrapping the supposedly-single value in an array makes sure that 
        // only a single iteration is done, even if an iterable is passed
        if ( ! $this->multiple ) {
            $this->value = [];
            $value = [ $value ];
        }

        foreach ( $this->loop( $value ) as $v ) {
            $this->value[] = $this->convert( $v );
        }
 
        return $this;
    }

    /**
     * Prepare a value for use in foreach().
     * 
     * @param mixed $value 
     * @return array|Traversable
     */
    protected function loop( $value )
    {
        if ( is_array( $value ) ) {
            return $value;
        }
        if ( $value instanceof Traversable ) {
            return $value;
        }
        return [ $value ];
    }

    /**
     * Entry point for data transformation/validation.
     * 
     * @param mixed $value 
     * @return string
     */
    protected function convert( $value )
    {
        $value = $this->run( $value );
        $value = $this->stringify( $value );

        return rtrim( $value );
    }

    /**
     * If a data transformer is given, apply it to the input value (before 
     * stringification).
     * 
     * @param mixed $input 
     * @return mixed
     */
    protected function run( $input )
    {
        if ( is_callable( $this->callback ) ) {
            return call_user_func( $this->callback, $input );
        }
        return $input;
    }

    /**
     * Converts a single value to a string. 
     * 
     * @param mixed $value A string or stringifyable object.
     * @return string Converted value.
     * @throws InvalidDataTypeException Invalid data type of the value(s).
     */
    final protected function stringify( $value )
    {
        if ( true === $value ) {
            return 'true';
        }
        if ( false === $value ) {
            return 'false';
        }
        if ( $value instanceof ObjectInterface ) {
            return $value->getPrimaryKey();
        }
        if ( is_scalar( $value ) or ( is_object( $value ) and method_exists( $value, '__toString' ) ) ) {
            return (string) $value;
        }

        $msg = sprintf( 'The [%s] attribute does not allow the %s data type.', $this->name, gettype( $value ) );
        throw new InvalidDataTypeException( $msg );
    }

    /**
     * Convert the list of values into a name+value array.
     * 
     * @return array
     */
    public function toArray()
    {
        $data = array_filter( $this->value, 'strlen' );
        $data = array_map( function ( $value ) {
            return [
                'name'  => $this->name,
                'value' => $value,
            ];
        }, $data );

        return $data;
    }

    /**
     * @see http://php.net/JsonSerializable
     * @return array
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }

    /**
     * Number of values assigned.
     * 
     * @see http://php.net/Countable
     * @return integer
     */
    public function count()
    {
        return count( $this->value );
    }

    /**
     * @see http://php.net/Iterator
     * @return void
     */
    public function rewind()
    {
        reset( $this->value );
    }
    
    /**
     * @see http://php.net/Iterator
     * @return string
     */
    public function current()
    {
        return current( $this->value );
    }
    
    /**
     * @see http://php.net/Iterator
     * @return integer
     */
    public function key()
    {
        return key( $this->value );
    }
    
    /**
     * @see http://php.net/Iterator
     * @return void
     */
    public function next()
    {
        next( $this->value );
    }
    
    /**
     * @see http://php.net/Iterator
     * @return boolean
     */
    public function valid()
    {
        return NULL !== key( $this->value );
    }
}
