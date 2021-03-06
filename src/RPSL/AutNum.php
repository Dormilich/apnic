<?php
// AutNum.php

namespace Dormilich\APNIC\RPSL;

use Dormilich\APNIC\AbstractObject;
use Dormilich\APNIC\AttributeInterface as Attr;
use Dormilich\APNIC\Exceptions\InvalidValueException;

class AutNum extends AbstractObject
{
    const VERSION = '1.88';

    /**
     * Create an AUTONOMOUS NUMBER (AUT-NUM) RPSL object.
     * 
     * @param string $value The ASN.
     * @return self
     */
    public function __construct( $value )
    {
        $this->init();
        $this->setType( 'aut-num' );
        $this->setKey( [
            'aut-num' => $value,
        ] );
    }

    /**
     * Defines attributes for the AUT-NUM RPSL object. 
     * 
     * @return void
     */
    protected function init()
    {
        $this->create( 'aut-num', Attr::REQUIRED, Attr::SINGLE );           # 1 +
        $this->create( 'as-name', Attr::REQUIRED, Attr::SINGLE );           # 1 +
        $this->create( 'descr', Attr::REQUIRED, Attr::MULTIPLE );           # m +
        $this->create( 'country', Attr::REQUIRED, Attr::SINGLE );           # 1 +
        $this->create( 'member-of', Attr::OPTIONAL, Attr::MULTIPLE );       # m
        $this->create( 'import-via', Attr::OPTIONAL, Attr::MULTIPLE );      # m
        $this->create( 'import', Attr::OPTIONAL, Attr::MULTIPLE );          # m
        $this->create( 'mp-import', Attr::OPTIONAL, Attr::MULTIPLE );       # m
        $this->create( 'export-via', Attr::OPTIONAL, Attr::MULTIPLE );      # m
        $this->create( 'export', Attr::OPTIONAL, Attr::MULTIPLE );          # m
        $this->create( 'mp-export', Attr::OPTIONAL, Attr::MULTIPLE );       # m
        $this->create( 'default', Attr::OPTIONAL, Attr::MULTIPLE );         # m
        $this->create( 'mp-default', Attr::OPTIONAL, Attr::MULTIPLE );      # m
        $this->create( 'remarks', Attr::OPTIONAL, Attr::MULTIPLE );         # m
        $this->create( 'org', Attr::OPTIONAL, Attr::SINGLE );               # 1
        $this->create( 'admin-c', Attr::REQUIRED, Attr::MULTIPLE );         # m +
        $this->create( 'tech-c', Attr::REQUIRED, Attr::MULTIPLE );          # m +
        $this->create( 'abuse-c', Attr::OPTIONAL, Attr::SINGLE );           # 1
        $this->create( 'notify', Attr::OPTIONAL, Attr::MULTIPLE );          # m
        $this->create( 'mnt-lower', Attr::OPTIONAL, Attr::MULTIPLE );       # m
        $this->create( 'mnt-routes', Attr::OPTIONAL, Attr::MULTIPLE );      # m
        $this->create( 'mnt-by', Attr::REQUIRED, Attr::MULTIPLE );          # m +
        $this->create( 'mnt-irt', Attr::REQUIRED, Attr::MULTIPLE );         # m +
        $this->create( 'source', Attr::REQUIRED, Attr::SINGLE )             # 1 +
            ->apply( 'strtoupper' );

        $this->setGeneratedAttribute( 'last-modified', Attr::SINGLE );
    }

    public function autNum( $input )
    {
        $input = strtoupper( $input );

        if ( preg_match( '~^AS\d+$~', $input ) ) {
            return $input;
        }

        throw new InvalidValueException( 'Invalid AS number' );
    }
}
