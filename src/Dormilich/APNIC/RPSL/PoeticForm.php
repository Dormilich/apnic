<?php
// PoeticForm.php

namespace Dormilich\APNIC\RPSL;

use Dormilich\APNIC\Object;
use Dormilich\APNIC\AttributeInterface as Attr;
use Dormilich\APNIC\Exceptions\InvalidValueException;

class PoeticForm extends Object
{
    /**
     * The version of the RIPE DB used for attribute definitions.
     */
    const VERSION = '1.69';

    /**
     * Create a POETIC-FORM RPSL object.
     * 
     * @param string $value The name of the genre.
     * @return self
     */
    public function __construct( $value )
    {
        $this->init();
        $this->setType( 'poetic-form' );
        $this->setKey( [
            'poetic-form' => $value,
        ] );
    }

    /**
     * Defines attributes for the POETIC-FORM RPSL object. 
     * 
     * @return void
     */
    protected function init()
    {
        $this->create( 'poetic-form', Attr::REQUIRED, Attr::SINGLE );       # 1 +
        $this->create( 'descr', Attr::OPTIONAL, Attr::MULTIPLE );           # m
        $this->create( 'admin-c', Attr::REQUIRED, Attr::MULTIPLE );         # m +
        $this->create( 'remarks', Attr::OPTIONAL, Attr::MULTIPLE );         # m
        $this->create( 'notify', Attr::OPTIONAL, Attr::MULTIPLE );          # m
        $this->create( 'mnt-by', Attr::REQUIRED, Attr::SINGLE );            # 1 +
        $this->create( 'changed', Attr::REQUIRED, Attr::MULTIPLE );         # m +
        $this->create( 'source', Attr::REQUIRED, Attr::SINGLE )             # 1 +
            ->apply( 'strtoupper' );
    }

    public function poeticForm( $input )
    {
        $input = strtoupper( $input );

        if ( strpos( $input, 'FORM-' ) === 0) {
            return $input;
        }

        throw new InvalidValueException( 'Invalid poetic-form handle' );
    }
}
