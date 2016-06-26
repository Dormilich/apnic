<?php
// Attribute.php

namespace Dormilich\APNIC;

use Dormilich\APNIC\Exceptions\InvalidDataTypeException;

class Attribute implements AttributeInterface
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
     * @var boolean 
     */
    protected $locked = false;

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
    public function __construct($name, $mandatory, $multiple)
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
        return (count($this->value) > 0);
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
     * Whether the attribute allows editing the attribute value, once it’s set.
     * 
     * @return boolean
     */
    public function isLocked()
    {
        return $this->locked;
    }

    /**
     * Disallow editing the attribute’s value.
     * 
     * @return self
     */
    public function lock()
    {
        $this->locked = true;

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
        if (count($this->value) === 0) {
            return NULL;
        }

        if (!$this->multiple) {
            return reset($this->value);
        }

        return $this->value;
    }

    /**
     * Set the value(s) of the attribute. Each value must be either a scalar 
     * or a stringifiable object.
     * 
     * @param mixed $value A string or stringifyable object or an array thereof.
     * @return self
     * @throws InvalidDataTypeException Invalid data type of the value(s).
     */
    public function setValue($value)
    {
        if (!$this->locked or count($this->value) === 0) {
            $this->value = [];
            $this->addValue($value);
        }

        return $this;
    }

    /**
     * Add value(s) to the attribute. If the attribute does not allow multiple 
     * values the value is replaced instead. The value(s) must be stringifiable.
     * If NULL is passed, execution is skipped. That is, `setValue(NULL)` will 
     * reset the Attribute while `addValue(NULL)` has no effect.
     * 
     * @param mixed $value A string or stringifyable object or an array thereof.
     * @return self
     * @throws InvalidDataTypeException Invalid data type of the value(s).
     */
    public function addValue($value)
    {
        if (NULL === $value) {
            return $this;
        }

        if ($this->locked and count($this->value) > 0) {
            return $this;
        }

        if (!$this->multiple) {
            $this->value = [ $this->convert($value) ];
            return $this;
        }

        foreach ($this->loop($value) as $v) {
            $this->value[] = $this->convert($v);
        }
 
        return $this;
    }

    /**
     * Prepare a value for use in foreach().
     * 
     * @param mixed $value 
     * @return array|Traversable
     */
    protected function loop($value)
    {
        if (is_array($value)) {
            return $value;
        }
        if ($value instanceof \Traversable) {
            return $value;
        }
        return [$value];
    }

    /**
     * Converts a single value to a string. This method may be extended to add 
     * further value validation.
     * 
     * @param mixed $value A string or stringifyable object.
     * @return string Converted value.
     * @throws InvalidDataTypeException Invalid data type of the value(s).
     */
    protected function convert($value)
    {
        if (true === $value) {
            return 'true';
        }
        if (false === $value) {
            return 'false';
        }
        if (is_scalar($value) or (is_object($value) and method_exists($value, '__toString'))) {
            return (string) $value;
        }

        $msg = sprintf('The [%s] attribute does not allow the %s data type.', $this->name, gettype($value));
        throw new InvalidDataTypeException($msg);
    }
}
