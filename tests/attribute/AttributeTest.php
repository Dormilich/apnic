<?php

use Dormilich\APNIC\Attribute;
use Dormilich\APNIC\AttributeInterface as Attr;
use PHPUnit\Framework\TestCase;

class AttributeTest extends TestCase
{
    // setup - name

    public function testAttributeHasCorrectName()
    {
        $attr = new Attribute('foo', true, true);
        $this->assertSame('foo', $attr->getName());

        $attr = new Attribute(1.8, true, true);
        $this->assertSame('1.8', $attr->getName());
    }

    // setup - properties

    public function constructorPropertyProvider()
    {
        return [
            [true,  true, true,  true], [true,  false, true,  false], 
            [false, true, false, true], [false, false, false, false], 
            [0,     1,    false, true], ['x',   NULL,  true,  false],
            [Attr::REQUIRED, Attr::SINGLE,   true,  false],
            [Attr::OPTIONAL, Attr::MULTIPLE, false, true],
        ];
    }

    /**
     * @dataProvider constructorPropertyProvider
     */
    public function testAttributeHasCorrectPropertiesSet($required, $multiple, $expect_required, $expect_multiple)
    {
        $attr = new Attribute('foo', $required, $multiple);

        $this->assertSame($expect_required, $attr->isRequired());
        $this->assertSame($expect_multiple, $attr->isMultiple());
    }

    // value

    public function testAttributeIsEmptyByDefault()
    {
        $attr = new Attribute('foo', true, true);
        $this->assertFalse($attr->isDefined());
        $this->assertNull($attr->getValue());
    }

    public function testAttributeWithValueIsDefined()
    {
        $attr = new Attribute('foo', Attr::REQUIRED, Attr::SINGLE);
        $attr->setValue('x');

        $this->assertTrue($attr->isDefined());
        $this->assertNotNull($attr->getValue());
    }

    public function testAttributeConvertsInputToStrings()
    {
        $attr = new Attribute('foo', Attr::REQUIRED, Attr::SINGLE);

        $attr->setValue(1);
        $this->assertSame('1', $attr->getValue());

        $attr->setValue(2.718);
        $this->assertSame('2.718', $attr->getValue());

        $attr->setValue('bar');
        $this->assertSame('bar', $attr->getValue());

        $test = new Test\StringObject;
        $attr->setValue($test);
        $this->assertSame('test', $attr->getValue());

        // I am not aware that the RIPE DB uses booleans somewhere…
        $attr->setValue(true);
        $this->assertSame('true', $attr->getValue());

        $attr->setValue(false);
        $this->assertSame('false', $attr->getValue());
    }

    public function testSingleAttributeOnlyHasOneValue()
    {
        $attr = new Attribute('foo', Attr::REQUIRED, Attr::SINGLE);

        $attr->setValue('fizz');
        $this->assertSame('fizz', $attr->getValue());

        $attr->setValue('buzz');
        $this->assertSame('buzz', $attr->getValue());

        $attr->addValue('bar');
        $this->assertSame('bar', $attr->getValue());
    }

    public function testMultipleAttributeReturnsList()
    {
        $attr = new Attribute('foo', Attr::REQUIRED, Attr::MULTIPLE);

        $attr->addValue('fizz');
        $this->assertSame(['fizz'], $attr->getValue());

        $attr->addValue('buzz');
        $this->assertSame(['fizz', 'buzz'], $attr->getValue());
    }

    /**
     * @expectedException \Dormilich\APNIC\Exceptions\InvalidDataTypeException
     */
    public function testSingleAttributeDoesNotAllowArrayInput()
    {
        $attr = new Attribute('foo', Attr::REQUIRED, Attr::SINGLE);
        $attr->setValue(['fizz', 'buzz']);
    }

    public function testSetValueResetsAttributeValue()
    {
        $attr = new Attribute('foo', Attr::REQUIRED, Attr::MULTIPLE);

        $attr->setValue('fizz');
        $this->assertSame(['fizz'], $attr->getValue());

        $attr->setValue('buzz');
        $this->assertSame(['buzz'], $attr->getValue());
    }

    public function testNullResetsAttributeValue()
    {
        $attr = new Attribute('foo', Attr::REQUIRED, Attr::SINGLE);

        $attr->setValue('foo');
        $this->assertTrue($attr->isDefined());

        $attr->setValue(NULL);
        $this->assertFalse($attr->isDefined());
    }

    // input types

    /**
     * @expectedException \Dormilich\APNIC\Exceptions\InvalidDataTypeException
     * @expectedExceptionMessageRegExp # \[foo\] #
     */
    public function testAttributeDoesNotAcceptResource()
    {
        $attr = new Attribute('foo', Attr::REQUIRED, Attr::SINGLE);
        $attr->setValue(tmpfile());
    }

