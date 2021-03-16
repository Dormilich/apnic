<?php
// WhoisParser.php

namespace Dormilich\APNIC\Utilities;

use RuntimeException;
use UnexpectedValueException;
use Dormilich\APNIC\ObjectInterface;

/**
 * Take the text output from the whois command and parse it into an RPSL object.
 */
class WhoisParser
{
    /**
     * Parse the given text block for a single RPSL object definition. If the 
     * intended target object is known beforehand, you can pass it along for 
     * updating. The assumptions made about the text block are:
     *  - an attribute definition is <name>: <value> (once per line)
     *  - an RPSL block starts with the object’s type attribute
     *  - an RPSL block ends with the `source` attribute
     * 
     * Note: 
     *  Providing a default object helps making sure you get the correct object. 
     *  Otherwise you have to test that the received object is actually the one 
     *  you wanted.
     * 
     * @param string $text Text block.
     * @param ObjectInterface $object A default object to fill with data.
     * @return ObjectInterface|NULL RPSL object or null if there were no RPSL data.
     * @throws InvalidAttributeException RPSL block does not match the feed object.
     * @throws RuntimeException Error line found.
     * @throws UnexpectedValueException Could not create object for this RPSL block.
     */
    public function parse( $text, ObjectInterface $object = NULL )
    {
        foreach ( $this->text2array( $text ) as $line ) {
            // errors are comments, so it needs to be checked first
            $this->testError( $line );

            if ( $this->isComment( $line ) ) { 
                continue;
            }
            // this skips anything that is not an attribute definition
            // or a comment-only attribute (cf. "auth: # Filtered")
            // `inet6num` & `route6` are the only attributes containing a number
            if ( preg_match( '~^\s*([a-z6-]+)\s*:\s*([^#\s].*)~', $line, $match ) !== 1 ) {
                continue;
            }
            // need the reassignment, otherwise the switch from NULL to Object fails
            $object = $this->add( $object, $match[ 1 ], $match[ 2 ] );

            if ( $match[ 1 ] === 'source' ) {
                break;
            }
        }

        return $object;
    }

    /**
     * Parse all RPSL objects out of a text by splitting the text into sections 
     * (separated by two line breaks) and return an array with the object handle 
     * as array key.
     * 
     * @param string $text Text block.
     * @return ObjectInterface[] An object for each RPSL block.
     * @throws RuntimeException Error line found.
     * @throws UnexpectedValueException Could not create object for an RPSL block.
     */
    public function parseAll( $text )
    {
        $text = str_replace( "\r\n", "\n", $text );
        $sections = explode( "\n\n", $text );

        $objects = array_map( [$this, 'parse'], $sections );
        $objects = array_filter( $objects, 'is_object' );

        $list = array_reduce( $objects, function ( array $list, ObjectInterface $obj ) {
            $list[ $obj->getHandle() ] = $obj;
            return $list;
        }, [] );

        return $list;
    }

    /**
     * Convert the text block into an array of lines by splitting on LF.
     * 
     * @param string $text Text block.
     * @return array Text lines.
     */
    private function text2array( $text )
    {
        $lines = explode( "\n", $text );
        $lines = array_map( 'rtrim', $lines ); // remove remaining CR from CRLF

        return $lines;
    }

    /**
     * Check for an object-not-found error (not sure if there are other errors 
     * possible in the whois output).
     * 
     * @param string $line Text line.
     * @return void
     * @throws RuntimeException Error line found.
     */
    private function testError( $line )
    {
        if ( preg_match( '~^[%#\s]*ERROR:(\d+):(.+)~', $line, $match ) === 1 ) {
            throw new RuntimeException( trim( $match[ 2 ] ), $match[ 1 ] );
        }
    }

    /**
     * Test for a RIPE-style comment line (starting with `%`).
     * 
     * @param string $line Text line.
     * @return boolean TRUE if it’s a comment.
     */
    private function isComment( $line )
    {
        return strpos( $line, '%' ) === 0;
    }

    /**
     * Add a value to an RPSL property. If the object does not exist yet, create 
     * it from the current key-value pair. This assumes, that the type attribute 
     * is the first in the object block.
     * 
     * @param ObjectInterface|NULL $obj RPSL object.
     * @param string $name Attribute name.
     * @param string $value Attribute value.
     * @return ObjectInterface Updated/created RPSL object.
     * @throws UnexpectedValueException No class for this attribute name found.
     */
    private function add( $obj, $name, $value )
    {
        if ( $obj ) {
            $obj->add( $name, $value );
        } else {
            $obj = $this->createObject( $name, $value );
        }

        return $obj;
    }

    /**
     * Create an RPSL object from a key-value pair.
     * 
     * @param string $name Attribute name.
     * @param string $value Attribute value.
     * @return ObjectInterface RPSL object.
     * @throws UnexpectedValueException No class for this attribute name found.
     */
    private function createObject( $name, $value )
    {
        $class = $this->type2class( $name );
        // requires the PK, which is not yet known for Person/Role
        // thus leaving it empty and set the type’s attribute explicitly, 
        // which is the PK for any other object
        $object = new $class( NULL );
        $object->set( $name, $value );

        return $object;
    }

    /**
     * Convert the attribute name (of the type attribute) into a valid class name. 
     * 
     * @param string $type Name of the object’s type.
     * @return ObjectInterface RPSL object.
     * @throws UnexpectedValueException No class for this attribute name found.
     */
    private function type2class( $type )
    {
        $name = preg_replace_callback( '/-?\b([a-z])/', function ( $matches ) {
            return strtoupper( $matches[ 1 ] );
        }, $type );

        $class = 'Dormilich\\APNIC\\RPSL\\' . $name;

        if ( class_exists( $class ) ) {
            return $class;
        }

        throw new UnexpectedValueException( 'No class found for type ' . $type );
    }
}
