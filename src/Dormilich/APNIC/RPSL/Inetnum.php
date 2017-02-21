<?php
// Inetnum.php

namespace Dormilich\APNIC\RPSL;

use Dormilich\APNIC\Object;
use Dormilich\APNIC\AttributeInterface as Attr;
use Dormilich\APNIC\Exceptions\InvalidValueException;

class Inetnum extends Object
{
    const VERSION = '1.69';

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
     *  - IP address & IP address
     *  - CIDR
     * 
     * @param mixed $start IP range, CIDR, or IP address.
     * @param mixed $end IP address.
     * @return self
     */
    public function __construct( $start, $end = null )
    {
        $this->init();
        $this->setType( 'inetnum' );
        $this->setKey( [
            'inetnum' => $this->getIPRange( $start, $end ),
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
        $this->create( 'changed', Attr::REQUIRED, Attr::MULTIPLE );         # m +
        $this->create( 'source', Attr::REQUIRED, Attr::SINGLE )             # 1 +
            ->apply( 'strtoupper' );
    }

    public function inetnum( $input )
    {
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
     * Convert the various input formats to an IP range string. If the input 
     * fails any validation, the address parameter is returned unchanged and 
     * likely to fail in the final validation.
     * 
     * @param mixed $address IP range, CIDR, or IP address.
     * @param mixed $end IP address.
     * @return string IP range string or the unchanged input.
     */
    private function getIPRange( $address, $end )
    {
        // check for range
        if ( strpos( $address, '-' ) !== false ) {
            return $address;
        }
        // check for CIDR
        if ( strpos( $address, '/' ) !== false )  {
            return $this->fromCIDR( $address );
        }
        // try input as IP
        if ( $end ) {
            return $this->fromIPs( $address, $end );
        }

        return $address;
    }

    /**
     * Convert a CIDR into an IP range. Returns the input if it is invalid or 
     * the end IP would exceed the IPv4 range.
     * 
     * @param string $address CIDR.
     * @return string IP range or original input.
     */
    private function fromCIDR( $address )
    {
        list( $ip, $prefix ) = explode( '/', $address, 2 );

        $ip = filter_var( $ip, \FILTER_VALIDATE_IP, \FILTER_FLAG_IPV4 );
        $prefix = filter_var( $prefix, \FILTER_VALIDATE_INT, 
            [ 'options' => [ 'min_range' => 0, 'max_range' => 32 ] ] );

        if ( false === $ip or false === $prefix) {
            return $address;
        }

        $netsize = 1 << ( 32 - $prefix );
        $end_num = ip2long( $ip ) + $netsize - 1;
        
        if ( $end_num < ( 1 << 32 ) ) {
            return $ip . ' - ' . long2ip( $end_num );
        }

        return $address;
    }

    /**
     * Validate both input parameters as IPs.
     * 
     * @param string $address Start IP address.
     * @param string $end End IP address.
     * @return string A valid handle or the first parameter.
     */
    private function fromIPs( $address, $end )
    {
        $start = filter_var( $address, \FILTER_VALIDATE_IP, \FILTER_FLAG_IPV4 );
        $end = filter_var( $end, \FILTER_VALIDATE_IP, \FILTER_FLAG_IPV4 );

        if ( $start and $end ) {
            return $start . ' - ' . $end;
        }

        return $address;
    }
}
