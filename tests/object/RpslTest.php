<?php

use Dormilich\APNIC\RPSL as RPSL;
use Dormilich\APNIC\AttributeInterface as Attr;
use PHPUnit\Framework\TestCase;

class RpslTest extends TestCase
{
    public function testAsBlock()
    {
        $obj = new RPSL\AsBlock('test');
        $names = [
            'as-block', 'descr', 'country', 'remarks', 'tech-c', 'admin-c', 
            'notify', 'mnt-lower', 'mnt-by', 'changed', 'source', 
        ];
        $this->assertEquals($names, $obj->getAttributeNames());
    }

    public function testAsSet()
    {
        $obj = new RPSL\AsSet('test');
        $names = [
            'as-set', 'descr', 'country', 'members', 'mbrs-by-ref', 
            'remarks', 'tech-c', 'admin-c', 'notify', 'mnt-by', 
            'mnt-lower', 'changed', 'source', 
        ];
        $this->assertEquals($names, $obj->getAttributeNames());
    }

    public function testAutNum()
    {
        $obj = new RPSL\AutNum('AS135');
        $names = [
            'aut-num', 'as-name', 'descr', 'country', 'member-of', 
            'import', 'export', 'default', 'remarks', 'admin-c', 
            'tech-c', 'notify', 'mnt-lower', 'mnt-routes', 'mnt-by', 
            'mnt-irt', 'changed', 'source', 
        ];
        $this->assertEquals($names, $obj->getAttributeNames());
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
        $names = [
            'domain', 'descr', 'country', 'admin-c', 'tech-c', 
            'zone-c', 'nserver', 'remarks', 'notify', 'mnt-by', 
            'mnt-lower', 'changed', 'source', 
        ];
        $this->assertEquals($names, $obj->getAttributeNames());
    }

    public function testIPv6Domain()
    {
        $obj = new RPSL\Domain('0DB8.2001.ip6.arpa');
        $this->assertSame('0DB8.2001.ip6.arpa', $obj->getPrimaryKey());
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
        $names = [
            'filter-set', 'descr', 'filter', 'mp-filter', 'remarks', 'tech-c', 
            'admin-c', 'notify', 'mnt-by', 'mnt-lower', 'changed', 'source', 
        ];
        $this->assertEquals($names, $obj->getAttributeNames());
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
        $names = [
            'inet6num', 'netname', 'descr', 'country', 'geoloc', 
            'language', 'admin-c', 'tech-c', 'status', 'remarks', 
            'notify', 'mnt-by', 'mnt-lower', 'mnt-routes', 'mnt-irt', 
            'changed', 'source', 
        ];
        $this->assertEquals($names, $obj->getAttributeNames());

        $obj['status'] = 'assigned portable';
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
        $names = [
            'inetnum', 'netname', 'descr', 'country', 'geoloc', 
            'language', 'admin-c', 'tech-c', 'status', 'remarks', 
            'notify', 'mnt-by', 'mnt-lower', 'mnt-routes', 'mnt-domains', 
            'mnt-irt', 'changed', 'source', 
        ];
        $this->assertEquals($names, $obj->getAttributeNames());

        $obj['status'] = 'assigned non-portable';
        $this->assertSame('ASSIGNED NON-PORTABLE', $obj['status']);
    }

    public function testInetnumSetups()
    {
        $obj1 = new RPSL\Inetnum('192.168.1.0-192.168.1.255');
        $this->assertSame('192.168.1.0 - 192.168.1.255', $obj1->getPrimaryKey());

        $obj2 = new RPSL\Inetnum('192.168.1.0', '192.168.1.255');
        $this->assertSame('192.168.1.0 - 192.168.1.255', $obj2->getPrimaryKey());

        $obj3 = new RPSL\Inetnum('192.168.1.0/24');
        $this->assertSame('192.168.1.0 - 192.168.1.255', $obj3->getPrimaryKey());
    }

    public function invalidIP4Addresses()
    {
        return [
            ['test'],
            ['2001:0DB8::/32'],
            ['192.168.1.0/64'],
            ['192.168.312.0/20'],
            ['255.255.255.0/20'],
        ];
    }

    /**
     * @dataProvider invalidIP4Addresses
     * @expectedException \Dormilich\APNIC\Exceptions\InvalidValueException
     * @expectedExceptionMessage Invalid IPv4 address range
     */
    public function testInetnumWithInvalidAddressFails($address)
    {
        new RPSL\Inetnum($address);
    }

