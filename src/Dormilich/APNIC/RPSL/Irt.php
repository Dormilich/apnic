<?php
// Irt.php

namespace Dormilich\APNIC\RPSL;

use Dormilich\APNIC\Object;
use Dormilich\APNIC\AttributeInterface as Attr;
use Dormilich\APNIC\Exceptions\InvalidValueException;

class Irt extends Object
{
    const VERSION = '1.69';

    /**
     * Create an incident response team (IRT) RPSL object.
     * 
     * @param string $value The name for the response team.
     * @return self
     */
    public function __construct( $value )
    {
        $this->init();
        $this->setType( 'irt' );
        $this->setKey( [
            'irt' => $value,
        ] );
    }

    /**
     * Defines attributes for the IRT RPSL object. 
     * 
     * @return void
     */
    protected function init()
    {
        $this->create( 'irt', Attr::REQUIRED, Attr::SINGLE );               # 1 +
        $this->create( 'address', Attr::REQUIRED, Attr::MULTIPLE );         # m +
        $this->create( 'phone', Attr::OPTIONAL, Attr::MULTIPLE )            # m
            ->apply( [$this, 'validatePhone'] );
        $this->create( 'fax-no', Attr::OPTIONAL, Attr::MULTIPLE )           # m
            ->apply( [$this, 'validatePhone'] );
        $this->create( 'e-mail', Attr::REQUIRED, Attr::MULTIPLE )           # m +
            ->apply( [$this, 'validateEmail'] );
        $this->create( 'abuse-mailbox', Attr::REQUIRED, Attr::MULTIPLE )    # m +
            ->apply( [$this, 'validateEmail'] );
        $this->create( 'signature', Attr::OPTIONAL, Attr::MULTIPLE );       # m
        $this->create( 'encryption', Attr::OPTIONAL, Attr::MULTIPLE );      # m
        $this->create( 'org', Attr::OPTIONAL, Attr::MULTIPLE );             # m
        $this->create( 'admin-c', Attr::REQUIRED, Attr::MULTIPLE );         # m +
        $this->create( 'tech-c', Attr::REQUIRED, Attr::MULTIPLE );          # m +
        $this->create( 'auth', Attr::REQUIRED, Attr::MULTIPLE );            # m +
        $this->create( 'remarks', Attr::OPTIONAL, Attr::MULTIPLE );         # m
        $this->create( 'irt-nfy', Attr::OPTIONAL, Attr::MULTIPLE )          # m
            ->apply( [$this, 'validateEmail'] );
        $this->create( 'notify', Attr::OPTIONAL, Attr::MULTIPLE );          # m
        $this->create( 'mnt-by', Attr::REQUIRED, Attr::MULTIPLE );          # m +
        $this->create( 'changed', Attr::REQUIRED, Attr::MULTIPLE );         # m +
        $this->create( 'source', Attr::REQUIRED, Attr::SINGLE )             # 1 +
            ->apply( 'strtoupper' );
    }
}
