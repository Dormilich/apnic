<?php
// Mntner.php

namespace Dormilich\APNIC\RPSL;

use Dormilich\APNIC\AbstractObject;
use Dormilich\APNIC\AttributeInterface as Attr;

class Mntner extends AbstractObject
{
    const VERSION = '1.88';

    /**
     * Create a maintainer (MNTNER) RPSL object.
     * 
     * @param string $value Handle of the maintainer that is represented by this object.
     * @return self
     */
    public function __construct( $value )
    {
        $this->init();
        $this->setType( 'mntner' );
        $this->setKey( [
            'mntner' => $value,
        ] );
    }

    /**
     * Defines attributes for the MNTNER RPSL object. 
     * 
     * @return void
     */
    protected function init()
    {
        // MAINT-{ISO 3166}-{NAME} ... except APNIC sys-mntners
        $this->create( 'mntner', Attr::REQUIRED, Attr::SINGLE )             # 1 +
            ->apply( 'strtoupper' );
        $this->create( 'descr', Attr::REQUIRED, Attr::MULTIPLE );           # m +
        $this->create( 'country', Attr::OPTIONAL, Attr::SINGLE );           # 1
        $this->create( 'org', Attr::OPTIONAL, Attr::MULTIPLE );             # m
        $this->create( 'admin-c', Attr::REQUIRED, Attr::MULTIPLE );         # m +
        $this->create( 'tech-c', Attr::OPTIONAL, Attr::MULTIPLE );          # m
        $this->create( 'upd-to', Attr::REQUIRED, Attr::MULTIPLE )           # m +
            ->apply( [$this, 'validateEmail'] );
        $this->create( 'mnt-nfy', Attr::OPTIONAL, Attr::MULTIPLE )          # m
            ->apply( [$this, 'validateEmail'] );
        $this->create( 'auth', Attr::REQUIRED, Attr::MULTIPLE );            # m +
        $this->create( 'remarks', Attr::OPTIONAL, Attr::MULTIPLE );         # m
        $this->create( 'notify', Attr::OPTIONAL, Attr::MULTIPLE );          # m
        $this->create( 'abuse-mailbox', Attr::OPTIONAL, Attr::MULTIPLE )    # m
            ->apply( [$this, 'validateEmail'] );
        $this->create( 'mnt-by', Attr::REQUIRED, Attr::MULTIPLE );          # m +
        $this->create( 'referral-by', Attr::OPTIONAL, Attr::SINGLE );       # 1
        $this->create( 'source', Attr::REQUIRED, Attr::SINGLE )             # 1 +
            ->apply( 'strtoupper' );

        $this->setGeneratedAttribute( 'last-modified', Attr::SINGLE );
    }
}
