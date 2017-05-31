<?php
// FilterSet.php

namespace Dormilich\APNIC\RPSL;

use Dormilich\APNIC\Object;
use Dormilich\APNIC\AttributeInterface as Attr;
use Dormilich\APNIC\Exceptions\InvalidValueException;

class FilterSet extends Object
{
    const VERSION = '1.88';

    /**
     * Create a FILTER-SET RPSL object.
     * 
     * @param string $value The name of the set (of routers).
     * @return self
     */
    public function __construct( $value )
    {
        $this->init();
        $this->setType( 'filter-set' );
        $this->setKey( [
            'filter-set' => $value,
        ] );
    }

    /**
     * Defines attributes for the FILTER-SET RPSL object. 
     * 
     * @return void
     */
    protected function init()
    {
        $this->create( 'filter-set', Attr::REQUIRED, Attr::SINGLE );        # 1 +
        $this->create( 'descr', Attr::REQUIRED, Attr::MULTIPLE );           # m +
        $this->create( 'filter', Attr::OPTIONAL, Attr::SINGLE );            # 1
        $this->create( 'mp-filter', Attr::OPTIONAL, Attr::SINGLE );         # 1
        $this->create( 'remarks', Attr::OPTIONAL, Attr::MULTIPLE );         # m
        $this->create( 'org', Attr::OPTIONAL, Attr::MULTIPLE );             # m
        $this->create( 'tech-c', Attr::REQUIRED, Attr::MULTIPLE );          # m +
        $this->create( 'admin-c', Attr::REQUIRED, Attr::MULTIPLE );         # m +
        $this->create( 'notify', Attr::OPTIONAL, Attr::MULTIPLE );          # m
        $this->create( 'mnt-by', Attr::REQUIRED, Attr::MULTIPLE );          # m +
        $this->create( 'mnt-lower', Attr::OPTIONAL, Attr::MULTIPLE );       # m
        $this->create( 'source', Attr::REQUIRED, Attr::SINGLE )             # 1 +
            ->apply( 'strtoupper' );

        $this->setGeneratedAttribute( 'last-modified', Attr::SINGLE );
    }

    public function filterSet( $input )
    {
        $input = strtoupper( $input );

       if ( strpos( $input, 'FLTR-' ) === 0) {
            return $input;
        }

        throw new InvalidValueException( 'Invalid filter-set name' );
    }
}
