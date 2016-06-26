<?php
// KeyCert.php

namespace Dormilich\APNIC\RPSL;

use Dormilich\APNIC\Object;
use Dormilich\APNIC\AttributeInterface as Attr;

/**
 * Be aware that the 'method', 'owner' and 'fingerpr' attributes 
 * must not be set/updated/deleted by the user.
 */
class KeyCert extends Object
{
    /**
     * Create a key certification (KEY-CERT) RIPE object.
     * 
     * @param string $value The key ID.
     * @return self
     */
    public function __construct($value)
    {
        $this->init();
        $this->setType('key-cert');
        $this->setKey('key-cert', $value);
    }

    /**
     * Defines attributes for the KEY-CERT RIPE object. 
     * 
     * @return void
     */
    protected function init()
    {
        $this->create('key-cert', Attr::REQUIRED, Attr::SINGLE);
        $this->generated('method',   Attr::SINGLE);
        $this->generated('owner',    Attr::MULTIPLE);
        $this->generated('fingerpr', Attr::SINGLE);
        $this->create('certif',   Attr::REQUIRED, Attr::MULTIPLE);
        $this->create('remarks',  Attr::OPTIONAL, Attr::MULTIPLE);
        $this->create('notify',   Attr::OPTIONAL, Attr::MULTIPLE);
        $this->create('admin-c',  Attr::OPTIONAL, Attr::MULTIPLE);
        $this->create('tech-c',   Attr::OPTIONAL, Attr::MULTIPLE);
        $this->create('mnt-by',   Attr::REQUIRED, Attr::MULTIPLE);
        $this->create('changed',  Attr::REQUIRED, Attr::MULTIPLE);
        $this->create('source',   Attr::REQUIRED, Attr::SINGLE);
    }
}
