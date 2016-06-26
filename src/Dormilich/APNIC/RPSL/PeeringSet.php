<?php
// PeeringSet.php

namespace Dormilich\APNIC\RPSL;

use Dormilich\APNIC\Object;
use Dormilich\APNIC\AttributeInterface as Attr;

class PeeringSet extends Object
{
    /**
     * Create a PEERING-SET RIPE object.
     * 
     * @param string $value The name of the set.
     * @return self
     */
    public function __construct($value)
    {
        $this->init();
        $this->setType('peering-set');
        $this->setKey('peering-set', $value);
    }

    /**
     * Defines attributes for the PEERING-SET RIPE object. 
     * 
     * @return void
     */
    protected function init()
    {
        $this->create('peering-set', Attr::REQUIRED, Attr::SINGLE);
        $this->create('descr',       Attr::REQUIRED, Attr::MULTIPLE);
        $this->create('peering',     Attr::OPTIONAL, Attr::MULTIPLE);
        $this->create('mp-peering',  Attr::OPTIONAL, Attr::MULTIPLE);
        $this->create('remarks',     Attr::OPTIONAL, Attr::MULTIPLE);
        $this->create('tech-c',      Attr::REQUIRED, Attr::MULTIPLE);
        $this->create('admin-c',     Attr::REQUIRED, Attr::MULTIPLE);
        $this->create('notify',      Attr::OPTIONAL, Attr::MULTIPLE);
        $this->create('mnt-by',      Attr::REQUIRED, Attr::MULTIPLE);
        $this->create('mnt-lower',   Attr::OPTIONAL, Attr::MULTIPLE);
        $this->create('changed',     Attr::REQUIRED, Attr::MULTIPLE);
        $this->create('source',      Attr::REQUIRED, Attr::SINGLE);
    }
}
