<?php
// InetRtr.php

namespace Dormilich\APNIC\RPSL;

use Dormilich\APNIC\Object;
use Dormilich\APNIC\AttributeInterface as Attr;
use Dormilich\APNIC\Exceptions\InvalidValueException;

class InetRtr extends Object
{
    const VERSION = '1.69';

    /**
     * Create a router (INET-RTR) RPSL object.
     * 
     * @param string $value The DNS name.
     * @return self
     */
    public function __construct( $value )
    {
        $this->init();
        $this->setType( 'inet-rtr' );
        $this->setKey( [
            'inet-rtr' => $value,
        ] );
    }

    /**
     * Defines attributes for the INET-RTR RPSL object. 
     * 
     * @return void
     */
    protected function init()
    {
        $this->create( 'inet-rtr', Attr::REQUIRED, Attr::SINGLE );          # 1 +
        $this->create( 'descr', Attr::REQUIRED, Attr::MULTIPLE );           # m +
        $this->create( 'alias', Attr::OPTIONAL, Attr::MULTIPLE );           # m
        $this->create( 'local-as', Attr::REQUIRED, Attr::SINGLE );          # 1 +
        $this->create( 'ifaddr', Attr::REQUIRED, Attr::MULTIPLE );          # m +
        $this->create( 'interface', Attr::OPTIONAL, Attr::MULTIPLE );       # m
        $this->create( 'peer', Attr::OPTIONAL, Attr::MULTIPLE );            # m
        $this->create( 'mp-peer', Attr::OPTIONAL, Attr::MULTIPLE );         # m
        $this->create( 'member-of', Attr::OPTIONAL, Attr::MULTIPLE );       # m
        $this->create( 'remarks', Attr::OPTIONAL, Attr::MULTIPLE );         # m
        $this->create( 'org', Attr::OPTIONAL, Attr::MULTIPLE );             # m
        $this->create( 'admin-c', Attr::REQUIRED, Attr::MULTIPLE );         # m +
        $this->create( 'tech-c', Attr::REQUIRED, Attr::MULTIPLE );          # m +
        $this->create( 'notify', Attr::OPTIONAL, Attr::MULTIPLE );          # m
        $this->create( 'mnt-by', Attr::REQUIRED, Attr::MULTIPLE );          # m +
        $this->create( 'changed', Attr::REQUIRED, Attr::MULTIPLE );         # m +
        $this->create( 'source', Attr::REQUIRED, Attr::SINGLE )             # 1 +
            ->apply( 'strtoupper' );
    }

    public function localAs( $input )
    {
        if ( preg_match( '~^AS\d+$~', $input ) ) {
            return $input;
        }

        throw new InvalidValueException( 'Invalid AS number' );
    }
}
