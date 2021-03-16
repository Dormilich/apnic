<?php
// Person.php

namespace Dormilich\APNIC\RPSL;

use Dormilich\APNIC\Object;
use Dormilich\APNIC\AttributeInterface as Attr;

class Person extends Object
{
    const VERSION = '1.88';

    /**
     * Create a PERSON RPSL object.
     * 
     * @param string $value NIC handle. If not specified an auto-handle is used.
     * @return self
     */
    public function __construct( $value = 'AUTO-1' )
    {
        $this->init();
        $this->setType( 'person' );
        $this->setKey( [
            'nic-hdl' => $value,
        ] );
    }

    /**
     * Defines attributes for the PERSON RPSL object. 
     * 
     * @return void
     */
    protected function init()
    {
        $this->create( 'person', Attr::REQUIRED, Attr::SINGLE );            # 1 +
        $this->create( 'address', Attr::REQUIRED, Attr::MULTIPLE );         # m +
        $this->create( 'country', Attr::REQUIRED, Attr::SINGLE );           # 1 +
        $this->create( 'phone', Attr::REQUIRED, Attr::MULTIPLE )            # m +
            ->apply( [$this, 'validatePhone'] );
        $this->create( 'fax-no', Attr::OPTIONAL, Attr::MULTIPLE )           # m
            ->apply( [$this, 'validatePhone'] );
        $this->create( 'e-mail', Attr::REQUIRED, Attr::MULTIPLE )           # m +
            ->apply( [$this, 'validateEmail'] );
        $this->create( 'org', Attr::OPTIONAL, Attr::MULTIPLE );             # m
        $this->create( 'nic-hdl', Attr::REQUIRED, Attr::SINGLE )            # 1 +
            ->apply( 'strtoupper' );
        $this->create( 'remarks', Attr::OPTIONAL, Attr::MULTIPLE );         # m
        $this->create( 'notify', Attr::OPTIONAL, Attr::MULTIPLE );          # m
        $this->create( 'abuse-mailbox', Attr::OPTIONAL, Attr::MULTIPLE )    # m
            ->apply( [$this, 'validateEmail'] );
        $this->create( 'mnt-by', Attr::REQUIRED, Attr::MULTIPLE );          # m +
        $this->create( 'source', Attr::REQUIRED, Attr::SINGLE )             # 1 +
            ->apply( 'strtoupper' );

        $this->setGeneratedAttribute( 'last-modified', Attr::SINGLE );
    }
}
