<?php
// AsSet.php

namespace Dormilich\APNIC\RPSL;

use Dormilich\APNIC\Object;
use Dormilich\APNIC\AttributeInterface as Attr;

class AsSet extends Object
{
    const VERSION = '1.88';

    /**
     * Create an AS-SET RPSL object.
     * 
     * @param string $value The name of the AS-Set.
     * @return self
     */
    public function __construct( $value )
    {
        $this->init();
        $this->setType( 'as-set' );
        $this->setKey( [
            'as-set' => $value,
        ] );
    }

    /**
     * Defines attributes for the AS-SET RPSL object. 
     * 
     * @return void
     */
    protected function init()
    {
        $this->create( 'as-set', Attr::REQUIRED, Attr::SINGLE );            # 1 +
        $this->create( 'descr', Attr::REQUIRED, Attr::MULTIPLE );           # m +
        $this->create( 'country', Attr::OPTIONAL, Attr::SINGLE );           # 1
        $this->create( 'members', Attr::OPTIONAL, Attr::MULTIPLE );         # m
        $this->create( 'mbrs-by-ref', Attr::OPTIONAL, Attr::MULTIPLE );     # m
        $this->create( 'remarks', Attr::OPTIONAL, Attr::MULTIPLE );         # m
        $this->create( 'org', Attr::OPTIONAL, Attr::MULTIPLE );             # m
        $this->create( 'tech-c', Attr::REQUIRED, Attr::MULTIPLE );          # m +
        $this->create( 'admin-c', Attr::REQUIRED, Attr::MULTIPLE );         # m +
        $this->create( 'notify', Attr::OPTIONAL, Attr::MULTIPLE );          # m
        $this->create( 'mnt-by', Attr::REQUIRED, Attr::MULTIPLE );          # m +
        $this->create( 'mnt-lower', Attr::OPTIONAL, Attr::MULTIPLE );       # m +
        $this->create( 'source', Attr::REQUIRED, Attr::SINGLE )             # 1 +
            ->apply( 'strtoupper' );

        $this->setGeneratedAttribute( 'last-modified', Attr::SINGLE );
    }
}
