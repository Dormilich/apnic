<?php
// Route.php

namespace Dormilich\APNIC\RPSL;

use Dormilich\APNIC\Object;
use Dormilich\APNIC\AttributeInterface as Attr;
use Dormilich\APNIC\Exceptions\InvalidValueException;

class Route extends Object
{
    const VERSION = '1.69';

    /**
     * Create a ROUTE RPSL object.
     * 
     * @param string $value The IPv4 address prefix of the route.
     *      Forms a combined primary key with the 'origin' attribute.
     * @return self
     */
    public function __construct($value)
    {
        $this->init();
        $this->setType('route');
        $this->parseKey($value);
    }

    /**
     * Extract origin and route from the primary key candidate. 
     * 
     * @param string $value 
     * @return void
     */
    private function parseKey( $value )
    {
        if ( is_scalar( $value ) ) {
            $value = $this->setOrigin( $value );
            $this->setKey( 'route', $value );
        }
    }

    /**
     * Extract the origin part from the primary key candidate.
     * 
     * @param string $value String passed as PK.
     * @return string Input with the ASN removed.
     */
    private function setOrigin( $value )
    {
        if ( preg_match( '/AS\d+/', $value, $match ) === 1 ) {
            $this->set( 'origin', $match[ 0 ] );
            $value = str_replace( $match[ 0 ], '', $value );
        }

        return trim( $value );
    }

    /**
     * Get the composite primary key. If either attribute is not set, return null.
     * 
     * @return string|NULL
     */
    public function getPrimaryKey()
    {
        $route  = $this->get( 'route' );
        $origin = $this->get( 'origin' );

        if ( $route and $origin ) {
            return $route . $origin;
        }

        return NULL;
    }

    /**
     * Defines attributes for the ROUTE RPSL object. 
     * 
     * @return void
     */
    protected function init()
    {
        $this->create( 'route', Attr::REQUIRED, Attr::SINGLE );         # 1 +
        $this->create( 'descr', Attr::REQUIRED, Attr::MULTIPLE );       # m +
        $this->create( 'country', Attr::OPTIONAL, Attr::SINGLE );       # 1
        $this->create( 'origin', Attr::REQUIRED, Attr::SINGLE );        # 1 +
        $this->create( 'holes', Attr::OPTIONAL, Attr::MULTIPLE );       # m
        $this->create( 'member-of', Attr::OPTIONAL, Attr::MULTIPLE );   # m
        $this->create( 'inject', Attr::OPTIONAL, Attr::MULTIPLE );      # m
        $this->create( 'aggr-mtd', Attr::OPTIONAL, Attr::SINGLE );      # 1
        $this->create( 'aggr-bndry', Attr::OPTIONAL, Attr::SINGLE );    # 1
        $this->create( 'export-comps', Attr::OPTIONAL, Attr::SINGLE );  # 1
        $this->create( 'components', Attr::OPTIONAL, Attr::SINGLE );    # 1
        $this->create( 'remarks', Attr::OPTIONAL, Attr::MULTIPLE );     # m
        $this->create( 'notify', Attr::OPTIONAL, Attr::MULTIPLE );      # m
        $this->create( 'mnt-lower', Attr::OPTIONAL, Attr::MULTIPLE );   # m
        $this->create( 'mnt-routes', Attr::OPTIONAL, Attr::MULTIPLE );  # m
        $this->create( 'mnt-by', Attr::REQUIRED, Attr::MULTIPLE );      # m +
        $this->create( 'changed', Attr::REQUIRED, Attr::MULTIPLE );     # m +
        $this->create( 'source', Attr::REQUIRED, Attr::SINGLE )->apply( 'strtoupper' );
    }

    public function route( $input )
    {
        if ( strpos( $input, '/' ) ) {
            list( $ip, $length ) = explode( '/', $input, 2 );

            $ip = filter_var( $ip, \FILTER_VALIDATE_IP, \FILTER_FLAG_IPV4 );
            $length = filter_var( $length, \FILTER_VALIDATE_INT, 
                [ 'options' => [ 'min_range' => 0, 'max_range' => 32 ] ] );

            if ( $ip and is_int( $length ) ) {
                return $input;
            }
        }

        throw new InvalidValueException( 'Invalid IPv4 route' );
    }

    public function origin( $input )
    {
        if ( preg_match( '~^AS\d+$~', $input ) ) {
            return $input;
        }

        throw new InvalidValueException( 'Invalid AS number' );
    }
}
