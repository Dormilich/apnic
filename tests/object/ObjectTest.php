<?php

use PHPUnit\Framework\TestCase;
use Test\BaseObject;

class ObjectTest extends TestCase
{
    public function testSetupObject()
    {
        $obj = new BaseObject('foo');

        $this->assertSame('test', $obj->getType());
        $this->assertSame('foo',  $obj->getHandle());
    }

    public function testGetAllAttributeNames()
    {
        $obj = new BaseObject('foo');
        $names = $obj->getAttributeNames();

        $this->assertCount(4, $names);
        $this->assertEquals(['test', 'name', 'comment', 'last-modified'], $names);
    }

    public function testGetAttributeObject()
    {
        $obj = new BaseObject('foo');
        $attr = $obj->attr('test');

        $this->assertInstanceOf('Dormilich\APNIC\Attribute', $attr);
    }

    public function testGetGeneratedAttributeObject()
    {
        $obj = new BaseObject('foo');
        $attr = $obj->attr('last-modified');

        $this->assertInstanceOf('Dormilich\APNIC\Attribute', $attr);
    }

    /**
     * @expectedException \Dormilich\APNIC\Exceptions\InvalidAttributeException
     * @expectedExceptionMessageRegExp /Attribute "1" is not defined for the [A-Z]+ object/
     */
    public function testGetUndefinedAttributeFails()
    {
        $obj = new BaseObject('foo');
        $attr = $obj->attr(1);
    }

    public function testSetMultipleAttributeValue()
    {
        $obj = new BaseObject('foo');

        $obj->set('comment', 'x');
        $this->assertEquals(['x'], $obj->attr('comment')->getValue());

        $obj->set('comment', 'y');
        $this->assertEquals(['y'], $obj->attr('comment')->getValue());
    }

    public function testSetSingleAttributeValue()
    {
        $obj = new BaseObject('foo');

        $obj->set('name', 'x');
        $this->assertEquals('x', $obj->attr('name')->getValue());

        $obj->set('name', 'y');
        $this->assertEquals('y', $obj->attr('name')->getValue());
    }

    public function testAddMultipleAttributeValue()
    {
        $obj = new BaseObject('foo');

        $obj->add('comment', 'x');
        $this->assertEquals(['x'], $obj->attr('comment')->getValue());

        $obj->add('comment', 'y');
        $this->assertEquals(['x', 'y'], $obj->attr('comment')->getValue());
    }

    public function testAddSingleAttributeValue()
    {
        $obj = new BaseObject('foo');

        $obj->add('name', 'x');
        $this->assertEquals('x', $obj->attr('name')->getValue());

        $obj->add('name', 'y');
        $this->assertEquals('y', $obj->attr('name')->getValue());
    }

    public function testGetAttributeValue()
    {
        $obj = new BaseObject('foo');

        $this->assertSame('foo', $obj['test']);
        $this->assertSame('foo', $obj->get('test'));
        $this->assertSame('foo', $obj->attr('test')->getValue());
    }

    public function testGetUndefinedAttributeDirectlyYieldsUndefined()
    {
        $obj = new BaseObject('foo');

        $this->assertNull($obj['x']);
    }

    public function testSetAttributeValueDirectly()
    {
        $obj = new BaseObject('foo');
        $obj['name'] = 'quux';

        $this->assertSame('quux', $obj['name']);
    }

    public function testUnsetAttributeValueDirectly()
    {
        $obj = new BaseObject('foo');

        $obj['name'] = 'quux';
        $this->assertSame('quux', $obj['name']);

        unset($obj['name']);
        $this->assertNull($obj['name']);
    }

    public function testIssetAttributeValue()
    {
        $obj = new BaseObject('foo');

        $this->assertTrue(isset($obj['name']));
        $this->assertFalse(isset($obj['flix']));
    }

    public function testObjectToArrayConversion()
    {
        $obj = new BaseObject('foo');
        $obj['name'] = 'quux';

        $expected = [
            ['name' => 'test', 'value' => 'foo'],
            ['name' => 'name', 'value' => 'quux'],
        ];
        $this->assertEquals($expected, $obj->toArray());
    }

    public function testObjectStringification()
    {
        $obj = new BaseObject('foo');
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
        $obj = new BaseObject;
        $obj['comment'] = ['fizz', 'buzz'];

        $this->assertCount(1, $obj);
    }

    public function testIteratorInForeach()
    {
        $obj = new BaseObject('foo');
        $obj['comment'] = ['fizz', 'buzz'];

        $this->assertGreaterThan(0, count($obj));

        $names = $defined = [];
        foreach ($obj as $name => $attr) {
            $names[] = $name;
            $defined[] = $attr->isDefined();
        }
        $this->assertEquals(['test', 'name', 'comment'], $names);
        $this->assertEquals([true, false, true], $defined);
    }
}
