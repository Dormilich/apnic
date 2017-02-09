<?php

use Dormilich\APNIC\Utilities\WhoisParser;
use Dormilich\APNIC\RPSL\Mntner;
use Dormilich\APNIC\RPSL\Role;
use PHPUnit\Framework\TestCase;

class WhoisParserTest extends TestCase
{
    private function loadText( $name )
    {
        $file = __DIR__ . '/_fixtures/' . $name . '.txt';
        return file_get_contents( $file );
    }

    /**
     * @expectedException RuntimeException
     * @expectedExceptionCode 101
     * @expectedExceptionMessage no entries found
     */
    public function testNotFoundThowsError()
    {
        $text = $this->loadText( 'not-found' );
        $parser = new WhoisParser;
        $obj = $parser->parse( $text );
    }

    public function testParseTextAutoDetectObjectType()
    {
        $text = $this->loadText( 'maint-example-hk' );
        $parser = new WhoisParser;
        $obj = $parser->parse( $text );

        $this->assertInstanceOf( Mntner::class, $obj );
        $this->assertSame( 'MAINT-EXAMPLE-HK', $obj->getPrimaryKey() );

        $this->assertSame( 'MAINT-EXAMPLE-HK', $obj[ 'mntner' ] );
        $this->assertEquals( ['Example, Ltd. (HK)'], $obj[ 'descr' ] );
        $this->assertSame( 'HK', $obj[ 'country' ] );
        $this->assertEquals( ['XL1-AP'], $obj[ 'admin-c' ] );
        $this->assertNull( $obj[ 'tech-c' ] );
        $this->assertEquals( ['abuse@example.com'], $obj[ 'upd-to' ] );
        $this->assertNull( $obj[ 'mnt-nfy' ] );
        $this->assertNull( $obj[ 'auth' ] );
        $this->assertNull( $obj[ 'remarks' ] );
        $this->assertNull( $obj[ 'notify' ] );
        $this->assertNull( $obj[ 'abuse-mailbox' ] );
        $this->assertEquals( ['MAINT-EXAMPLE-HK'], $obj[ 'mnt-by' ] );
        $this->assertSame( 'APNIC-HM', $obj[ 'referral-by' ] );
        $this->assertEquals( ['hm-changed@apnic.net 19700101'], $obj[ 'changed' ] );
        $this->assertSame( 'APNIC', $obj[ 'source' ] );
    }

    public function testParseTextWithDefaultObject()
    {
        $text = $this->loadText( 'maint-example-hk' );
        $mnt = new Mntner( NULL );
        $parser = new WhoisParser;
        $obj = $parser->parse( $text, $mnt );

        $this->assertInstanceOf( Mntner::class, $obj );
        $this->assertSame( 'MAINT-EXAMPLE-HK', $obj->getPrimaryKey() );
    }

    /**
     * @expectedException Dormilich\APNIC\Exceptions\InvalidAttributeException
     * @expectedExceptionMessage Attribute "mntner" is not defined for the ROLE object.
     */
    public function testParstTextWithIncorrectDefaultObjectFails()
    {
        $text = $this->loadText( 'maint-example-hk' );
        $role = new Role;
        $parser = new WhoisParser;
        $obj = $parser->parse( $text, $role );
    }

    /**
     * @expectedException UnexpectedValueException
     * @expectedExceptionMessage No class found for type address
     */
    public function testParseMalformedTextFails()
    {
        // snippet of Role ...
        $text = <<<RPSL
address:        Any Street 1
address:        Hong Kong District 2
address:        Hong Kong
country:        HK
mnt-by:         MAINT-EXAMPLE-HK
changed:        hm-changed@apnic.net 19700101
RPSL;
        $parser = new WhoisParser;
        $obj = $parser->parse( $text );
    }

    public function testNonRpslTextReturnsNothing()
    {
        $text = <<<TXT
Lorem ipsum dolor sit amet, consectetur adipisici elit, sed eiusmod tempor
incidunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud
exercitation ullamco laboris nisi ut aliquid ex ea commodi consequat. Quis aute
iure reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.

Excepteur sint obcaecat cupiditat non proident, sunt in culpa qui officia deserunt
mollit anim id est laborum.
TXT;
        $parser = new WhoisParser;
        $obj = $parser->parse( $text );

        $this->assertNull( $obj );
    }

    public function testParseMultipleObjects()
    {
        $text = $this->loadText( 'maint-example-hk' );
        $parser = new WhoisParser;
        $data = $parser->parseAll( $text );

        $this->assertCount( 2, $data );

        $this->assertArrayHasKey( 'MAINT-EXAMPLE-HK', $data );
        $this->assertArrayHasKey( 'XL1-AP', $data );

        $this->assertInstanceOf( Mntner::class, $data[ 'MAINT-EXAMPLE-HK' ] );
        $this->assertInstanceOf( Role::class, $data[ 'XL1-AP' ] );

        // Mntner is not valid due to filtered auth data
        $this->assertTrue( $data[ 'XL1-AP' ]->isValid() );
    }
}
