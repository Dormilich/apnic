<?php

use Dormilich\APNIC\RPSL as RPSL;
use PHPUnit\Framework\TestCase;

class RpslTest extends TestCase
{
    public function testAsBlock()
    {
        $obj = new RPSL\AsBlock('phpunit');

        $this->assertSame('as-block', $obj->getType());
        $this->assertEquals(['as-block'], $obj->getPrimaryKeyNames());
    }

    public function testAsSet()
    {
        $obj = new RPSL\AsSet('phpunit');

        $this->assertSame('as-set', $obj->getType());
        $this->assertEquals(['as-set'], $obj->getPrimaryKeyNames());
    }

    public function testAutNum()
    {
        $obj = new RPSL\AutNum('AS135');

        $this->assertSame('aut-num', $obj->getType());
        $this->assertEquals(['aut-num'], $obj->getPrimaryKeyNames());
    }

    /**
     * @expectedException \Dormilich\APNIC\Exceptions\InvalidValueException
     * @expectedExceptionMessage Invalid AS number
     */
    public function testAutNumWithInvalidAsnFails()
    {
        new RPSL\AutNum('test');
    }

    public function testIPv4Domain()
    {
        $obj = new RPSL\Domain('0.0.127.in-addr.arpa');

        $this->assertSame('domain', $obj->getType());
        $this->assertEquals(['domain'], $obj->getPrimaryKeyNames());
    }

    public function testIPv6Domain()
    {
        $obj = new RPSL\Domain('0DB8.2001.ip6.arpa');

        $this->assertSame('domain', $obj->getType());
        $this->assertEquals(['domain'], $obj->getPrimaryKeyNames());
    }

    /**
     * @expectedException \Dormilich\APNIC\Exceptions\InvalidValueException
     * @expectedExceptionMessage Invalid reverse delegation
     */
    public function testDomainWithInvalidAddressFails()
    {
        new RPSL\Domain('test');
    }

    public function testFilterSet()
    {
        $obj = new RPSL\FilterSet('FLTR-EXAMPLENET');

        $this->assertSame('filter-set', $obj->getType());
        $this->assertEquals(['filter-set'], $obj->getPrimaryKeyNames());
    }

    /**
     * @expectedException \Dormilich\APNIC\Exceptions\InvalidValueException
     * @expectedExceptionMessage Invalid filter-set name
     */
    public function testFilterSetWithInvalidNameFails()
    {
        new RPSL\FilterSet('test');
    }

    public function testInet6num()
    {
        $obj = new RPSL\Inet6num('2001:0DB8::/32');
        $obj['status'] = 'assigned portable';

        $this->assertSame('inet6num', $obj->getType());
        $this->assertEquals(['inet6num'], $obj->getPrimaryKeyNames());
        $this->assertSame('ASSIGNED PORTABLE', $obj['status']);
    }

    public function invalidIP6Addresses()
    {
        return [
            ['test'],
            ['2001:0DB8::'],
            ['2001:0DB8::/256'],
            ['127.0.0.0/20'],
            ['2001:ABXY::/32']
        ];
    }

    /**
     * @dataProvider invalidIP6Addresses
     * @expectedException \Dormilich\APNIC\Exceptions\InvalidValueException
     * @expectedExceptionMessage Invalid IPv6 address range
     */
    public function testInet6numWithInvalidAddressFails($address)
    {
        new RPSL\Inet6num($address);
    }

    /**
     * @expectedException \Dormilich\APNIC\Exceptions\InvalidValueException
     * @expectedExceptionMessage Invalid status for the Inet6num object
     */
    public function testInet6numWithInvalidStatusFails()
    {
        $obj = new RPSL\Inet6num('2001:0DB8::/32');
        $obj['status'] = 'invalid status';
    }

    public function testInetnum()
    {
        $obj = new RPSL\Inetnum('192.168.1.0 - 192.168.1.255');
        $obj['status'] = 'assigned non-portable';

        $this->assertSame('inetnum', $obj->getType());
        $this->assertEquals(['inetnum'], $obj->getPrimaryKeyNames());
        $this->assertSame('ASSIGNED NON-PORTABLE', $obj['status']);
    }

    public function testInetnumSetups()
    {
        $obj1 = new RPSL\Inetnum('192.168.1.0-192.168.1.255');
        $this->assertSame('192.168.1.0 - 192.168.1.255', $obj1->getHandle());

        $obj2 = new RPSL\Inetnum('192.168.1.0/24');
        $this->assertSame('192.168.1.0 - 192.168.1.255', $obj2->getHandle());
    }

    public function invalidIP4Cidr()
    {
        return [
            ['2001:0DB8::/32'],
            ['192.168.1.0/64'],
            ['192.168.312.0/20'],
            ['255.255.255.0/20'],
        ];
    }

