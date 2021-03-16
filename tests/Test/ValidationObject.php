<?php
// Mntner.php

namespace Test;

use Dormilich\APNIC\AttributeInterface as Attr;

/**
 * An object with attributes for inherited validation tests.
 */
class ValidationObject extends \Dormilich\APNIC\AbstractObject
{
    /**
     * Create stand-in object for any RIPE object data. 
     * It's the responsibility of the writer to figure out the correct values.
     * One notable difference to the regular objects is that it expects the 
     * Type and PK names (which may be the same), and not the Type’s value.
     * 
     * @param string|NULL $value The value for the PK.
     */
    public function __construct($value = null)
    {
        $this->init();
        $this->setType('test');
        $this->setKey(['test' => $value]);
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
        // no validation
        $this->create('remarks', Attr::OPTIONAL, Attr::MULTIPLE);
        // phone validation
        $this->create('fax-no',  Attr::OPTIONAL, Attr::MULTIPLE);
        // length validation (inherit)
        $this->create('country', Attr::OPTIONAL, Attr::SINGLE);
        // email validation (inherit)
        $this->create('notify',  Attr::OPTIONAL, Attr::MULTIPLE);
        // handle (inherit)
        $this->create('mnt-by',  Attr::REQUIRED, Attr::MULTIPLE);
        // upper-case (inherit)
        $this->create('source',  Attr::REQUIRED, Attr::SINGLE);
    }

    /**
     * Phone validator. Does not occur often enough (such as country) to make 
     * it into the base object.
     * 
     * @param mixed $input 
     * @return string
     */
    public function faxNo( $input )
    {
        return $this->validatePhone( $input );
    }
}
