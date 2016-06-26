<?php
// AutNum.php

namespace Dormilich\APNIC\RPSL;

use Dormilich\APNIC\Object;
use Dormilich\APNIC\AttributeInterface as Attr;

/**
 * Be aware that the 'sponsoring-org' and 'status' attributes 
 * must not be set/updated/deleted by the user.
 */
class AutNum extends Object
{
    /**
     * Create an AUTONOMOUS NUMBER (AUT-NUM) RIPE object.
     * 
     * @param string $value The ASN.
     * @return self
     */
    public function __construct($value)
    {
        $this->init();
        $this->setType('aut-num');
        $this->setKey('aut-num', $value);
    }

    /**
     * Defines attributes for the AUT-NUM RIPE object. 
     * 
     * @return void
     */
    protected function init()
    {
        $this->create('aut-num',    Attr::REQUIRED, Attr::SINGLE);
        $this->create('as-name',    Attr::REQUIRED, Attr::SINGLE);
        $this->create('descr',      Attr::REQUIRED, Attr::MULTIPLE);
        $this->create('country',    Attr::REQUIRED, Attr::SINGLE);
        $this->create('member-of',  Attr::OPTIONAL, Attr::MULTIPLE);
        $this->create('import',     Attr::OPTIONAL, Attr::MULTIPLE);
        $this->create('export',     Attr::OPTIONAL, Attr::MULTIPLE);
        $this->create('default',    Attr::OPTIONAL, Attr::MULTIPLE);
        $this->create('remarks',    Attr::OPTIONAL, Attr::MULTIPLE);
        $this->create('admin-c',    Attr::REQUIRED, Attr::MULTIPLE);
        $this->create('tech-c',     Attr::REQUIRED, Attr::MULTIPLE);
        $this->create('notify',     Attr::OPTIONAL, Attr::MULTIPLE);
        $this->create('mnt-lower',  Attr::OPTIONAL, Attr::MULTIPLE);
        $this->create('mnt-routes', Attr::OPTIONAL, Attr::MULTIPLE);
        $this->create('mnt-by',     Attr::REQUIRED, Attr::MULTIPLE);
        $this->create('mnt-irt',    Attr::REQUIRED, Attr::MULTIPLE);
        $this->create('changed',    Attr::REQUIRED, Attr::MULTIPLE);
        $this->create('source',     Attr::REQUIRED, Attr::SINGLE);
    }
}
