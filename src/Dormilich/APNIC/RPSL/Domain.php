<?php
// Domain.php

namespace Dormilich\APNIC\RPSL;

use Dormilich\APNIC\Object;
use Dormilich\APNIC\AttributeInterface as Attr;
use Dormilich\APNIC\Exceptions\InvalidValueException;

class Domain extends Object
{
    const VERSION = '1.88';

    /**
     * Create a DOMAIN RPSL object.
     * 
     * @param string $value The reverse delegetion address/range.
     * @return self
     */
    public function __construct( $value )
    {
        $this->init();
        $this->setType( 'domain' );
        $this->setKey( [
            'domain' => $value,
        ] );
    }

    /**
     * Defines attributes for the DOMAIN RPSL object. 
     * 
     * @return void
     */
    protected function init()
    {
        $this->create( 'domain', Attr::REQUIRED, Attr::SINGLE );            # 1 +
        $this->create( 'descr', Attr::REQUIRED, Attr::MULTIPLE );           # m +
        $this->create( 'country', Attr::OPTIONAL, Attr::SINGLE );           # 1
        $this->create( 'org', Attr::OPTIONAL, Attr::MULTIPLE );             # m
        $this->create( 'admin-c', Attr::REQUIRED, Attr::MULTIPLE );         # m +
        $this->create( 'tech-c', Attr::REQUIRED, Attr::MULTIPLE );          # m +
        $this->create( 'zone-c', Attr::REQUIRED, Attr::MULTIPLE );          # m +
        $this->create( 'nserver', Attr::OPTIONAL, Attr::MULTIPLE );         # m
        $this->create( 'ds-rdata', Attr::OPTIONAL, Attr::MULTIPLE );        # m
        $this->create( 'remarks', Attr::OPTIONAL, Attr::MULTIPLE );         # m
        $this->create( 'notify', Attr::OPTIONAL, Attr::MULTIPLE );          # m
        $this->create( 'mnt-by', Attr::REQUIRED, Attr::MULTIPLE );          # m +
        $this->create( 'mnt-lower', Attr::OPTIONAL, Attr::MULTIPLE );       # m
        $this->create( 'source', Attr::REQUIRED, Attr::SINGLE )             # 1 +
            ->apply( 'strtoupper' );

        $this->setGeneratedAttribute( 'last-modified', Attr::SINGLE );
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
