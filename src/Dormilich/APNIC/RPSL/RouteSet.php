<?php
// RouteSet.php

namespace Dormilich\APNIC\RPSL;

use Dormilich\APNIC\Object;
use Dormilich\APNIC\AttributeInterface as Attr;

class RouteSet extends Object
{
    /**
     * Create a ROUTE-SET RIPE object.
     * 
     * @param string $value The name of the set (of route prefixes).
     * @return self
     */
    public function __construct($value)
    {
        $this->init();
        $this->setType('route-set');
        $this->setKey('route-set', $value);
    }

    /**
     * Defines attributes for the ROUTE-SET RIPE object. 
     * 
     * @return void
     */
    protected function init()
    {
        $this->create('route-set',   Attr::REQUIRED, Attr::SINGLE);
        $this->create('descr',       Attr::REQUIRED, Attr::MULTIPLE);
        $this->create('members',     Attr::OPTIONAL, Attr::MULTIPLE);
        $this->create('mp-members',  Attr::OPTIONAL, Attr::MULTIPLE);
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
