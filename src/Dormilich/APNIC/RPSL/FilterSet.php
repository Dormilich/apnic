<?php
// FilterSet.php

namespace Dormilich\APNIC\RPSL;

use Dormilich\APNIC\Object;
use Dormilich\APNIC\AttributeInterface as Attr;

class FilterSet extends Object
{
    /**
     * Create a FILTER-SET RIPE object.
     * 
     * @param string $value The name of the set (of routers).
     * @return self
     */
    public function __construct($value)
    {
        $this->init();
        $this->setType('filter-set');
        $this->setKey('filter-set', $value);
    }

    /**
     * Defines attributes for the FILTER-SET RIPE object. 
     * 
     * @return void
     */
    protected function init()
    {
        $this->create('filter-set',  Attr::REQUIRED, Attr::SINGLE);
        $this->create('descr',       Attr::REQUIRED, Attr::MULTIPLE);
        $this->create('filter',      Attr::OPTIONAL, Attr::SINGLE);
        $this->create('mp-filter',   Attr::OPTIONAL, Attr::SINGLE);
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
