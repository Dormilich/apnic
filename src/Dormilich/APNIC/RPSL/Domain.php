<?php
// Domain.php

namespace Dormilich\APNIC\RPSL;

use Dormilich\APNIC\Object;
use Dormilich\APNIC\AttributeInterface as Attr;
use Dormilich\APNIC\Exceptions\InvalidValueException;

class Domain extends Object
{
    /**
     * Create a DOMAIN RPSL object.
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
     * Defines attributes for the DOMAIN RPSL object. 
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
        $this->create('nserver',   Attr::REQUIRED, Attr::MULTIPLE);
        #$this->create('sub-dom',   Attr::OPTIONAL, Attr::MULTIPLE);
        #$this->create('dom-net',   Attr::OPTIONAL, Attr::MULTIPLE);
        $this->create('remarks',   Attr::OPTIONAL, Attr::MULTIPLE);
        $this->create('notify',    Attr::OPTIONAL, Attr::MULTIPLE);
        $this->create('mnt-by',    Attr::REQUIRED, Attr::MULTIPLE);
        $this->create('mnt-lower', Attr::OPTIONAL, Attr::MULTIPLE);
        #$this->create('refer',     Attr::OPTIONAL, Attr::SINGLE);
        $this->create('changed',   Attr::REQUIRED, Attr::MULTIPLE);
        $this->create('source',    Attr::REQUIRED, Attr::SINGLE)->apply('strtoupper');
    }

    public function domain( $input )
    {
        if ( strpos( $input, '.in-addr.arpa') !== false ) {
            return $input;
        }
        if ( strpos( $input, '.ip6.arpa') !== false ) {
            return $input;
        }

        throw new InvalidValueException( 'Invalid reverse delegation' );
    }
}
