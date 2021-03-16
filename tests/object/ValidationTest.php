<?php

use Dormilich\APNIC\Exceptions\InvalidValueException;
use PHPUnit\Framework\TestCase;
use Test\ValidationObject;

class ValidationTest extends TestCase
{

    public function testCountryValidator()
    {
        $obj = new ValidationObject('foo');
        $obj['country'] = 'de';

        $this->assertSame('DE', $obj['country']);
    }

    public function testCountryValidatorFailsForLongStrings()
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage('Invalid country code');

        $obj = new ValidationObject('foo');
        $obj['country'] = 'ger';
    }

    public function testNotifyValidator()
    {
        $obj = new ValidationObject('foo');
        $obj['notify'] = 'test@example.com';

        $this->assertEquals(['test@example.com'], $obj['notify']);
    }

    public function testCountryValidatorFailsForInvalidEmail()
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage('Invalid email address');

        $obj = new ValidationObject('foo');
        $obj['notify'] = 'not an email address';
    }

    public function testPhoneValidator()
    {
        $obj = new ValidationObject('foo');
        // taken from the APNIC docs
        $obj->add('fax-no', '+12 34 567890 010')
            ->add('fax-no', '+681 368 0844 ext. 32')
        ;
        $expected = ['+12 34 567890 010', '+681 368 0844 ext. 32'];
        $this->assertEquals($expected, $obj['fax-no']);
    }

    public function testPhoneValidatorFailsForInvalidNumber()
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage('Invalid phone/fax number');

        $obj = new ValidationObject('foo');
        $obj['fax-no'] = '123 75319';
    }

    public function testHandleValidator()
    {
        $obj = new ValidationObject('foo');
        $obj['mnt-by'] = 'phpunit-AP';

        $this->assertEquals(['PHPUNIT-AP'], $obj['mnt-by']);
    }

    public function testHandleValidatorFailsForInvalidString()
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage('Invalid RPSL object handle');

        $obj = new ValidationObject('foo');
        $obj['mnt-by'] = '127.0.0.1';
    }

    public function testObjectValidity()
    {
        $obj = new ValidationObject('foo');

        $this->assertFalse($obj->isValid());

        $obj['source']  = 'APNIC';
        $obj['mnt-by']  = 'PHPUNIT-AP';

        $this->assertTrue($obj->isValid());
    }
}
