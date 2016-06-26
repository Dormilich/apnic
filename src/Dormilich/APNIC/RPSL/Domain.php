<?php
// Domain.php

namespace Dormilich\APNIC\RPSL;

use Dormilich\APNIC\Object;
use Dormilich\APNIC\AttributeInterface as Attr;

class Domain extends Object
{
    /**
     * Create a DOMAIN RIPE object.
     * 
     * @param string $value The reverse delegetion address/range.
     * @return self
     */
    public function __construct($value)
    {
        $this->init();
        $this->setType('domain');
        $this->setKey('domain', $value);
    }

    /**
     * Defines attributes for the DOMAIN RIPE object. 
     * 
     * @return void
     */
    protected function init()
    {
        $this->create('domain',    Attr::REQUIRED, Attr::SINGLE);
        $this->create('descr',     Attr::REQUIRED, Attr::MULTIPLE);
        $this->create('country',   Attr::REQUIRED, Attr::SINGLE);
        $this->create('admin-c',   Attr::REQUIRED, Attr::MULTIPLE);
        $this->create('tech-c',    Attr::REQUIRED, Attr::MULTIPLE);
        $this->create('zone-c',    Attr::REQUIRED, Attr::MULTIPLE);
        $this->create('nserver',   Attr::OPTIONAL, Attr::MULTIPLE);
        $this->create('sub-dom',   Attr::OPTIONAL, Attr::MULTIPLE);
        $this->create('dom-net',   Attr::OPTIONAL, Attr::MULTIPLE);
        $this->create('remarks',   Attr::OPTIONAL, Attr::MULTIPLE);
        $this->create('notify',    Attr::OPTIONAL, Attr::MULTIPLE);
        $this->create('mnt-by',    Attr::REQUIRED, Attr::MULTIPLE);
        $this->create('mnt-lower', Attr::OPTIONAL, Attr::MULTIPLE);
        $this->create('refer',     Attr::OPTIONAL, Attr::SINGLE);
        $this->create('changed',   Attr::REQUIRED, Attr::MULTIPLE);
        $this->create('source',    Attr::REQUIRED, Attr::SINGLE);
    }
}