    /**
     * @dataProvider invalidIP4Cidr
     * @expectedException \Dormilich\APNIC\Exceptions\InvalidValueException
     * @expectedExceptionMessage Invalid IPv4 CIDR
     */
    public function testInetnumWithInvalidAddressFails($cidr)
    {
        new RPSL\Inetnum($cidr);
    }

    /**
     * @expectedException \Dormilich\APNIC\Exceptions\InvalidValueException
     * @expectedExceptionMessage Invalid IPv4 address range
     */
    public function testInetnumWithInvalidIPsFails()
    {
        new RPSL\Inetnum('test');
    }

    /**
     * @expectedException \Dormilich\APNIC\Exceptions\InvalidValueException
     * @expectedExceptionMessage Invalid status for the Inetnum object
     */
    public function testInetnumWithInvalidStatusFails()
    {
        $obj = new RPSL\Inetnum('192.168.1.0/24');
        $obj['status'] = 'invalid status';
    }

    public function testInetRtr()
    {
        $obj = new RPSL\InetRtr('test');
        $obj['local-as'] = 'AS135';

        $this->assertSame('inet-rtr', $obj->getType());
        $this->assertEquals(['inet-rtr'], $obj->getPrimaryKeyNames());
    }

    /**
     * @expectedException \Dormilich\APNIC\Exceptions\InvalidValueException
     * @expectedExceptionMessage Invalid AS number
     */
    public function testInetRtrWithInvalidAsnFails()
    {
        $obj = new RPSL\InetRtr('test');
        $obj['local-as'] = 'NaN';
    }

    public function testIrt()
    {
        $obj = new RPSL\Irt('IRT-PHPUNIT');

        $this->assertSame('irt', $obj->getType());
        $this->assertEquals(['irt'], $obj->getPrimaryKeyNames());
    }

    public function testKeyCert()
    {
        $obj = new RPSL\KeyCert('PGPKEY-83F2A90E');

        $this->assertSame('key-cert', $obj->getType());
        $this->assertEquals(['key-cert'], $obj->getPrimaryKeyNames());
    }

    /**
     * @expectedException \Dormilich\APNIC\Exceptions\InvalidValueException
     * @expectedExceptionMessage Invalid key-cert ID
     */
    public function testKeyCertWithInvalidKeyFails()
    {
        new RPSL\KeyCert('test');
    }

    public function testMntner()
    {
        $obj = new RPSL\Mntner('MAINT-PHPUNIT');

        $this->assertSame('mntner', $obj->getType());
        $this->assertEquals(['mntner'], $obj->getPrimaryKeyNames());
    }

    public function testOrganisation()
    {
        $obj = new RPSL\Organisation('ORG-PHPUNIT');

        $this->assertSame('organisation', $obj->getType());
        $this->assertEquals(['organisation'], $obj->getPrimaryKeyNames());
    }

    public function testPeeringSet()
    {
        $obj = new RPSL\PeeringSet('PRNG-examplenet');

        $this->assertSame('peering-set', $obj->getType());
        $this->assertEquals(['peering-set'], $obj->getPrimaryKeyNames());
    }

    public function testPeeringSetValidity()
    {
        $obj = new RPSL\PeeringSet('PRNG-EXAMPLENET');
        // add required attributes
        $obj->set('descr', 'test peering')
            ->set('tech-c', 'TEST-APNIC')
            ->set('admin-c', 'TEST-APNIC')
            ->set('mnt-by', 'MAINT-EU-TEST')
            ->set('changed', 'hostmaster@example.com')
            ->set('source', 'APNIC')
        ;
        // either peering attribute makes it valid:
        $this->assertFalse($obj->isValid());

        $obj['peering'] = 'treat this as a valid peering';
        $this->assertTrue($obj->isValid());

        unset($obj['peering']);
        $this->assertFalse($obj->isValid());

        $obj['mp-peering'] = 'treat this as a valid peering';
        $this->assertTrue($obj->isValid());
    }

    /**
     * @expectedException \Dormilich\APNIC\Exceptions\InvalidValueException
     * @expectedExceptionMessage Invalid peering-set name
     */
    public function testPeeringSetWithInvalidKeyFails()
    {
        new RPSL\PeeringSet('test');
    }

    public function testPerson()
    {
        $obj = new RPSL\Person('PHPUNIT-AP');

        $this->assertSame('person', $obj->getType());
        $this->assertEquals(['nic-hdl'], $obj->getPrimaryKeyNames());
    }

    public function testRole()
    {
        $obj = new RPSL\Role('PHPUNIT-AP');

        $this->assertSame('role', $obj->getType());
        $this->assertEquals(['nic-hdl'], $obj->getPrimaryKeyNames());
    }