    /**
     * @expectedException \Dormilich\APNIC\Exceptions\InvalidValueException
     * @expectedExceptionMessage Invalid IPv4 address range
     */
    public function testInetnumWithInvalidIPsFails()
    {
        new RPSL\Inetnum('192.168.1.0', '24');
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
        $names = [
            'inet-rtr', 'descr', 'alias', 'local-as', 'ifaddr', 
            'interface', 'peer', 'mp-peer', 'member-of', 'remarks', 
            'admin-c', 'tech-c', 'notify', 'mnt-by', 'changed', 'source', 
        ];
        $this->assertEquals($names, $obj->getAttributeNames());

        $obj['local-as'] = 'AS135';
        $this->assertSame('AS135', $obj['local-as']);
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
        $obj = new RPSL\Irt('test');
        $names = [
            'irt', 'address', 'phone', 'fax-no', 'e-mail', 'abuse-mailbox', 
            'signature', 'encryption', 'admin-c', 'tech-c', 'auth', 
            'remarks', 'irt-nfy', 'notify', 'mnt-by', 'changed', 'source', 
        ];
        $this->assertEquals($names, $obj->getAttributeNames());

        // phone & email validation failure is covered in ValidationTest.php

        $obj['phone'] = '+12 34 567890 010';
        $this->assertEquals(['+12 34 567890 010'], $obj['phone']);

        $obj['fax-no'] = '+12 34 567890 010';
        $this->assertEquals(['+12 34 567890 010'], $obj['fax-no']);

        $obj['e-mail'] = 'test@example.com';
        $this->assertEquals(['test@example.com'], $obj['e-mail']);

        $obj['abuse-mailbox'] = 'test@example.com';
        $this->assertEquals(['test@example.com'], $obj['abuse-mailbox']);

        $obj['irt-nfy'] = 'test@example.com';
        $this->assertEquals(['test@example.com'], $obj['irt-nfy']);
    }

    public function testKeyCert()
    {
        $obj = new RPSL\KeyCert('PGPKEY-83F2A90E');
        $names = [
            'key-cert', 'certif', 'remarks', 'notify', 'admin-c', 'tech-c', 
            'mnt-by', 'changed', 'source', 'method', 'owner', 'fingerpr', 
        ];
        $this->assertEquals($names, $obj->getAttributeNames());
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
        $obj = new RPSL\Mntner('MAINT-EU-test');
        $names = [
            'mntner', 'descr', 'country', 'admin-c', 'tech-c', 
            'upd-to', 'mnt-nfy', 'auth', 'remarks', 'notify', 
            'abuse-mailbox', 'mnt-by', 'referral-by', 'changed', 'source', 
        ];
        $this->assertEquals($names, $obj->getAttributeNames());
        $this->assertSame('MAINT-EU-TEST', $obj->getPrimaryKey());

        // email validation failure is covered in ValidationTest.php

        $obj['upd-to'] = 'test@example.com';
        $this->assertEquals(['test@example.com'], $obj['upd-to']);

        $obj['abuse-mailbox'] = 'test@example.com';
        $this->assertEquals(['test@example.com'], $obj['abuse-mailbox']);

        $obj['mnt-nfy'] = 'test@example.com';
        $this->assertEquals(['test@example.com'], $obj['mnt-nfy']);
    }

    public function testPeeringSet()
    {
        $obj = new RPSL\PeeringSet('PRNG-examplenet');
        $names = [
            'peering-set', 'descr', 'peering', 'mp-peering', 
            'remarks', 'tech-c', 'admin-c', 'notify', 'mnt-by', 
            'mnt-lower', 'changed', 'source', 
        ];
        $this->assertEquals($names, $obj->getAttributeNames());
        $this->assertSame('PRNG-EXAMPLENET', $obj->getPrimaryKey());
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
        $obj = new RPSL\Person('John Doe');
        $names = [
            'person', 'address', 'country', 'phone', 'fax-no', 
            'e-mail', 'nic-hdl', 'remarks', 'notify', 
            'abuse-mailbox', 'mnt-by', 'changed', 'source', 
        ];
        $this->assertEquals($names, $obj->getAttributeNames());

        // phone & email validation failure is covered in ValidationTest.php

        $obj['phone'] = '+12 34 567890 010';
        $this->assertEquals(['+12 34 567890 010'], $obj['phone']);

        $obj['fax-no'] = '+12 34 567890 010';
        $this->assertEquals(['+12 34 567890 010'], $obj['fax-no']);

        $obj['e-mail'] = 'test@example.com';
        $this->assertEquals(['test@example.com'], $obj['e-mail']);

        $obj['abuse-mailbox'] = 'test@example.com';
        $this->assertEquals(['test@example.com'], $obj['abuse-mailbox']);
    }

    public function testRole()
    {
        $obj = new RPSL\Role('Abuse');
        $names = [
            'role', 'address', 'country', 'phone', 'fax-no', 
            'e-mail', 'admin-c', 'tech-c', 'nic-hdl', 'remarks', 
            'notify', 'abuse-mailbox', 'mnt-by', 'changed', 'source', 
        ];
        $this->assertEquals($names, $obj->getAttributeNames());

        // phone & email validation failure is covered in ValidationTest.php

        $obj['phone'] = '+12 34 567890 010';
        $this->assertEquals(['+12 34 567890 010'], $obj['phone']);

        $obj['fax-no'] = '+12 34 567890 010';
        $this->assertEquals(['+12 34 567890 010'], $obj['fax-no']);

        $obj['e-mail'] = 'test@example.com';
        $this->assertEquals(['test@example.com'], $obj['e-mail']);

        $obj['abuse-mailbox'] = 'test@example.com';
        $this->assertEquals(['test@example.com'], $obj['abuse-mailbox']);
    }

