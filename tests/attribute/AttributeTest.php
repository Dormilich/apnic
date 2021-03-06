<?php

use Dormilich\APNIC\Attribute;
use Dormilich\APNIC\AttributeInterface as Attr;
use Dormilich\APNIC\Exceptions\InvalidDataTypeException;
use PHPUnit\Framework\TestCase;
use Test\BaseObject;

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
        // integer
        $attr->setValue(1);
        $this->assertSame('1', $attr->getValue());
        // float
        $attr->setValue(2.718);
        $this->assertSame('2.718', $attr->getValue());
        // string
        $attr->setValue('bar');
        $this->assertSame('bar', $attr->getValue());
        // stringifiable object
        $test = $this->createMock('Exception');
        $test->method('__toString')->willReturn('test');
        $attr->setValue($test);
        $this->assertSame('test', $attr->getValue());
        // boolean
        // I am not aware that the DB uses booleans somewhere…
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

    public function testSingleAttributeDoesNotAllowArrayInput()
    {
        $this->expectException(InvalidDataTypeException::class);

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

    public function testAttributeAllowsRpslObject()
    {
        $attr = new Attribute('foo', Attr::REQUIRED, Attr::SINGLE);
        $obj = new BaseObject(uniqid());

        $attr->setValue($obj);
        $this->assertSame($obj->getHandle(), $attr->getValue());
    }

    public function testAttributeDoesNotAcceptResource()
    {
        $this->expectException(InvalidDataTypeException::class);
        $this->expectExceptionMessage('[foo]');

        $attr = new Attribute('foo', Attr::REQUIRED, Attr::SINGLE);
        $attr->setValue(tmpfile());
    }

    public function testAttributeDoesNotAcceptArbitraryObject()
    {
        $this->expectException(InvalidDataTypeException::class);
        $this->expectExceptionMessage('[foo]');

        $attr = new Attribute('foo', Attr::REQUIRED, Attr::SINGLE);
        $attr->setValue(new stdClass);
    }

    public function testMultipleAttributeAllowsStringArray()
    {
        $attr = new Attribute('foo', Attr::REQUIRED, Attr::MULTIPLE);

        $attr->setValue(['fizz', 'buzz']);
        $this->assertSame(['fizz', 'buzz'], $attr->getValue());
    }

    public function testMultipleAttributeAllowsAttribute()
    {
        $attr = new Attribute('foo', Attr::REQUIRED, Attr::MULTIPLE);
        $src = new Attribute('bar', Attr::REQUIRED, Attr::MULTIPLE);
        $src->setValue(['fizz', 'buzz']);

        $attr->setValue($src);
        $this->assertSame(['fizz', 'buzz'], $attr->getValue());
    }

    public function testMultipleAttributeDoesNotAllowNonScalarArray()
    {
        $this->expectException(InvalidDataTypeException::class);

        $attr = new Attribute('foo', Attr::REQUIRED, Attr::MULTIPLE);
        $attr->setValue([NULL]);
    }

    public function testMultipleAttributeDoesNotAllowNestedArray()
    {
        $this->expectException(InvalidDataTypeException::class);

        $attr = new Attribute('foo', Attr::REQUIRED, Attr::MULTIPLE);
        $attr->setValue(['bar', [1,2,3]]);
    }

    public function testMultipleAttributeIgnoresArrayKeys()
    {
        $attr = new Attribute('foo', Attr::REQUIRED, Attr::MULTIPLE);

        $attr->setValue(['fizz' => 'buzz']);
        $this->assertSame(['buzz'], $attr->getValue());
    }

    public function testMultipleAttributeSplitsMultilineText()
    {
        $attr = new Attribute('foo', Attr::REQUIRED, Attr::MULTIPLE);

        $value = <<<TXT
Lorem ipsum dolor sit amet, consectetur adipisici elit, sed eiusmod tempor
incidunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud
exercitation ullamco laboris nisi ut aliquid ex ea commodi consequat. Quis aute
iure reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.

Excepteur sint obcaecat cupiditat non proident, sunt in culpa qui officia deserunt
mollit anim id est laborum.
TXT;
        $attr->setValue($value);
        $this->assertCount(7, $attr);
    }

    public function testsingleAttributeDoesNotAllowMultilineText()
    {
        $this->expectException(InvalidDataTypeException::class);
        $this->expectExceptionMessage('The [foo] attribute does not allow the array data type.');

        $attr = new Attribute('foo', Attr::REQUIRED, Attr::SINGLE);

        $value = <<<TXT
Lorem ipsum dolor sit amet, consectetur adipisici elit, sed eiusmod tempor
incidunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud
exercitation ullamco laboris nisi ut aliquid ex ea commodi consequat. Quis aute
iure reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.

Excepteur sint obcaecat cupiditat non proident, sunt in culpa qui officia deserunt
mollit anim id est laborum.
TXT;
        $attr->setValue($value);
    }

    // input validation

    public function testAttributeSetValueRunsCallback()
    {
        $attr = new Attribute('test', Attr::REQUIRED, Attr::SINGLE);
        $attr->apply('strtoupper');
        $attr->setValue('x');

        $this->assertSame('X', $attr->getValue());
    }

    public function testValidatorReceivesStringInput()
    {
        $obj = $this->createMock('Exception');
        $obj->method('__toString')->willReturn('phpunit');

        $attr = new Attribute('test', Attr::REQUIRED, Attr::SINGLE);

        $attr->apply(function ($input) {
            $this->assertInternalType('string', $input);
            return strtoupper($input);
        });

        $attr->setValue($obj);

        $this->assertSame('PHPUNIT', $attr->getValue());
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

    public function testSingleAttributeIsIterable()
    {
        $attr = new Attribute('test', Attr::REQUIRED, Attr::SINGLE);

        $array0 = iterator_to_array($attr);
        $this->assertEquals([], $array0);

        $attr->setValue('phpunit');
        $array1 = iterator_to_array($attr);
        $this->assertEquals(['phpunit'], $array1);
    }
}
