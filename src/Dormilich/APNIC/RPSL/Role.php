<?php
// Role.php

namespace Dormilich\APNIC\RPSL;

use Dormilich\APNIC\Object;
use Dormilich\APNIC\AttributeInterface as Attr;

class Role extends Object
{
    const VERSION = '1.69';

    /**
     * Create a ROLE RPSL object.
     * 
     * @param string $value NIC handle. If not specified an auto-handle is used.
     * @return self
     */
    public function __construct( $value = 'AUTO-1' )
    {
        $this->init();
        $this->setType( 'role' );
        $this->setKey( [
            'nic-hdl' => $value,
        ] );
    }

    /**
     * Defines attributes for the ROLE RPSL object. 
     * 
     * @return void
     */
    protected function init()
    {
        $this->create( 'role', Attr::REQUIRED, Attr::SINGLE );              # 1 +
        $this->create( 'address', Attr::REQUIRED, Attr::MULTIPLE );         # m +
        $this->create( 'country', Attr::REQUIRED, Attr::SINGLE );           # 1 +
        $this->create( 'phone', Attr::REQUIRED, Attr::MULTIPLE )            # m +
            ->apply( [$this, 'validatePhone'] );
        $this->create( 'fax-no', Attr::OPTIONAL, Attr::MULTIPLE )           # m
            ->apply( [$this, 'validatePhone'] );
        $this->create( 'e-mail', Attr::REQUIRED, Attr::MULTIPLE )           # m +
            ->apply( [$this, 'validateEmail'] );
        $this->create( 'org', Attr::OPTIONAL, Attr::MULTIPLE );             # m
        $this->create( 'admin-c', Attr::REQUIRED, Attr::MULTIPLE );         # m +
        $this->create( 'tech-c', Attr::REQUIRED, Attr::MULTIPLE );          # m +
        $this->create( 'nic-hdl', Attr::REQUIRED, Attr::SINGLE )            # 1 +
            ->apply( 'strtoupper' );
        $this->create( 'remarks', Attr::OPTIONAL, Attr::MULTIPLE );         # m
        $this->create( 'notify', Attr::OPTIONAL, Attr::MULTIPLE );          # m
        $this->create( 'abuse-mailbox', Attr::OPTIONAL, Attr::MULTIPLE )    # m
            ->apply( [$this, 'validateEmail'] );
        $this->create( 'mnt-by', Attr::REQUIRED, Attr::MULTIPLE );          # m +
        $this->create( 'changed', Attr::REQUIRED, Attr::MULTIPLE );         # m +
        $this->create( 'source', Attr::REQUIRED, Attr::SINGLE )             # 1 +
            ->apply( 'strtoupper' );
    }

    public function phone( $input )
    {
        return $this->validatePhone( $input );
    }

    public function faxNo( $input )
    {
        return $this->validatePhone( $input );
    }

    public function eMail( $input )
    {
        return $this->validateEmail( $input );
    }

    public function abuseMailbox( $input )
    {
        return $this->validateEmail( $input );
    }
}
