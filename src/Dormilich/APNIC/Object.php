<?php
// Object.php

namespace Dormilich\APNIC;

use Dormilich\APNIC\AttributeInterface as Attr;
use Dormilich\APNIC\Exceptions\IncompleteRPSLObjectException;
use Dormilich\APNIC\Exceptions\InvalidAttributeException;
use Dormilich\APNIC\Exceptions\InvalidDataTypeException;
use Dormilich\APNIC\Exceptions\InvalidValueException;

/**
 * The prototype for every RIPE object class. 
 * 
 * A child class must
 *  1) define a primary key and type (which are usually the same)
 *  2) set the class name to thats name using camel case (e.g. domain => Domain, aut-num => AutNum)
 *  3) define the attributes for this RIPE object
 * 
 * A child class should
 *  - set the primary key on instantiation
 *  - set a "VERSION" constant
 */
abstract class Object implements ObjectInterface, \ArrayAccess, \IteratorAggregate, \Countable
{
    /**
     * The type of the object as found in the WHOIS response object’s 'type' parameter.
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
     * Define the attributes for this object according to the RIPE DB docs.
     * 
     * @return void
     */
    abstract protected function init();

    /**
     * Get the value of the attribute defined as primary key.
     * 
     * @return string
     */
    public function getPrimaryKey()
    {
        return $this->getAttribute($this->primaryKey)->getValue();
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
     * Set the name of the primary key.
     * 
     * @param string $value The name of the primary key.
     * @return self
     * @throws LogicException Value is empty
     */
    protected function setKey($name, $value)
    {
        if (NULL === $this->primaryKey) {
            $this->primaryKey = (string) $name;
            if (strlen($this->primaryKey) === 0) {
                throw new \LogicException('The Primary Key must not be empty.');
            }
            $this->getAttribute($name)->setValue($value)->lock();
        }

        return $this;
    }

    /**
     * Get the name of the current RIPE object.
     * 
     * @return string RIPE object name.
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set the name of the object type.
     * 
     * @param string $value The name of the primary key.
     * @return self
     * @throws LogicException Value is empty
     */
    protected function setType($value)
    {
        if (NULL === $this->type) {
            $this->type = (string) $value;
            if (strlen($this->type) === 0) {
                throw new \LogicException('The object type must not be empty.');
            }
        }

        return $this;
    }

    /**
     * Shortcut for creating an attribute definition.
     * 
     * @param string $name Name of the attribute.
     * @param boolean $required If the attribute is mandatory.
     * @param boolean $multiple If the attribute allows multiple values.
     * @return self
     */
    protected function create($name, $required, $multiple)
    {
        $this->attributes[$name] = new Attribute($name, $required, $multiple);

        return $this;
    }

    /**
     * Shortcut for creating a generated attribute definition. Generated 
     * attributes are set to be optional.
     * 
     * @param string $name Name of the attribute.
     * @param boolean $multiple [false] If the attribute allows multiple values.
     * @return self
     */
    protected function generated($name, $multiple = Attr::SINGLE)
    {
        $attr = new Attribute($name, Attr::OPTIONAL, $multiple);
        $attr->lock();

        $this->attributes[$name] = $attr;

        return $this;
    }

    /**
     * Shortcut for creating an attribute with fixed values. Fixed attributes 
     * are usually single value attributes.
     * 
     * @param string $name Name of the attribute.
     * @param boolean $required If the attribute is mandatory.
     * @param array $constraint A string list of the allowed values.
     * @return self
     */
    protected function fixed($name, $required, array $constraint)
    {
        $this->attributes[$name] = new FixedAttribute($name, $required, $constraint);

        return $this;
    }

    /**
     * Shortcut for creating an attribute with values matching a given regular 
     * expression. Fixed attributes are usually single value attributes.
     * 
     * @param string $name Name of the attribute.
     * @param boolean $required If the attribute is mandatory.
     * @param string $constraint A RegExp the values have to fulfill.
     * @return self
     * @throws InvalidAttributeException RegExp is invalid.
     */
    protected function matched($name, $required, $constraint)
    {
        $this->attributes[$name] = new MatchedAttribute($name, $required, $constraint);

        return $this;
    }

    /**
     * Get the keys for the attributes (no matter whether they’re defined or not), 
     * optionally adding the names of the generated attributes.
     * 
     * @param bool $includeGenerated 
     * @return array
     */
    public function getAttributeNames($includeGenerated = false)
    {
        $names = array_keys($this->attributes);

        if ($includeGenerated) {
            $names = array_merge($names, array_keys($this->generated));
        }

        return $names;
    }

    /**
     * Get an attribute specified by name.
     * 
     * @param string $name Name of the attribute.
     * @return Attribute Attribute object.
     * @throws InvalidAttributeException Invalid argument name.
     */
    public function getAttribute($name)
    {
        if (isset($this->attributes[$name])) {
            return $this->attributes[$name];
        }
        throw new InvalidAttributeException('Attribute "' . $name . '" is not defined for the ' . strtoupper($this->type) . ' object.');
    }

    /**
     * Set an attribute’s value(s).
     * 
     * @param string $name Attribute name.
     * @param mixed $value Attibute value(s).
     * @return self
     */
    public function setAttribute($name, $value)
    {
        $this->getAttribute($name)->setValue($value);

        return $this;
    }

    /**
     * Add a value to an attribute.
     * 
     * @param string $name Attribute name.
     * @param mixed $value Attibute value(s).
     * @return self
     */
    public function addAttribute($name, $value)
    {
        $this->getAttribute($name)->addValue($value);

        return $this;
    }

    /**
     * Output the object as a textual list of its defined attributes.
     * 
     * @return string
     */
    public function __toString()
    {
        $output = '';
        $max = max(array_map('strlen', array_keys($this->attributes)));
        // using $this because of the applied filter and flattener
        foreach ($this as $name => $attr)  {
            $output .= $name . ':   ';
            $output .= str_pad('', $max - strlen($name), ' ', \STR_PAD_LEFT);
            $output .= $value . \PHP_EOL;
        }

        return $output;
    }

    /**
     * Checks if an Attribute exists, but not if it is populated.
     * 
     * @param mixed $offset The array key.
     * @return boolean
     */
    public function offsetExists($offset)
    {
        return isset($this->attributes[$offset]); 
    }

    /**
     * Get the value of the specified Attribute.
     * 
     * @param string $offset Attribute name.
     * @return string|array Attribute value.
     * @throws OutOfBoundsException Attribute does not exist.
     */
    public function offsetGet($offset)
    {
        return $this->getAttribute($offset)->getValue();
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
    public function offsetSet($offset, $value)
    {
        $this->setAttribute($offset, $value);
    }

    /**
     * Reset an Attribute’s value.
     * 
     * @param string $offset Attribute name.
     * @return void
     */
    public function offsetUnset($offset)
    {
        if (isset($this->attributes[$offset])) {
            $this->setAttribute($offset, NULL);
        }
    }

    /**
     * Create an Iterator for use in foreach. Only the populated Attributes are passed.
     * This creates a clone of the Attributes array and hence does not modify the original set.
     * 
     * @return Iterator Read-only access to all defined attributes (including generated attributes)
     */
    public function getIterator()
    {
        return new ObjectIterator($this->getDefinedAttributes());
    }

    /**
     * Return the number of defined Attributes.
     * 
     * @return integer
     */
    public function count()
    {
        return count($this->getDefinedAttributes());
    }

    /**
     * Filter all attributes that are defined.
     * 
     * @return array
     */
    protected function getDefinedAttributes()
    {
        return array_filter($this->attributes, function ($attr) {
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
        return array_reduce($this->attributes, function ($carry, $attr) {
            if ($attr->isRequired() and !$attr->isDefined()) {
                return false;
            }
            return $carry;
        }, true);
    }
}
