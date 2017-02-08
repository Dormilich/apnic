<?php

use Test\ValidationObject as Object;
use Dormilich\APNIC\AttributeInterface as Attr;
use PHPUnit\Framework\TestCase;

class ValidationTest extends TestCase
{
    public function testChangedValidatorWithEmail()
    {
        $email = 'test@example.com';
        $obj = new Object('foo');
        $obj['changed'] = $email;

        $expected = $email . ' ' . date('Ymd');
        $this->assertEquals([$expected], $obj['changed']);
    }

    public function testChangedValidatorWithEmailAndDate()
    {
        $email = 'test@example.com ';
        $obj = new Object('foo');

        $obj['changed'] = $email . date('Ymd');
        $this->assertEquals([$email . date('Ymd')], $obj['changed']);

        $obj['changed'] = $email . date('ymd');
        $this->assertEquals([$email . date('ymd')], $obj['changed']);
    }

    /**
     * @expectedException \Dormilich\APNIC\Exceptions\InvalidValueException
     * @expectedExceptionMessage Invalid email or date format
     */
    public function testChangedValidatorWithInvalidEmailFails()
    {
        $obj = new Object('foo');
        $obj['changed'] = 'test@example.com 12/04/2011';
    }

    /**
     * @expectedException \Dormilich\APNIC\Exceptions\InvalidValueException
     * @expectedExceptionMessage Invalid email or date format
     */
    public function testChangedValidatorWithInvalidDateFails()
    {
        $obj = new Object('foo');
        $obj['changed'] = 'not an email address';
    }

    public function testCountryValidator()
    {
        $obj = new Object('foo');
        $obj['country'] = 'de';

        $this->assertSame('DE', $obj['country']);
    }

    /**
     * @expectedException \Dormilich\APNIC\Exceptions\InvalidValueException
     * @expectedExceptionMessage Invalid country code
     */
    public function testCountryValidatorFailsForLongStrings()
    {
        $obj = new Object('foo');
        $obj['country'] = 'ger';
    }

    public function testNotifyValidator()
    {
        $obj = new Object('foo');
        $obj['notify'] = 'test@example.com';

        $this->assertEquals(['test@example.com'], $obj['notify']);
    }

    /**
     * @expectedException \Dormilich\APNIC\Exceptions\InvalidValueException
     * @expectedExceptionMessage Invalid email address
     */
    public function testCountryValidatorFailsForInvalidEmail()
    {
        $obj = new Object('foo');
        $obj['notify'] = 'not an email address';
    }

    public function testPhoneValidator()
    {
        $obj = new Object('foo');
        // taken from the APNIC docs
        $obj->addAttribute('fax-no', '+12 34 567890 010')
            ->addAttribute('fax-no', '+681 368 0844 ext. 32')
        ;
        $expected = ['+12 34 567890 010', '+681 368 0844 ext. 32'];
        $this->assertEquals($expected, $obj['fax-no']);
    }

    /**
     * @expectedException \Dormilich\APNIC\Exceptions\InvalidValueException
     * @expectedExceptionMessage Invalid phone/fax number
     */
    public function testPhoneValidatorFailsForInvalidNumber()
    {
        $obj = new Object('foo');
        $obj['fax-no'] = '123 75319';
    }

    public function testObjectValidity()
    {
        $obj = new Object('foo');

        $this->assertFalse($obj->isValid());

        $obj['changed'] = 'test@example.com';
        $obj['source']  = 'APNIC';

        $this->assertTrue($obj->isValid());
    }
}
