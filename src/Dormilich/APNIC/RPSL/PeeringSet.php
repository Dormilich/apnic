<?php
// PeeringSet.php

namespace Dormilich\APNIC\RPSL;

use Dormilich\APNIC\Object;
use Dormilich\APNIC\AttributeInterface as Attr;
use Dormilich\APNIC\Exceptions\InvalidValueException;

class PeeringSet extends Object
{
    const VERSION = '1.69';

    /**
     * Create a PEERING-SET RPSL object.
     * 
     * @param string $value The name of the set.
     * @return self
     */
    public function __construct($value)
    {
        $this->init();
        $this->setType( 'peering-set' );
        $this->setKey( [
            'peering-set' => $value,
        ] );
    }

    /**
     * Defines attributes for the PEERING-SET RPSL object. 
     * 
     * @return void
     */
    protected function init()
    {
        $this->create( 'peering-set', Attr::REQUIRED, Attr::SINGLE );       # 1 +
        $this->create( 'descr', Attr::REQUIRED, Attr::MULTIPLE );           # m +
        $this->create( 'peering', Attr::OPTIONAL, Attr::MULTIPLE );         # m
        $this->create( 'mp-peering', Attr::OPTIONAL, Attr::MULTIPLE );      # m
        $this->create( 'remarks', Attr::OPTIONAL, Attr::MULTIPLE );         # m
        $this->create( 'org', Attr::OPTIONAL, Attr::MULTIPLE );             # m
        $this->create( 'tech-c', Attr::REQUIRED, Attr::MULTIPLE );          # m +
        $this->create( 'admin-c', Attr::REQUIRED, Attr::MULTIPLE );         # m +
        $this->create( 'notify', Attr::OPTIONAL, Attr::MULTIPLE );          # m
        $this->create( 'mnt-by', Attr::REQUIRED, Attr::MULTIPLE );          # m +
        $this->create( 'mnt-lower', Attr::OPTIONAL, Attr::MULTIPLE );       # m
        $this->create( 'changed', Attr::REQUIRED, Attr::MULTIPLE );         # m +
        $this->create( 'source', Attr::REQUIRED, Attr::SINGLE )             # 1 +
            ->apply( 'strtoupper' );
    }

    public function peeringSet( $input )
    {
        $input = strtoupper( $input );

        if ( strpos( $input, 'PRNG-' ) === 0) {
            return $input;
        }

        throw new InvalidValueException( 'Invalid peering-set name' );
    }

    /**
     * Check if any of the required Attributes or their combinations are undefined.
     * 
     * @return boolean
     */
    public function isValid()
    {
        $peer4 = $this->attr( 'peering' )->isDefined();
        $peer6 = $this->attr( 'mp-peering' )->isDefined();

        return parent::isValid() and ( $peer4 or $peer6 );
    }
}
