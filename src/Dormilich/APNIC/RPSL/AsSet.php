<?php
// AsSet.php

namespace Dormilich\APNIC\RPSL;

use Dormilich\APNIC\Object;
use Dormilich\APNIC\AttributeInterface as Attr;

class AsSet extends Object
{
    /**
     * Create an AS-SET RIPE object.
     * 
     * @param string $value The name of the AS-Set.
     * @return self
     */
    public function __construct($value)
    {
        $this->init();
        $this->setType('as-set');
        $this->setKey('as-set', $value);
    }

    /**
     * Defines attributes for the AS-SET RIPE object. 
     * 
     * @return void
     */
    protected function init()
    {
        $this->create('as-set',      Attr::REQUIRED, Attr::SINGLE);
        $this->create('descr',       Attr::REQUIRED, Attr::MULTIPLE);
        $this->create('members',     Attr::OPTIONAL, Attr::MULTIPLE);
        $this->create('mbrs-by-ref', Attr::OPTIONAL, Attr::MULTIPLE);
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
