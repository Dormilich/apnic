<?php
// Inetnum.php

namespace Dormilich\APNIC\RPSL;

use Dormilich\APNIC\AbstractObject;
use Dormilich\APNIC\AttributeInterface as Attr;
use Dormilich\APNIC\Exceptions\InvalidValueException;

class Inetnum extends AbstractObject
{
    const VERSION = '1.88';

    const STATUS = [
        'ALLOCATED PORTABLE',
        'ALLOCATED NON-PORTABLE',
        'ASSIGNED PORTABLE',
        'ASSIGNED NON-PORTABLE',
    ];

    /**
     * Create an INETNUM RPSL object.
     * 
     * Supported input formats:
     *  - IP range string (IP address - space - hyphen - space - IP address)
     *  - CIDR
     * 
     * @param mixed $value IP range or CIDR.
     * @return self
     */
    public function __construct( $value )
    {
        $this->init();
        $this->setType( 'inetnum' );
        $this->setKey( [
            'inetnum' => $value,
        ] );
    }

    /**
     * Defines attributes for the INETNUM RPSL object. 
     * 
     * @return void
     */
    protected function init()
    {
        $this->create( 'inetnum', Attr::REQUIRED, Attr::SINGLE );           # 1 +
        $this->create( 'netname', Attr::REQUIRED, Attr::SINGLE );           # 1 +
        $this->create( 'descr', Attr::REQUIRED, Attr::MULTIPLE );           # m +
        $this->create( 'country', Attr::REQUIRED, Attr::MULTIPLE );         # m +
        $this->create( 'geoloc', Attr::OPTIONAL, Attr::SINGLE );            # 1
        $this->create( 'language', Attr::OPTIONAL, Attr::MULTIPLE );        # m
        $this->create( 'org', Attr::OPTIONAL, Attr::SINGLE );               # 1
        $this->create( 'admin-c', Attr::REQUIRED, Attr::MULTIPLE );         # m +
        $this->create( 'tech-c', Attr::REQUIRED, Attr::MULTIPLE );          # m +
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

    public function inetnum( $input )
    {
       if ( strpos( $input, '/' ) !== false )  {
            $input = $this->fromCIDR( $input );
        }

        $ip = explode( '-', $input );
        $ip = array_map( 'trim', $ip );
        $ip = array_filter( $ip, function ( $addr ) {
            return filter_var( $addr, \FILTER_VALIDATE_IP, \FILTER_FLAG_IPV4 );
        });

        if ( count( $ip ) === 2 ) {
            return implode( ' - ', $ip );
        }

        throw new InvalidValueException( 'Invalid IPv4 address range' );
    }

    public function status( $value )
    {
        $value = strtoupper( $value );

        if ( in_array( $value, self::STATUS, true ) ) {
            return $value;
        }

        throw new InvalidValueException( 'Invalid status for the Inetnum object' );
    }

    /**
     * Convert a CIDR into an IP range. 
     * 
     * @param string $cidr CIDR.
     * @return string IP range or original input.
     * @throws InvalidValueException Not an IPv4 CIDR.
     */
    private function fromCIDR( $cidr )
    {
        list( $ip, $prefix ) = explode( '/', $cidr, 2 );

        $ip = filter_var( $ip, \FILTER_VALIDATE_IP, \FILTER_FLAG_IPV4 );
        $prefix = filter_var( $prefix, \FILTER_VALIDATE_INT, 
            [ 'options' => [ 'min_range' => 0, 'max_range' => 32 ] ] );

        if ( false !== $ip and false !== $prefix) {
            return $this->ipRangeFromCidr( $ip, $prefix );
        }

        throw new InvalidValueException( 'Invalid IPv4 CIDR' );
    }

    /**
     * Convert the CIDR parts into an IP range. 
     * 
     * @param string $cidr CIDR.
     * @return string IP range or original input.
     * @throws InvalidValueException End IP by prefix length exceeds IPv4 space.
     */
    private function ipRangeFromCidr( $ip, $prefixLength )
    {
        $netsize = 1 << ( 32 - $prefixLength );
        $end_num = ip2long( $ip ) + $netsize - 1;
        
        if ( $end_num < ( 1 << 32 ) ) {
            return $ip . ' - ' . long2ip( $end_num );
        }

        throw new InvalidValueException( 'Invalid IPv4 CIDR' );
    }
}
