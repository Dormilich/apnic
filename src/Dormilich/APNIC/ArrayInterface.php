<?php
// ArrayInterface.php

namespace Dormilich\APNIC;

interface ArrayInterface
{
    /**
     * Convert the list of values into a name+value array.
     * 
     * @return array
     */
    public function toArray();
}
