<?php
// Inet6num.php

namespace Dormilich\APNIC\RPSL;

use Dormilich\APNIC\AbstractObject;
use Dormilich\APNIC\AttributeInterface as Attr;
use Dormilich\APNIC\Exceptions\InvalidValueException;

class Inet6num extends AbstractObject
{
    const VERSION = '1.88';

    const STATUS = [
        'ALLOCATED PORTABLE',
        'ALLOCATED NON-PORTABLE',
        'ASSIGNED PORTABLE',
        'ASSIGNED NON-PORTABLE',
    ];

    /**
     * Create a INET6NUM RPSL object
     * 
     * @param string $value A block of or a single IPv6 address.
     * @return self
     */
    public function __construct( $value )
    {
        $this->init();
        $this->setType( 'inet6num' );
        $this->setKey( [
            'inet6num' => $value,
        ] );
    }

    /**
     * Defines attributes for the INET6NUM RPSL object. 
     * 
     * @return void
     */
    protected function init()
    {
        $this->create( 'inet6num', Attr::REQUIRED, Attr::SINGLE );          # 1 +
        $this->create( 'netname', Attr::REQUIRED, Attr::SINGLE );           # 1 +
        $this->create( 'descr', Attr::REQUIRED, Attr::MULTIPLE );           # m +
        $this->create( 'country', Attr::REQUIRED, Attr::MULTIPLE );         # m +
        $this->create( 'geoloc', Attr::OPTIONAL, Attr::SINGLE );            # 1
        $this->create( 'language', Attr::OPTIONAL, Attr::MULTIPLE );        # m
        $this->create( 'org', Attr::OPTIONAL, Attr::SINGLE );               # 1
        $this->create( 'admin-c', Attr::REQUIRED, Attr::MULTIPLE );         # m +
        $this->create( 'tech-c', Attr::REQUIRED, Attr::MULTIPLE );          # m +
        $this->create( 'abuse-c', Attr::OPTIONAL, Attr::SINGLE );           # 1
        $this->create( 'status', Attr::REQUIRED, Attr::SINGLE );            # 1 +
        $this->create( 'remarks', Attr::OPTIONAL, Attr::MULTIPLE );         # m
        $this->create( 'notify', Attr::OPTIONAL, Attr::MULTIPLE );          # m
        $this->create( 'mnt-by', Attr::REQUIRED, Attr::MULTIPLE );          # m +
        $this->create( 'mnt-lower', Attr::OPTIONAL, Attr::MULTIPLE );       # m
        $this->create( 'mnt-routes', Attr::OPTIONAL, Attr::MULTIPLE );      # m
        $this->create( 'mnt-irt', Attr::REQUIRED, Attr::MULTIPLE );         # m +
        $this->create( 'source', Attr::REQUIRED, Attr::SINGLE )             # 1 +
            ->apply( 'strtoupper' );

        $this->setGeneratedAttribute( 'last-modified', Attr::SINGLE );
    }

    public function inet6num( $input )
    {
        if ( strpos( $input, '/' ) ) {
            list( $ip, $length ) = explode( '/', $input, 2 );

            $ip = filter_var( $ip, \FILTER_VALIDATE_IP, \FILTER_FLAG_IPV6 );
            $length = filter_var( $length, \FILTER_VALIDATE_INT, 
                [ 'options' => [ 'min_range' => 0, 'max_range' => 128 ] ] );

            if ( $ip and false !== $length ) {
                return $input;
            }
        }

        throw new InvalidValueException( 'Invalid IPv6 address range' );
    }

    public function status( $input )
    {
        $input = strtoupper( $input );

        if ( in_array( $input, self::STATUS, true )) {
            return $input;
        }

        throw new InvalidValueException( 'Invalid status for the Inet6num object' );
    }
}
