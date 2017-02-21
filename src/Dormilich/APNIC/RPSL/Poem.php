<?php
// Poem.php

namespace Dormilich\APNIC\RPSL;

use Dormilich\APNIC\Object;
use Dormilich\APNIC\AttributeInterface as Attr;

class Poem extends Object
{
    const VERSION = '1.69';

    /**
     * Create a POEM RPSL object.
     * 
     * @param string $value Title of the poem that is represented by this object.
     * @return self
     */
    public function __construct( $value )
    {
        $this->init();
        $this->setType( 'poem' );
        $this->setKey( [
            'poem' => $value,
        ] );
    }

    /**
     * Defines attributes for the POEM RPSL object. 
     * 
     * @return void
     */
    protected function init()
    {
        $this->create( 'poem', Attr::REQUIRED, Attr::SINGLE );              # 1 +
        $this->create( 'descr', Attr::OPTIONAL, Attr::MULTIPLE );           # m
        $this->create( 'form', Attr::REQUIRED, Attr::SINGLE );              # 1 +
        $this->create( 'text', Attr::REQUIRED, Attr::MULTIPLE );            # m +
        $this->create( 'author', Attr::OPTIONAL, Attr::MULTIPLE );          # m
        $this->create( 'remarks', Attr::OPTIONAL, Attr::MULTIPLE );         # m
        $this->create( 'notify', Attr::OPTIONAL, Attr::MULTIPLE );          # m
        $this->create( 'mnt-by', Attr::REQUIRED, Attr::SINGLE );            # 1 +
        $this->create( 'changed', Attr::REQUIRED, Attr::MULTIPLE );         # m +
        $this->create( 'source', Attr::REQUIRED, Attr::SINGLE )             # 1 +
            ->apply( 'strtoupper' );
    }

    public function form( $input )
    {
        $input = strtoupper( $input );

        if ( strpos( $input, 'FORM-' ) === 0) {
            return $input;
        }

        throw new InvalidValueException( 'Invalid poetic-form handle' );
    }
}