    public function routeKeyProvider()
    {
        return [
            ['192.168.1.0/24AS135'],
            ['192.168.1.0/24 AS135'],
            ['AS135 192.168.1.0/24'],
        ];
    }

    /**
     * @dataProvider routeKeyProvider
     */
    public function testRoute($key)
    {
        $obj = new RPSL\Route($key);

        $this->assertSame('route', $obj->getType());
        $this->assertEquals(['route', 'origin'], $obj->getPrimaryKeyNames());
        $this->assertSame('192.168.1.0/24AS135', $obj->getHandle());
        $this->assertSame('192.168.1.0/24', $obj['route']);
        $this->assertSame('AS135', $obj['origin']);
    }

    // Route / Route6 are the only objects with a composite key
    // so passed objects must be treated in the object, rather than the attribute
    public function testSetUpRouteWithRouteInstance()
    {
        $src = new RPSL\Route('192.168.1.0/24 AS135');
        $obj = new RPSL\Route($src);

        $this->assertSame($src->getHandle(), $obj->getHandle());
    }

    /**
     * @expectedException \Dormilich\APNIC\Exceptions\InvalidValueException
     * @expectedExceptionMessage Invalid IPv4 route
     */
    public function testRouteWithInvalidAddressFails()
    {
        new RPSL\Route('2001:0DB8::/32AS135');
    }

    /**
     * @expectedException \Dormilich\APNIC\Exceptions\InvalidValueException
     * @expectedExceptionMessage Invalid IPv4 route
     */
    public function testRouteWithInvalidIPv4CidrFails()
    {
        new RPSL\Route('192.168.1.0/44AS135'); // prefix length too large
    }

    /**
     * @expectedException \Dormilich\APNIC\Exceptions\InvalidValueException
     * @expectedExceptionMessage Invalid AS number
     */
    public function testRouteWithInvalidAsnFails()
    {
        $obj = new RPSL\Route('192.168.1.0/24ASN');
    }

    /**
     * @expectedException \Dormilich\APNIC\Exceptions\InvalidValueException
     * @expectedExceptionMessage Invalid AS number
     */
    public function testEditRouteWithInvalidOriginFails()
    {
        $obj = new RPSL\Route('192.168.1.0/24AS135');
        $obj['origin'] = 'ASN';
    }

    public function route6KeyProvider()
    {
        return [
            ['2001:0DB8::/64AS135'],
            ['2001:0DB8::/64 AS135'],
            ['AS135 2001:0DB8::/64'],
        ];
    }

    /**
     * @dataProvider route6KeyProvider
     */
    public function testRoute6($key)
    {
        $obj = new RPSL\Route6($key);

        $this->assertSame('route6', $obj->getType());
        $this->assertEquals(['route6', 'origin'], $obj->getPrimaryKeyNames());
        $this->assertSame('2001:0DB8::/64AS135', $obj->getHandle());
        $this->assertSame('2001:0DB8::/64', $obj['route6']);
        $this->assertSame('AS135', $obj['origin']);
    }

    // Route / Route6 are the only objects with a composite key
    // so passed objects must be treated in the object, rather than the attribute
    public function testSetUpRoute6WithRouteInstance()
    {
        $src = new RPSL\Route6('2001:0DB8::/64 AS135');
        $obj = new RPSL\Route6($src);

        $this->assertSame($src->getHandle(), $obj->getHandle());
    }

    /**
     * @expectedException \Dormilich\APNIC\Exceptions\InvalidValueException
     * @expectedExceptionMessage Invalid IPv6 route
     */
    public function testRoute6WithInvalidAddressFails()
    {
        new RPSL\Route6('abcx:759/24AS135');
    }

    /**
     * @expectedException \Dormilich\APNIC\Exceptions\InvalidValueException
     * @expectedExceptionMessage Invalid AS number
     */
    public function testRoute6WithInvalidAsnFails()
    {
        $obj = new RPSL\Route6('2001:0DB8::/32ASN');
    }

    /**
     * @expectedException \Dormilich\APNIC\Exceptions\InvalidValueException
     * @expectedExceptionMessage Invalid AS number
     */
    public function testEditRoute6WithInvalidOriginFails()
    {
        $obj = new RPSL\Route6('2001:0DB8::/32AS135');
        $obj['origin'] = 'ASN';
    }

    public function testRouteSet()
    {
        $obj = new RPSL\RouteSet('test');

        $this->assertSame('route-set', $obj->getType());
        $this->assertEquals(['route-set'], $obj->getPrimaryKeyNames());
    }

    public function testRtrSet()
    {
        $obj = new RPSL\RtrSet('test');

        $this->assertSame('rtr-set', $obj->getType());
        $this->assertEquals(['rtr-set'], $obj->getPrimaryKeyNames());
    }
}
