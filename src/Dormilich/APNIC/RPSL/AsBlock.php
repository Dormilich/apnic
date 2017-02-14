<?php
// AsBlock.php

namespace Dormilich\APNIC\RPSL;

use Dormilich\APNIC\Object;
use Dormilich\APNIC\AttributeInterface as Attr;

class AsBlock extends Object
{
    const VERSION = '1.69';

    /**
     * Create a AS-BLOCK object.
     * 
     * @param string $value The range of AS numbers in this block.
     * @return self
     */
    public function __construct($value)
    {
        $this->init();
        $this->setType('as-block');
        $this->setKey('as-block', $value);
    }

    /**
     * Defines attributes for the AS-BLOCK RPSL object. 
     * 
     * @return void
     */
    protected function init()
    {
        $this->create('as-block',  Attr::REQUIRED, Attr::SINGLE);
        $this->create('descr',     Attr::OPTIONAL, Attr::MULTIPLE);
        $this->create('country',   Attr::OPTIONAL, Attr::SINGLE);
        $this->create('remarks',   Attr::OPTIONAL, Attr::MULTIPLE);
        $this->create('tech-c',    Attr::REQUIRED, Attr::MULTIPLE);
        $this->create('admin-c',   Attr::REQUIRED, Attr::MULTIPLE);
        $this->create('notify',    Attr::OPTIONAL, Attr::MULTIPLE);
        $this->create('mnt-lower', Attr::OPTIONAL, Attr::MULTIPLE);
        $this->create('mnt-by',    Attr::REQUIRED, Attr::MULTIPLE);
        $this->create('changed',   Attr::REQUIRED, Attr::MULTIPLE);
        $this->create('source',    Attr::REQUIRED, Attr::SINGLE)->apply('strtoupper');
    }
}
