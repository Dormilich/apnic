<?php
// AsBlock.php

namespace Dormilich\APNIC\RPSL;

use Dormilich\APNIC\AbstractObject;
use Dormilich\APNIC\AttributeInterface as Attr;

class AsBlock extends AbstractObject
{
    const VERSION = '1.88';

    /**
     * Create a AS-BLOCK object.
     * 
     * @param string $value The range of AS numbers in this block.
     * @return self
     */
    public function __construct( $value )
    {
        $this->init();
        $this->setType( 'as-block' );
        $this->setKey( [
            'as-block' => $value,
        ] );
    }

    /**
     * Defines attributes for the AS-BLOCK RPSL object. 
     * 
     * @return void
     */
    protected function init()
    {
        $this->create( 'as-block', Attr::REQUIRED, Attr::SINGLE );          # 1 +
        $this->create( 'descr', Attr::OPTIONAL, Attr::MULTIPLE );           # m
        $this->create( 'remarks', Attr::OPTIONAL, Attr::MULTIPLE );         # m
        $this->create( 'country', Attr::OPTIONAL, Attr::SINGLE );           # 1
        $this->create( 'org', Attr::OPTIONAL, Attr::MULTIPLE );             # m
        $this->create( 'admin-c', Attr::REQUIRED, Attr::MULTIPLE );         # m +
        $this->create( 'tech-c', Attr::REQUIRED, Attr::MULTIPLE );          # m +
        $this->create( 'notify', Attr::OPTIONAL, Attr::MULTIPLE );          # m
        $this->create( 'mnt-by', Attr::REQUIRED, Attr::MULTIPLE );          # m +
        $this->create( 'mnt-lower', Attr::OPTIONAL, Attr::MULTIPLE );       # m
        $this->create( 'source', Attr::REQUIRED, Attr::SINGLE )             # 1 +
            ->apply( 'strtoupper' );

        $this->setGeneratedAttribute( 'last-modified', Attr::SINGLE );
    }
}
