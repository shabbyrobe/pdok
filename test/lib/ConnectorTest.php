<?php
namespace PDOK\Test;

use PDOK\Connector;

/**
 * This test doesn't make any real connections, so it should not be necessary to
 * extend from DataTestCase
 * @group unit
 */
class ConnectorTest extends \CustomTestCase
{
    /**
     */
    public function testEngine()
    {
        $c = new Connector('pants:foo=bar');
        $this->assertEquals('pants', $c->engine);
    }
    
    /**
     */
    public function testConnect()
    {
        $c = new Connector('sqlite::memory:');
        $this->assertNull($this->getProtected($c, 'pdo'));
        $c->exec("SELECT * FROM sqlite_master WHERE type='table'");
        $this->assertNotNull($this->getProtected($c, 'pdo'));
    }
    
    /**
     * @covers PDOK\Connector::disconnect
     */
    public function testDisconnect()
    {
        $c = new Connector('sqlite::memory:');
        $c->query("SELECT 1");
        $this->assertNotNull($this->getProtected($c, 'pdo'));
        $c->disconnect();
        $this->assertNull($this->getProtected($c, 'pdo'));
    }
    
    /**
     * @covers PDOK\Connector::setAttribute
     */
    public function testDisconnectedSetAttribute()
    {
        $c = new Connector('sqlite::memory:');
        $this->assertNull($this->getProtected($c, 'pdo'));
        
        $c->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
        $this->assertNull($this->getProtected($c, 'pdo'));
        $this->assertEquals(array(\PDO::ATTR_DEFAULT_FETCH_MODE=>\PDO::FETCH_ASSOC), $this->getProtected($c, 'attributes'));
    }

    /**
     * @covers PDOK\Connector::setAttribute
     */
    public function testConnectedSetAttribute()
    {
        $pdo = $this->getMockBuilder('stdClass')
            ->setMethods(array('setAttribute'))
            ->getMock()
        ;
        $pdo->expects($this->once())->method('setAttribute')->with(
            $this->equalTo(\PDO::ATTR_ERRMODE),
            $this->equalTo(\PDO::ERRMODE_EXCEPTION)
        );
        $c = new Connector('sqlite::memory:');
        $this->setProtected($c, 'pdo', $pdo);
        $c->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    }
    
    /**
     * @covers PDOK\Connector::getAttribute
     */
    public function testDisconnectedGetAttribute()
    {
        $c = new Connector('sqlite::memory:');
        $this->setProtected($c, 'attributes', array(\PDO::ATTR_DEFAULT_FETCH_MODE=>\PDO::FETCH_ASSOC));
        $this->assertNull($this->getProtected($c, 'pdo'));
        
        $attr = $c->getAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE);
        $this->assertNull($this->getProtected($c, 'pdo'));
        $this->assertEquals($attr, \PDO::FETCH_ASSOC);
    }

    /**
     * @covers PDOK\Connector::getAttribute
     */
    public function testConnectedGetAttribute()
    {
        $pdo = $this->getMockBuilder('stdClass')
            ->setMethods(array('getAttribute'))
            ->getMock()
        ;
        $pdo->expects($this->once())->method('getAttribute')
            ->with($this->equalTo(\PDO::ATTR_ERRMODE))
            ->will($this->returnValue(\PDO::ERRMODE_EXCEPTION))
        ;
        
        $c = new Connector('sqlite::memory:');
        $this->setProtected($c, 'pdo', $pdo);
        $this->assertEquals(\PDO::ERRMODE_EXCEPTION, $c->getAttribute(\PDO::ATTR_ERRMODE));
    }

    /**
     * @covers PDOK\Connector::errorInfo
     */
    public function testErrorInfoConnected()
    {
        $pdo = $this->getMockBuilder('stdClass')
            ->setMethods(array('errorInfo'))
            ->getMock()
        ;
        $pdo->expects($this->once())->method('errorInfo');
        
        $c = new Connector('sqlite::memory:');
        $this->setProtected($c, 'pdo', $pdo);
        $c->errorInfo();
    }
    
    /**
     * @covers PDOK\Connector::errorInfo
     */
    public function testErrorInfoDisconnected()
    {
        $c = new Connector('sqlite::memory:');
        $this->assertNull($c->errorInfo());
    }

    /**
     * @covers PDOK\Connector::errorCode
     */
    public function testErrorCodeConnected()
    {
        $pdo = $this->getMockBuilder('stdClass')
            ->setMethods(array('errorCode'))
            ->getMock()
        ;
        $pdo->expects($this->once())->method('errorCode');
        
        $c = new Connector('sqlite::memory:');
        $this->setProtected($c, 'pdo', $pdo);
        $c->errorCode();
    }
    
    /**
     * @covers PDOK\Connector::errorCode
     */
    public function testErrorCodeDisconnected()
    {
        $c = new Connector('sqlite::memory:');
        $this->assertNull($c->errorCode());
    }
    
    /**
     * @covers PDOK\Connector::exec
     * @covers PDOK\Connector::lastInsertId
     * @covers PDOK\Connector::prepare
     * @covers PDOK\Connector::query
     * @covers PDOK\Connector::quote
     * @covers PDOK\Connector::beginTransaction
     * @covers PDOK\Connector::commit
     * @covers PDOK\Connector::rollback
     * @dataProvider dataForProxies
     */
    public function testProxies($method, $args=array())
    {
        $connector = $this->getMockBuilder('PDOK\Connector')
            ->setMethods(array('createPDO'))
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $pdo = $this->getMockBuilder('stdClass')
            ->setMethods(array($method))
            ->getMock()
        ;
        $connector->expects($this->any())->method('createPDO')->will($this->returnValue($pdo));
        $expect = $pdo->expects($this->once())->method($method);
        $connector->connect();
        
        if ($args) {
            $equals = array();
            foreach ($args as $a) {
                $equals[] = $this->equalTo($a);
            }
            call_user_func_array(array($expect, 'with'), $equals);
        }
        
        call_user_func_array(array($connector, $method), $args);
    }
    
    public function dataForProxies()
    {
        return array(
            array('exec', array('yep')),
            array('lastInsertId'),
            array('prepare', array('stmt', array('k'=>'v'))),
            array('quote', array('q')),
            array('beginTransaction'),
            array('commit'),
            array('rollback'),
            
            // query just takes whatever you throw at it
            array('query'),
            array('query', array('foo')),
            array('query', array('stmt', 'foo', 'bar', 'baz', 'qux')),
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
            array('p'),
            array('Pass'),
            array('paSSword'),
            array('passwd'),
            array('plage noire'),
        );
    }

    /**
     * @covers PDOK\Connector::create
     * @dataProvider dataForCreateOptions
     */
    public function testCreateOptions($key)
    {
        $conn = \PDOK\Connector::create(array($key=>[\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY=>true]));
        $this->assertEquals([\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY=>true], $conn->driverOptions);
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
