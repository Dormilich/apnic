<?php
// Organisation.php

namespace Dormilich\APNIC\RPSL;

use Dormilich\APNIC\AbstractObject;
use Dormilich\APNIC\AttributeInterface as Attr;

class Organisation extends AbstractObject
{
    const VERSION = '1.88';

    /**
     * Create a ORGANISATION object.
     * 
     * @param string $value The name of the organisation.
     * @return self
     */
    public function __construct( $value = 'AUTO-1' )
    {
        $this->init();
        $this->setType( 'organisation' );
        $this->setKey( [
            'organisation' => $value,
        ] );
    }

    /**
     * Defines attributes for the ORGANISATION RPSL object. 
     * 
     * @return void
     */
    protected function init()
    {
        $this->create( 'organisation', Attr::REQUIRED, Attr::SINGLE )       # 1 +
            ->apply( 'strtoupper' );
        $this->create( 'org-name', Attr::REQUIRED, Attr::SINGLE );          # 1 +
        $this->create( 'descr', Attr::OPTIONAL, Attr::MULTIPLE );           # m
        $this->create( 'country', Attr::REQUIRED, Attr::SINGLE );           # 1 +
        $this->create( 'remarks', Attr::OPTIONAL, Attr::MULTIPLE );         # m
        $this->create( 'address', Attr::REQUIRED, Attr::MULTIPLE );         # m +
        $this->create( 'phone', Attr::OPTIONAL, Attr::MULTIPLE )            # m
            ->apply( [$this, 'validatePhone'] );
        $this->create( 'fax-no', Attr::OPTIONAL, Attr::MULTIPLE )           # m
            ->apply( [$this, 'validatePhone'] );
        $this->create( 'e-mail', Attr::REQUIRED, Attr::MULTIPLE )           # m +
            ->apply( [$this, 'validateEmail'] );
        $this->create( 'org', Attr::OPTIONAL, Attr::MULTIPLE );             # m
        $this->create( 'admin-c', Attr::OPTIONAL, Attr::MULTIPLE );         # m
        $this->create( 'tech-c', Attr::OPTIONAL, Attr::MULTIPLE );          # m
        $this->create( 'ref-nfy', Attr::OPTIONAL, Attr::MULTIPLE );         # m
        $this->create( 'mnt-ref', Attr::REQUIRED, Attr::MULTIPLE );         # m +
        $this->create( 'notify', Attr::OPTIONAL, Attr::MULTIPLE );          # m
        $this->create( 'mnt-by', Attr::REQUIRED, Attr::MULTIPLE );          # m +
        $this->create( 'source', Attr::REQUIRED, Attr::SINGLE )             # 1 +
            ->apply( 'strtoupper' );

        $this->setGeneratedAttribute( 'last-modified', Attr::SINGLE );
    }
}
