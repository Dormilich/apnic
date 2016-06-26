<?php
// Inet6num.php

namespace Dormilich\APNIC\RPSL;

use Dormilich\APNIC\Object;
use Dormilich\APNIC\AttributeInterface as Attr;

class Inet6num extends Object
{
    /**
     * Create a INET6NUM RIPE object
     * 
     * @param string $value A block of or a single IPv6 address.
     * @return self
     */
    public function __construct($value)
    {
        $this->init();
        $this->setType('inet6num');
        $this->setKey('inet6num', $value);
    }

    /**
     * Defines attributes for the INET6NUM RIPE object. 
     * 
     * @return void
     */
    protected function init()
    {
        $this->create('inet6num',    Attr::REQUIRED, Attr::SINGLE);
        $this->create('netname',     Attr::REQUIRED, Attr::SINGLE);
        $this->create('descr',       Attr::REQUIRED, Attr::MULTIPLE);
        $this->create('country',     Attr::REQUIRED, Attr::MULTIPLE);
        $this->create('geoloc',      Attr::OPTIONAL, Attr::SINGLE);
        $this->create('language',    Attr::OPTIONAL, Attr::MULTIPLE);
        $this->create('admin-c',     Attr::REQUIRED, Attr::MULTIPLE);
        $this->create('tech-c',      Attr::REQUIRED, Attr::MULTIPLE);
        $this->fixed('status',       Attr::REQUIRED, [
            'ALLOCATED PORTABLE', 'ALLOCATED NON-PORTABLE', 
            'ASSIGNED PORTABLE',  'ASSIGNED NON-PORTABLE',
        ]);
        $this->create('remarks',     Attr::OPTIONAL, Attr::MULTIPLE);
        $this->create('notify',      Attr::OPTIONAL, Attr::MULTIPLE);
        $this->create('mnt-by',      Attr::REQUIRED, Attr::MULTIPLE);
        $this->create('mnt-lower',   Attr::OPTIONAL, Attr::MULTIPLE);
        $this->create('mnt-routes',  Attr::OPTIONAL, Attr::MULTIPLE);
        $this->create('mnt-irt',     Attr::REQUIRED, Attr::SINGLE);
        $this->create('changed',     Attr::REQUIRED, Attr::MULTIPLE);
        $this->create('source',      Attr::REQUIRED, Attr::SINGLE);
    }
}