    public function testRoute()
    {
        $obj = new RPSL\Route('192.168.1.0/24');
        $names = [
            'route', 'descr', 'country', 'origin', 'holes', 
            'member-of', 'inject', 'aggr-mtd', 'aggr-bndry', 
            'export-comps', 'components', 'remarks', 'notify', 
            'mnt-lower', 'mnt-routes', 'mnt-by', 'changed', 'source', 
        ];
        $this->assertEquals($names, $obj->getAttributeNames());

        $this->assertNull($obj->getPrimaryKey());

        $obj['origin'] = 'AS135';
        $this->assertSame('AS135', $obj['origin']);

        $this->assertSame('192.168.1.0/24AS135', $obj->getPrimaryKey());
    }

    public function testRouteWithCompositeKey()
    {
        $obj = new RPSL\Route('192.168.1.0/24AS135');
        $this->assertSame('192.168.1.0/24', $obj['route']);
        $this->assertSame('AS135', $obj['origin']);
    }

    /**
     * @expectedException \Dormilich\APNIC\Exceptions\InvalidValueException
     * @expectedExceptionMessage Invalid IPv4 route
     */
    public function testRouteWithInvalidAddressFails()
    {
        new RPSL\Route('2001:0DB8::/32');
    }

    /**
     * @expectedException \Dormilich\APNIC\Exceptions\InvalidValueException
     * @expectedExceptionMessage Invalid IPv4 route
     */
    public function testRouteWithInvalidIPv4CidrFails()
    {
        new RPSL\Route('192.168.1.0/44'); // prefix length too large
    }

    /**
     * @expectedException \Dormilich\APNIC\Exceptions\InvalidValueException
     * @expectedExceptionMessage Invalid AS number
     */
    public function testRouteWithInvalidAsnFails()
    {
        $obj = new RPSL\Route('192.168.1.0/24');
        $obj['origin'] = 'ASN';
    }

    public function testRoute6()
    {
        $obj = new RPSL\Route6('2001:0DB8::/64');
        $names = [
            'route6', 'descr', 'country', 'origin', 'holes', 
            'member-of', 'inject', 'aggr-mtd', 'aggr-bndry', 
            'export-comps', 'components', 'remarks', 'notify', 
            'mnt-lower', 'mnt-routes', 'mnt-by', 'changed', 'source', 
        ];
        $this->assertEquals($names, $obj->getAttributeNames());

        $this->assertNull($obj->getPrimaryKey());

        $obj['origin'] = 'AS135';
        $this->assertSame('AS135', $obj['origin']);

        $this->assertSame('2001:0DB8::/64AS135', $obj->getPrimaryKey());
    }

    public function testRoute6WithCompositeKey()
    {
        $obj = new RPSL\Route6('2001:0DB8::/48AS135');
        $this->assertSame('2001:0DB8::/48', $obj['route6']);
        $this->assertSame('AS135', $obj['origin']);
    }

    public function testRoute6WithInet6num()
    {
        $net = new RPSL\Inet6num('2001:0DB8::/48');
        $obj = new RPSL\Route6($net);
        $this->assertSame('2001:0DB8::/48', $obj['route6']);
        $this->assertNull($obj->getPrimaryKey());
    }

    /**
     * @expectedException \Dormilich\APNIC\Exceptions\InvalidValueException
     * @expectedExceptionMessage Invalid IPv6 route
     */
    public function testRoute6WithInvalidAddressFails()
    {
        new RPSL\Route6('abcx:759/24');
    }

    /**
     * @expectedException \Dormilich\APNIC\Exceptions\InvalidValueException
     * @expectedExceptionMessage Invalid AS number
     */
    public function testRoute6WithInvalidAsnFails()
    {
        $obj = new RPSL\Route6('2001:0DB8::/32');
        $obj['origin'] = 'ASN';
    }

    public function testRouteSet()
    {
        $obj = new RPSL\RouteSet('test');
        $names = [
            'route-set', 'descr', 'members', 'mp-members', 
            'mbrs-by-ref', 'remarks', 'tech-c', 'admin-c', 
            'notify', 'mnt-by', 'mnt-lower', 'changed', 'source', 
        ];
        $this->assertEquals($names, $obj->getAttributeNames());
    }

    public function testRtrSet()
    {
        $obj = new RPSL\RtrSet('test');
        $names = [
            'rtr-set', 'descr', 'members', 'mp-members', 
            'mbrs-by-ref', 'remarks', 'tech-c', 'admin-c', 
            'notify', 'mnt-by', 'mnt-lower', 'changed', 'source', 
        ];
        $this->assertEquals($names, $obj->getAttributeNames());
    }
}
