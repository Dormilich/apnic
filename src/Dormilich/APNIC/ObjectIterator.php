<?php
// ObjectIterator

namespace Dormilich\APNIC;

/**
 * Iterate through all values of the object’s attributes.
 * 
 * Note: Due to the keys not being unique, the use of iterator_to_array() may 
 *       scrap data off the multiple attributes.
 */
class ObjectIterator implements \Iterator, \Countable
{
    /**
     * The flattenened attribute values as name/value arrays.
     * @var array
     */
    protected $data = [];

    /**
     * Convert the Attribute list into an array of name/value arrays.
     * 
     * @param array $attributes List of APNIC Attribute objects.
     * @return self
     */
    public function __construct(array $attributes)
    {
        $this->data = $this->flatten($attributes);
    }

    /**
     * Flatten the Attribute array.
     * 
     * @param array $attributes 
     * @return array
     */
    protected function flatten(array $attributes)
    {
        $data = [];

        $attributes = array_filter($attributes, function ($attr) {
            return $attr instanceof AttributeInterface;
        });

        foreach ($attributes as $key => $attr) {
            foreach ((array) $attr->getValue() as $value) {
                $data[] = [
                    "name"  => $attr->getName(),
                    "value" => $value,
                ];
            }
        }
        return $data;
    }

    /**
     * Returns the base array used to iterate over.
     * 
     * @return array
     */
    public function getArray()
    {
        return $this->data;
    }

    /**
     * @inheritdoc
     */
    public function rewind()
    {
        reset($this->data);
    }

    /**
     * @inheritdoc
     */
    public function current()
    {
        return current($this->data)['name'];
    }

    /**
     * @inheritdoc
     */
    public function key()
    {
        return current($this->data)['value'];
    }

    /**
     * @inheritdoc
     */
    public function next()
    {
        next($this->data);
    }

    /**
     * @inheritdoc
     */
    public function valid()
    {
        return key($this->data) !== NULL;
    }

    /**
     * Return the number of lines in the result set.
     * 
     * @return integer
     */
    public function count()
    {
        return count($this->data);
    }
}
