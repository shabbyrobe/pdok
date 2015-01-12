<?php
namespace PDOK\Test\Unit;

use PDOK\Connector;

/**
 * @group unit
 */
class ConnectorCreateTest extends \CustomTestCase
{
    /**
     * @covers PDOK\Connector::create
     */
    function testCreateFromArrayWithDsn()
    {
        $c = Connector::create(array(
            'dsn'=>'mysql:host=localhost;dbname=pants',
            'user'=>'foo',
            'pass'=>'bar',
        ));
        $this->assertEquals('mysql:host=localhost;dbname=pants', $c->dsn);
        $this->assertEquals('foo', $c->username);
        $this->assertEquals('bar', $c->password);
    }
    
    /**
     * @covers PDOK\Connector::create
     */
    function testCreateFromArrayWithHost()
    {
        $c = Connector::create(array(
            'host'=>'localhost',
        ));
        $this->assertEquals('mysql:host=localhost;', $c->dsn);
    }
    
    /**
     * @covers PDOK\Connector::create
     */
    function testCreateFromArrayWithHostAndPort()
    {
        $c = Connector::create(array(
            'host'=>'localhost',
            'port'=>'123',
        ));
        $this->assertEquals('mysql:host=localhost;port=123;', $c->dsn);
    }
    
    /**
     * @covers PDOK\Connector::create
     * @dataProvider dbNameKeysProvider
     */
    function testCreateFromArrayWithHostAndDbName($key)
    {
        $c = Connector::create(array(
            'host'=>'localhost',
            $key=>'abc',
        ));
        $this->assertEquals('mysql:host=localhost;dbname=abc;', $c->dsn);
    }
    
    function dbNameKeysProvider()
    {
        return array(
            array('db'),
            array('DB'),
            array('dbname'),
            array('dbName'),
            array('database'),
            array('DataBASE'),
        );
    }
    
    /**
     * @covers PDOK\Connector::create
     * @dataProvider userNameKeysProvider
     */
    function testCreateFromArrayUserName($key)
    {
        $c = Connector::create(array(
            'host'=>'localhost',
            $key=>'abc',
        ));
        $this->assertEquals('abc', $c->username);
    }
    
    function userNameKeysProvider()
    {
        return array(
            array('u'),
            array('uname'),
            array('user'),
            array('username'),
            array('userName'),
        );
    }
    
    /**
     * @covers PDOK\Connector::create
     * @dataProvider passwordKeysProvider
     */
    function testCreateFromArrayPassword($key)
    {
        $c = Connector::create(array(
            'host'=>'localhost',
            $key=>'abc',
        ));
        $this->assertEquals('abc', $c->password);
    }
    
    function passwordKeysProvider()
    {
        return array(
            array('p'),
            array('P'),
            array('pass'),
            array('PASSWD'),
            array('password'),
            array('PAssWOrd'),
        );
    }
}