    /**
     * @expectedException \Dormilich\APNIC\Exceptions\InvalidDataTypeException
     * @expectedExceptionMessageRegExp # \[foo\] #
     */
    public function testAttributeDoesNotAcceptArbitraryObject()
    {
        $attr = new Attribute('foo', Attr::REQUIRED, Attr::SINGLE);
        $attr->setValue(new stdClass);
    }

    public function testMultipleAttributeAllowsStringArray()
    {
        $attr = new Attribute('foo', Attr::REQUIRED, Attr::MULTIPLE);

        $attr->setValue(['fizz', 'buzz']);
        $this->assertSame(['fizz', 'buzz'], $attr->getValue());
    }

    public function testMultipleAttributeAllowsIterator()
    {
        $attr = new Attribute('foo', Attr::REQUIRED, Attr::MULTIPLE);
        $iter = new \ArrayIterator(['fizz', 'buzz']);

        $attr->setValue($iter);
        $this->assertSame(['fizz', 'buzz'], $attr->getValue());
    }

    /**
     * @expectedException \Dormilich\APNIC\Exceptions\InvalidDataTypeException
     */
    public function testMultipleAttributeDoesNotAllowNonScalarArray()
    {
        $attr = new Attribute('foo', Attr::REQUIRED, Attr::MULTIPLE);
        $attr->setValue([NULL]);
    }

    /**
     * @expectedException \Dormilich\APNIC\Exceptions\InvalidDataTypeException
     */
    public function testMultipleAttributeDoesNotAllowNestedArray()
    {
        $attr = new Attribute('foo', Attr::REQUIRED, Attr::MULTIPLE);
        $attr->setValue(['bar', [1,2,3]]);
    }

    public function testMultipleAttributeIgnoresArrayKeys()
    {
        $attr = new Attribute('foo', Attr::REQUIRED, Attr::MULTIPLE);

        $attr->setValue(['fizz' => 'buzz']);
        $this->assertSame(['buzz'], $attr->getValue());
    }

    // input validation

    public function testAttributeSetValueRunsCallback()
    {
        $attr = new Attribute('test', Attr::REQUIRED, Attr::SINGLE);
        $attr->apply('strtoupper');
        $attr->setValue('x');

        $this->assertSame('X', $attr->getValue());
    }

    public function testValidatorReceivesOriginalInput()
    {
        $test = new Test\StringObject;
        $attr = new Attribute('test', Attr::REQUIRED, Attr::SINGLE);

        $attr->apply(function ($input) {
            $this->assertInternalType('object', $input);
            return strtoupper($input);
        });
        $attr->setValue($test);

        $this->assertSame('TEST', $attr->getValue());
    }

    public function testValidatorCannotBeRedefined()
    {
        $attr = new Attribute('test', Attr::REQUIRED, Attr::SINGLE);
        $attr->apply('strtoupper');
        $attr->apply('strtolower');
        $attr->setValue('xY');

        $this->assertSame('XY', $attr->getValue());
    }

    // interface implementation

    public function testSingleAttributeValueCount()
    {
        $attr = new Attribute('test', Attr::REQUIRED, Attr::SINGLE);

        $this->assertCount(0, $attr);

        $attr->addValue(1);
        $this->assertCount(1, $attr);

        $attr->addValue(2);
        $this->assertCount(1, $attr);
    }

    public function testMultipleAttributeValueCount()
    {
        $attr = new Attribute('test', Attr::REQUIRED, Attr::MULTIPLE);

        $this->assertCount(0, $attr);

        $attr->addValue(1);
        $this->assertCount(1, $attr);

        $attr->addValue(2);
        $this->assertCount(2, $attr);
    }

    public function testArrayConversion()
    {
        $attr = new Attribute('test', Attr::REQUIRED, Attr::MULTIPLE);
        $attr->setValue(['fizz', 'buzz']);

        $expected = [
            ['name' => 'test', 'value' => 'fizz'],
            ['name' => 'test', 'value' => 'buzz'],
        ];
        $this->assertEquals($expected, $attr->toArray());
    }

    public function testJsonConversion()
    {
        $attr = new Attribute('test', Attr::REQUIRED, Attr::MULTIPLE);
        $attr->setValue(['fizz', 'buzz']);

        $expected = [
            ['name' => 'test', 'value' => 'fizz'],
            ['name' => 'test', 'value' => 'buzz'],
        ];
        $this->assertJsonStringEqualsJsonString(json_encode($expected), json_encode($attr));
    }
}
