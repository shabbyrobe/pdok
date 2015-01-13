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
     * @dataProvider dataForCreateUser
     */
    public function testCreateUser($key)
    {
        $conn = \PDOK\Connector::create(array($key=>'myuser'));
        $this->assertEquals('myuser', $conn->username);
    }
    
    public function dataForCreateUser()
    {
        return array(
            array('u'),
            array('UNAME'),
            array('unagi'),
            array('user'),
            array('uname'),
            array('username'),
            array('userName'),
        );
    }

    /**
     * @covers PDOK\Connector::create
     * @dataProvider dataForCreatePassword
     */
    public function testCreatePassword($key)
    {
        $conn = \PDOK\Connector::create(array($key=>'passw0rd'));
        $this->assertEquals('passw0rd', $conn->password);
    }
    
    public function dataForCreatePassword()
    {
        return array(
            array('pa'),
            array('Pass'),
            array('paSSword'),
            array('passwd'),
            array('PASSWD'),
        );
    }

    /**
     * @covers PDOK\Connector::create
     * @dataProvider dataForCreateHost
     */
    public function testCreateHost($hostKey)
    {
        $conn = \PDOK\Connector::create(array($hostKey=>'dbhost'));
        $this->assertEquals('mysql:host=dbhost;', $conn->dsn);
    }
    
    public function dataForCreateHost()
    {
        return array(
            array('host'),
            array('HOst'),
            array('hostName'),
            array('hOSTAGe'),
            array('hOSTAGe'),
            array('host_name'),
            array('server'),
        );
    }

    /**
     * @covers PDOK\Connector::create
     * @dataProvider dataForCreateOptions
     */
    public function testCreateOptions($key)
    {
        $conn = \PDOK\Connector::create(array($key=>array(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY=>true)));
        $this->assertEquals(array(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY=>true), $conn->driverOptions);
    }
    
    public function dataForCreateOptions()
    {
        return array(
            array('driverOptions'),
            array('driveroptions'),
            array('options'),
        );
    }

    /**
     * @covers PDOK\Connector::create
     * @dataProvider dataForCreateConnectionStatements
     */
    public function testCreateConnectionStatements($key)
    {
        $conn = \PDOK\Connector::create(array($key=>array('a', 'b')));
        $this->assertEquals(array('a', 'b'), $conn->connectionStatements);
    }
    
    public function dataForCreateConnectionStatements()
    {
        return array(
            array('connectionstatements'),
            array('CONNECTIONstatements'),
            array('statements'),
        );
    }

    /**
     * @covers PDOK\Connector::create
     */
    public function testCreateDsn()
    {
        $value = 'mysql:host=localhost;dbname=foobar';
        $conn = \PDOK\Connector::create(array('dsn'=>$value));
        $this->assertEquals($value, $conn->dsn);
    }

    /**
     * @covers PDOK\Connector::create
     */
    public function testCreateDsnOverridesHost()
    {
        $value = 'mysql:host=localhost;dbname=foobar';
        $conn = \PDOK\Connector::create(array('dsn'=>$value, 'host'=>'whoopee'));
        $this->assertEquals($value, $conn->dsn);
    }

}
