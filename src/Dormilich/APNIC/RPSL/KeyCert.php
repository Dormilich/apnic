<?php
// KeyCert.php

namespace Dormilich\APNIC\RPSL;

use Dormilich\APNIC\Object;
use Dormilich\APNIC\AttributeInterface as Attr;
use Dormilich\APNIC\Exceptions\InvalidValueException;

/**
 * Be aware that the 'method', 'owner' and 'fingerpr' attributes 
 * must not be set/updated/deleted by the user.
 */
class KeyCert extends Object
{
    const VERSION = '1.69';

    /**
     * Create a key certification (KEY-CERT) RPSL object.
     * 
     * @param string $value The key ID.
     * @return self
     */
    public function __construct( $value )
    {
        $this->init();
        $this->setType( 'key-cert' );
        $this->setKey( [
            'key-cert' => $value,
        ] );
    }

    /**
     * Defines attributes for the KEY-CERT RPSL object. 
     * 
     * @return void
     */
    protected function init()
    {
        $this->create( 'key-cert', Attr::REQUIRED, Attr::SINGLE );          # 1 +
        $this->setGeneratedAttribute('method', Attr::SINGLE );              # 1 -
        $this->setGeneratedAttribute('owner', Attr::MULTIPLE );             # m -
        $this->setGeneratedAttribute('fingerpr', Attr::SINGLE );            # 1 -
        $this->create( 'certif', Attr::REQUIRED, Attr::MULTIPLE );          # m +
        $this->create( 'org', Attr::OPTIONAL, Attr::MULTIPLE );             # m
        $this->create( 'remarks', Attr::OPTIONAL, Attr::MULTIPLE );         # m
        $this->create( 'notify', Attr::OPTIONAL, Attr::MULTIPLE );          # m
        $this->create( 'admin-c', Attr::OPTIONAL, Attr::MULTIPLE );         # m
        $this->create( 'tech-c', Attr::OPTIONAL, Attr::MULTIPLE );          # m
        $this->create( 'mnt-by', Attr::REQUIRED, Attr::MULTIPLE );          # m +
        $this->create( 'changed', Attr::REQUIRED, Attr::MULTIPLE );         # m +
        $this->create( 'source', Attr::REQUIRED, Attr::SINGLE )             # 1 +
            ->apply( 'strtoupper' );

    }

    public function keyCert( $input )
    {
        $input = strtoupper( $input );

        if ( preg_match( '~^PGPKEY-[0-9A-F]{8}$~', $input ) ) {
            return $input;
        }

        throw new InvalidValueException( 'Invalid key-cert ID' );
    }
}
