<?php
// RouteSet.php

namespace Dormilich\APNIC\RPSL;

use Dormilich\APNIC\AbstractObject;
use Dormilich\APNIC\AttributeInterface as Attr;

class RouteSet extends AbstractObject
{
    const VERSION = '1.88';

    /**
     * Create a ROUTE-SET RPSL object.
     * 
     * @param string $value The name of the set (of route prefixes).
     * @return self
     */
    public function __construct($value)
    {
        $this->init();
        $this->setType( 'route-set' );
        $this->setKey( [
            'route-set' => $value,
        ] );
    }

    /**
     * Defines attributes for the ROUTE-SET RPSL object. 
     * 
     * @return void
     */
    protected function init()
    {
        $this->create( 'route-set', Attr::REQUIRED, Attr::SINGLE );         # 1 +
        $this->create( 'descr', Attr::REQUIRED, Attr::MULTIPLE );           # m +
        $this->create( 'members', Attr::OPTIONAL, Attr::MULTIPLE );         # m
        $this->create( 'mp-members', Attr::OPTIONAL, Attr::MULTIPLE );      # m
        $this->create( 'mbrs-by-ref', Attr::OPTIONAL, Attr::MULTIPLE );     # m
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
}
