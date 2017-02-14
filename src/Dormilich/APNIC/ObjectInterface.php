<?php
// ObjectInterface.php

namespace Dormilich\APNIC;

interface ObjectInterface
{
    /**
     * Get the name/type of the current RPSL object.
     * 
     * @return string RPSL object name.
     */
    public function getType();

    /**
     * Get the value of the attribute defined as primary key.
     * 
     * @return string
     */
    public function getPrimaryKey();

    /**
     * Get an attribute object specified by name.
     * 
     * @param string $name Name of the attribute.
     * @return AttributeInterface Attribute object.
     * @throws InvalidAttributeException Invalid argument name.
     */
    public function attr( $name );

    /**
     * Get an attribute’s value(s). This may throw an exception if the attribute 
     * does not exist.
     * 
     * @param string $name Attribute name.
     * @return string[]|string|NULL Attribute value(s).
     */
    public function get( $name );

    /**
     * Set an attribute’s value(s). This may throw an exception if multiple 
     * values are not supported by the underlying attribute.
     * 
     * @param string $name Attribute name.
     * @param mixed $value Attibute value(s).
     * @return self
     */
    public function set( $name, $value );

    /**
     * Add value(s) to an attribute. This may throw an exception if multiple 
     * values are not supported by the underlying attribute.
     * 
     * @param string $name Attribute name.
     * @param mixed $value Attibute value(s).
     * @return self
     */
    public function add( $name, $value );

    /**
     * Get the keys for the attributes (no matter whether they’re defined or not).
     * 
     * @return array
     */
    public function getAttributeNames();

    /**
     * Check if any of the required Attributes is undefined.
     * 
     * @return boolean
     */
    public function isValid();
}
