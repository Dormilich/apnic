<?php
// Mntner.php

namespace Test;

use Dormilich\APNIC\AttributeInterface as Attr;
use Dormilich\APNIC\Object;

/**
 * An object with a bit of flexibility for testing purposes.
 */
class BaseObject extends Object
{
    /**
     * Most basic object implementation to test the base class’ principal 
     * behaviour.
     * 
     * @param string|NULL $value The value for the PK.
     */
    public function __construct($value = null)
    {
        $this->init();
        $this->setType('test');
        $this->setKey('test', $value);
    }

    /**
     * Create an attribute for the primary key.
     * 
     * @return void
     */
    protected function init() 
    {
        // PK
        $this->create('test',    Attr::REQUIRED, Attr::SINGLE);
        // single & multiple attributes
        $this->create('name',    Attr::OPTIONAL, Attr::SINGLE);
        $this->create('comment', Attr::OPTIONAL, Attr::MULTIPLE);
        // generated attribute
        $this->setGeneratedAttribute('last-modified', Attr::SINGLE);
    }
}
