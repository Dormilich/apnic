<?php

use Dormilich\APNIC\Exceptions\InvalidValueException;
use PHPUnit\Framework\TestCase;
use Test\BaseObject;

class ValidationTest extends TestCase
{
    public function testChangedValidatorWithEmail()
    {
        $email = 'test@example.com';
        $obj = new BaseObject('foo');
        $obj['changed'] = $email;

        $expected = $email . ' ' . date('Ymd');
        $this->assertEquals([$expected], $obj['changed']);
    }

    public function testChangedValidatorWithEmailAndDate()
    {
        $email = 'test@example.com ';
        $obj = new BaseObject('foo');

        $obj['changed'] = $email . date('Ymd');
        $this->assertEquals([$email . date('Ymd')], $obj['changed']);

        $obj['changed'] = $email . date('ymd');
        $this->assertEquals([$email . date('ymd')], $obj['changed']);
    }

    public function testChangedValidatorWithInvalidEmailFails()
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage('Invalid email or date format');

        $obj = new BaseObject('foo');
        $obj['changed'] = 'test@example.com 12/04/2011';
    }

    public function testChangedValidatorWithInvalidDateFails()
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage('Invalid email or date format');

        $obj = new BaseObject('foo');
        $obj['changed'] = 'not an email address';
    }

    public function testCountryValidator()
    {
        $obj = new BaseObject('foo');
        $obj['country'] = 'de';

        $this->assertSame('DE', $obj['country']);
    }

    public function testCountryValidatorFailsForLongStrings()
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage('Invalid country code');

        $obj = new BaseObject('foo');
        $obj['country'] = 'ger';
    }

    public function testNotifyValidator()
    {
        $obj = new BaseObject('foo');
        $obj['notify'] = 'test@example.com';

        $this->assertEquals(['test@example.com'], $obj['notify']);
    }

    public function testCountryValidatorFailsForInvalidEmail()
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage('Invalid email address');

        $obj = new BaseObject('foo');
        $obj['notify'] = 'not an email address';
    }

    public function testPhoneValidator()
    {
        $obj = new BaseObject('foo');
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

        $obj = new BaseObject('foo');
        $obj['fax-no'] = '123 75319';
    }

    public function testHandleValidator()
    {
        $obj = new BaseObject('foo');
        $obj['mnt-by'] = 'phpunit-AP';

        $this->assertEquals(['PHPUNIT-AP'], $obj['mnt-by']);
    }

    public function testHandleValidatorFailsForInvalidString()
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage('Invalid RPSL object handle');

        $obj = new BaseObject('foo');
        $obj['mnt-by'] = '127.0.0.1';
    }

    public function testObjectValidity()
    {
        $obj = new BaseObject('foo');

        $this->assertFalse($obj->isValid());

        $obj['changed'] = 'test@example.com';
        $obj['source']  = 'APNIC';
        $obj['mnt-by']  = 'PHPUNIT-AP';

        $this->assertTrue($obj->isValid());
    }
}
