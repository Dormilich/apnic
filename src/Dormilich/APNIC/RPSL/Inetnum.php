<?php
// Inetnum.php

namespace Dormilich\APNIC\RPSL;

use Dormilich\APNIC\Object;
use Dormilich\APNIC\AttributeInterface as Attr;

class Inetnum extends Object
{
    /**
     * Create a INETNUM RIPE object.
     * 
     * Supported input formats:
     *  - IP range string (IP address - space - hyphen - space - IP address)
     *  - IP address/object & IP address/object
     *  - CIDR
     *  - IP address/object & CIDR prefix
     * 
     * @param mixed $address IP range, CIDR, or IP string/object.
     * @param mixed $value CIDR prefix or IP string/object.
     * @return self
     */
    public function __construct($address, $value = null)
    {
        $this->init();
        $this->setType('inetnum');
        $this->setKey('inetnum', $this->getIPRange($address, $value));
    }

    /**
     * Convert the various input formats to an IP range string. If the input 
     * fails any validation, the address parameter is returned unchanged.
     * 
     * @param mixed $address IP range, CIDR, or IP string/object.
     * @param mixed $value CIDR prefix or IP string/object.
     * @return string IP range string.
     */
    private function getIPRange($address, $value)
    {
        // check for range
        if (strpos($address, '-') !== false) {
            return $address;
        }
        // check for CIDR
        if (strpos($address, '/') !== false)  {
            $cidr = explode('/', $address);
            $range = $this->convertCIDR($cidr[0], $cidr[1]);
            if (!$range) {
                return $address;
            }
            return $range;
        }
        // check for separated CIDR
        if (is_numeric($value)) {
            $range = $this->convertCIDR($address, $value);
            if (!$range) {
                return $address;
            }
            return $range;
        }
        // try input as IP
        if ($value) {
            $start_num = ip2long((string) $address);
            $end_num   = ip2long((string) $value);

            if (false === $start_num or false === $end_num) {
                return $address;
            }

            if ($start_num < $end_num) {
                return long2ip($start_num) . ' - ' . long2ip($end_num);
            } 
            elseif ($start_num > $end_num) {
                return long2ip($end_num) . ' - ' . long2ip($start_num);
            }
            else {
                return long2ip($start_num);
            }
        }

        return (string) $address;
    }

    /**
     * Convert IP and CIDR prefix into an IP range. Returns FALSE if either 
     * input is invalid or the end IP would exceed the IPv4 range.
     * 
     * @param mixed $ip IP address.
     * @param integer $prefix CIDR prefix.
     * @return string IP range or FALSE.
     */
    private function convertCIDR($ip, $prefix)
    {
        $ipnum = ip2long((string) $ip);
        $prefix = filter_var($prefix, \FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 0, 'max_range' => 32]
        ]);

        if (false === $ipnum or false === $prefix) {
            return false;
        }

        $netsize = 1 << (32 - $prefix);
        $end_num = $ipnum + $netsize - 1;

        if ($end_num >= (1 << 32)) {
            return false;
        }

        return long2ip($ipnum) . ' - ' . long2ip($end_num);
    }

    /**
     * Defines attributes for the INETNUM RIPE object. 
     * 
     * @return void
     */
    protected function init()
    {
        $this->create('inetnum',     Attr::REQUIRED, Attr::SINGLE);
        $this->create('netname',     Attr::REQUIRED, Attr::SINGLE);
        $this->create('descr',       Attr::REQUIRED, Attr::MULTIPLE);
        $this->create('country',     Attr::REQUIRED, Attr::MULTIPLE);
        $this->create('geoloc',      Attr::OPTIONAL, Attr::SINGLE);
        $this->create('language',    Attr::OPTIONAL, Attr::MULTIPLE);
        $this->create('admin-c',     Attr::REQUIRED, Attr::MULTIPLE);
        $this->create('tech-c',      Attr::REQUIRED, Attr::MULTIPLE);
        $this->fixed('status',       Attr::REQUIRED, [
            'ALLOCATED PORTABLE', 'ALLOCATED NON-PORTABLE', 
            'ASSIGNED PORTABLE',  'ASSIGNED NON-PORTABLE',
        ]);
        $this->create('remarks',     Attr::OPTIONAL, Attr::MULTIPLE);
        $this->create('notify',      Attr::OPTIONAL, Attr::MULTIPLE);
        $this->create('mnt-by',      Attr::REQUIRED, Attr::MULTIPLE);
        $this->create('mnt-lower',   Attr::OPTIONAL, Attr::MULTIPLE);
        $this->create('mnt-routes',  Attr::OPTIONAL, Attr::MULTIPLE);
        $this->create('mnt-domains', Attr::OPTIONAL, Attr::MULTIPLE);
        $this->create('mnt-irt',     Attr::REQUIRED, Attr::MULTIPLE);
        $this->create('changed',     Attr::REQUIRED, Attr::MULTIPLE);
        $this->create('source',      Attr::REQUIRED, Attr::SINGLE);
    }
}
