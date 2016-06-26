<?php
// Mntner.php

namespace Dormilich\APNIC\RPSL;

use Dormilich\APNIC\Object;
use Dormilich\APNIC\AttributeInterface as Attr;

class Mntner extends Object
{
    /**
     * Create a maintainer (MNTNER) RIPE object.
     * 
     * @param string $value Handle of the maintainer that is represented by this object.
     * @return self
     */
    public function __construct($value)
    {
        $this->init();
        $this->setType('mntner');
        $this->setKey('mntner', $value);
    }

    /**
     * Defines attributes for the MNTNER RIPE object. 
     * 
     * @return void
     */
    protected function init()
    {
        $this->create('mntner',  Attr::REQUIRED, Attr::SINGLE);
        $this->create('descr',   Attr::REQUIRED, Attr::MULTIPLE);
        $this->create('country', Attr::OPTIONAL, Attr::SINGLE);
        $this->create('admin-c', Attr::REQUIRED, Attr::MULTIPLE);
        $this->create('tech-c',  Attr::OPTIONAL, Attr::MULTIPLE);
        $this->create('upd-to',  Attr::REQUIRED, Attr::MULTIPLE);
        $this->create('mnt-nfy', Attr::OPTIONAL, Attr::MULTIPLE);
        $this->create('auth',    Attr::REQUIRED, Attr::MULTIPLE);
        $this->create('remarks', Attr::OPTIONAL, Attr::MULTIPLE);
        $this->create('notify',  Attr::OPTIONAL, Attr::MULTIPLE);
        $this->create('abuse-mailbox', Attr::OPTIONAL, Attr::MULTIPLE);
        $this->create('mnt-by',  Attr::REQUIRED, Attr::MULTIPLE);
        $this->create('referral-by',  Attr::REQUIRED, Attr::SINGLE);
        $this->create('changed', Attr::REQUIRED, Attr::MULTIPLE);
        $this->create('source',  Attr::REQUIRED, Attr::SINGLE);
    }
}
