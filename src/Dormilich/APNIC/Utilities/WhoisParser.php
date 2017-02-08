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
     * @return ObjectInterface RPSL object.
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
            // this skips anything (e.g. empty lines) that is not an attribute definition
            // or a comment-only attribute (cf. "auth: # Filtered")
            if ( preg_match( '~^\s*([\w-]+)\s*:\s*([^#\s].*)~', $line, $match ) !== 1 ) {
                continue;
            }
            // need the reassignment, otherwise the switch from NULL to Object fails
            $object = $this->addAttribute( $object, $match[ 1 ], $match[ 2 ] );
            // skip parsing after the `source` property, 
            // which is usually the last in an RPSL block
            if ( $match[ 1 ] === 'source' ) {
                break;
            }
        }

        return $object;
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
    private function addAttribute( $obj, $name, $value )
    {
        if ( $obj ) {
            $obj->addAttribute( $name, $value );
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
        $object->setAttribute( $name, $value );

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
            return strtoupper( $matches[1] );
        }, $type );

        $class = 'Dormilich\\APNIC\\RPSL\\' . $name;

        if ( class_exists( $class ) ) {
            return $class;
        }

        throw new UnexpectedValueException( 'No class found for type ' . $type );
    }
}
