<?php
// ObjectInterface.php

namespace Dormilich\APNIC;

interface ObjectInterface
{
    /**
     * Get the name of the current RPSL object.
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
     * Get the name of the PK via function. 
     * Conformance function to overwrite in the Dummy class, 
     * which can not use a constant to store the PK.
     * 
     * @return string
     */
    public function getPrimaryKeyName();

    /**
     * Get an attribute specified by name.
     * 
     * @param string $name Name of the attribute.
     * @return AttributeInterface Attribute object.
     * @throws InvalidAttributeException Invalid argument name.
     */
    public function getAttribute( $name );

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
