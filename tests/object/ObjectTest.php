<?php

use Test\BaseObject as Object;
use Dormilich\APNIC\AttributeInterface as Attr;
use PHPUnit\Framework\TestCase;

class ObjectTest extends TestCase
{
    public function testSetupObject()
    {
        $obj = new Object('foo');

        $this->assertSame('test', $obj->getType());
        $this->assertSame('test', $obj->getPrimaryKeyName());
        $this->assertSame('foo',  $obj->getPrimaryKey());
    }

    public function testGetAllAttributeNames()
    {
        $obj = new Object('foo');
        $names = $obj->getAttributeNames();

        $this->assertCount(3, $names);
        $this->assertEquals(['test', 'name', 'comment'], $names);
    }

    public function testGetAttributeObject()
    {
        $obj = new Object('foo');
        $attr = $obj->getAttribute('test');

        $this->assertInstanceOf('Dormilich\APNIC\Attribute', $attr);
    }

    /**
     * @expectedException \Dormilich\APNIC\Exceptions\InvalidAttributeException
     * @expectedExceptionMessageRegExp /Attribute "1" is not defined for the [A-Z]+ object/
     */
    public function testGetUndefinedAttributeFails()
    {
        $obj = new Object('foo');
        $attr = $obj->getAttribute(1);
    }

    public function testSetMultipleAttributeValue()
    {
        $obj = new Object('foo');

        $obj->setAttribute('comment', 'x');
        $this->assertEquals(['x'], $obj->getAttribute('comment')->getValue());

        $obj->setAttribute('comment', 'y');
        $this->assertEquals(['y'], $obj->getAttribute('comment')->getValue());
    }

    public function testSetSingleAttributeValue()
    {
        $obj = new Object('foo');

        $obj->setAttribute('name', 'x');
        $this->assertEquals('x', $obj->getAttribute('name')->getValue());

        $obj->setAttribute('name', 'y');
        $this->assertEquals('y', $obj->getAttribute('name')->getValue());
    }

    public function testAddMultipleAttributeValue()
    {
        $obj = new Object('foo');

        $obj->addAttribute('comment', 'x');
        $this->assertEquals(['x'], $obj->getAttribute('comment')->getValue());

        $obj->addAttribute('comment', 'y');
        $this->assertEquals(['x', 'y'], $obj->getAttribute('comment')->getValue());
    }

    public function testAddSingleAttributeValue()
    {
        $obj = new Object('foo');

        $obj->addAttribute('name', 'x');
        $this->assertEquals('x', $obj->getAttribute('name')->getValue());

        $obj->addAttribute('name', 'y');
        $this->assertEquals('y', $obj->getAttribute('name')->getValue());
    }

    public function testGetAttributeValueDirectly()
    {
        $obj = new Object('foo');

        $this->assertSame('foo', $obj['test']);
    }

    public function testSetAttributeValueDirectly()
    {
        $obj = new Object('foo');
        $obj['name'] = 'quux';

        $this->assertSame('quux', $obj['name']);
    }

    public function testUnsetAttributeValueDirectly()
    {
        $obj = new Object('foo');

        $obj['name'] = 'quux';
        $this->assertSame('quux', $obj['name']);

        unset($obj['name']);
        $this->assertNull($obj['name']);
    }

    public function testIssetAttributeValue()
    {
        $obj = new Object('foo');

        $this->assertTrue(isset($obj['name']));
        $this->assertFalse(isset($obj['flix']));
    }

    public function testObjectToArrayConversion()
    {
        $obj = new Object('foo');
        $obj['name'] = 'quux';

        $expected = [
            ['name' => 'test', 'value' => 'foo'],
            ['name' => 'name', 'value' => 'quux'],
        ];
        $this->assertEquals($expected, $obj->toArray());
    }

    public function testObjectStringification()
    {
        $obj = new Object('foo');
        $obj['comment'] = ['fizz', 'buzz'];

        $text = (string) $obj;
        $lines = explode(\PHP_EOL, trim($text)); // removing trailing LF

        $this->assertCount(3, $lines);

        $this->assertStringStartsWith('test:', $lines[0]);
        $this->assertStringEndsWith('foo', $lines[0]);

        $this->assertStringStartsWith('comment:', $lines[1]);
        $this->assertStringEndsWith('fizz', $lines[1]);

        $this->assertStringStartsWith('comment:', $lines[2]);
        $this->assertStringEndsWith('buzz', $lines[2]);
    }

    public function testCountDefinedAttributes()
    {
        $obj = new Object;
        $obj['comment'] = ['fizz', 'buzz'];

        $this->assertCount(1, $obj);
    }

    public function testIteratorInForeach()
    {
        $obj = new Object('foo');
        $obj['comment'] = ['fizz', 'buzz'];

        $this->assertGreaterThan(0, count($obj));

        $keys = $values = [];
        foreach ($obj as $key => $value) {
            $keys[]   = $key;
            $values[] = $value;
        }
        $this->assertEquals(['test', 'comment', 'comment'], $keys);
        $this->assertEquals(['foo', 'fizz', 'buzz'], $values);
    }
}
