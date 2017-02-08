<?php
// ObjectIterator

namespace Dormilich\APNIC;

/**
 * Iterate through all values of the object’s attributes.
 * 
 * Note: Due to the keys not being unique, the use of iterator_to_array() may 
 *       scrap data off the multiple attributes.
 */
class ObjectIterator extends \ArrayIterator
{
    /**
     * Convert the Attribute list into an array of name/value arrays.
     * 
     * @param ArrayInterface $object.
     * @return self
     */
    public function __construct( ArrayInterface $object )
    {
        parent::__construct($object->toArray());
    }

    /**
     * @inheritdoc
     */
    public function current()
    {
        return parent::current()[ 'value' ];
    }

    /**
     * @inheritdoc
     */
    public function key()
    {
        return parent::current()[ 'name' ];
    }
}
