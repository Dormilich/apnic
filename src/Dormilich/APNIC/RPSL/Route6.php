<?php
// Route6.php

namespace Dormilich\APNIC\RPSL;

use Dormilich\APNIC\Object;
use Dormilich\APNIC\AttributeInterface as Attr;
use Dormilich\APNIC\Exceptions\InvalidValueException;

class Route6 extends Object
{
    /**
     * Create a ROUTE6 RPSL object.
     * 
     * @param string $value The IPv6 address prefix of the route.
     *      Forms a combined primary key with the 'origin' attribute.
     * @return self
     */
    public function __construct($value)
    {
        $this->init();
        $this->setType('route6');
        $this->setKey('route6', $value);
    }

    /**
     * Defines attributes for the ROUTE6 RPSL object. 
     * 
     * @return void
     */
    protected function init()
    {
        $this->create('route6',       Attr::REQUIRED, Attr::SINGLE);
        $this->create('descr',        Attr::REQUIRED, Attr::MULTIPLE);
        $this->create('country',      Attr::OPTIONAL, Attr::SINGLE);
        $this->create('origin',       Attr::REQUIRED, Attr::SINGLE);
        $this->create('holes',        Attr::OPTIONAL, Attr::MULTIPLE);
        $this->create('member-of',    Attr::OPTIONAL, Attr::MULTIPLE);
        $this->create('inject',       Attr::OPTIONAL, Attr::MULTIPLE);
        $this->create('aggr-mtd',     Attr::OPTIONAL, Attr::SINGLE);
        $this->create('aggr-bndry',   Attr::OPTIONAL, Attr::SINGLE);
        $this->create('export-comps', Attr::OPTIONAL, Attr::SINGLE);
        $this->create('components',   Attr::OPTIONAL, Attr::SINGLE);
        $this->create('remarks',      Attr::OPTIONAL, Attr::MULTIPLE);
        $this->create('notify',       Attr::OPTIONAL, Attr::MULTIPLE);
        $this->create('mnt-lower',    Attr::OPTIONAL, Attr::MULTIPLE);
        $this->create('mnt-routes',   Attr::OPTIONAL, Attr::MULTIPLE);
        $this->create('mnt-by',       Attr::REQUIRED, Attr::MULTIPLE);
        $this->create('changed',      Attr::REQUIRED, Attr::MULTIPLE);
        $this->create('source',       Attr::REQUIRED, Attr::SINGLE)->apply('strtoupper');
    }

    public function route6( $input )
    {
        if ( strpos( $input, '/' ) ) {
            list( $ip, $length ) = explode( '/', $input, 2 );

            $ip = filter_var( $ip, \FILTER_VALIDATE_IP, \FILTER_FLAG_IPV6 );
            $length = filter_var( $length, \FILTER_VALIDATE_INT, 
                [ 'options' => [ 'min_range' => 0, 'max_range' => 32 ] ] );

            if ( $ip and is_int( $length ) ) {
                return $input;
            }
        }

        throw new InvalidValueException( 'Invalid IPv6 route' );
    }

    public function origin( $input )
    {
        if ( preg_match( '~^AS\d+$~', $input ) ) {
            return $input;
        }

        throw new InvalidValueException( 'Invalid AS number' );
    }
}
