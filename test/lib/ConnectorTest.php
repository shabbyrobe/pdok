<?php
namespace PDOK\Test;

use PDOK\Connector;

class ConnectorTest extends \CustomTestCase
{
    public function testEngine()
    {
        $c = new Connector('pants:foo=bar');
        $this->assertEquals('pants', $c->engine);
    }
    
    /**
     * @covers PDOK\Connector::connect
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
     * @covers PDOK\Connector::inTransaction
     * @dataProvider dataForProxies
     */
    public function testProxies($method, $args=array())
    {
        $connector = $this->getMockBuilder('PDOK\Connector')
            ->setMethods(array('createPDO'))
            ->disableOriginalConstructor()
            ->getMock();
        $pdo = $this->getMockBuilder('stdClass')
            ->setMethods(array($method))
            ->getMock();

        $connector->expects($this->any())->method('createPDO')->will($this->returnValue($pdo));
        $expect = $pdo->expects($this->once())->method($method);
        $connector->connect();
        
        if ($args) {
            $equals = array();
            foreach ($args as $a) {
                $equals[] = $this->equalTo($a);
            }
            $expect = call_user_func_array(array($expect, 'with'), $equals);
        }
        $expect->will($this->returnValue(true));
        
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
            array('inTransaction'),
            
            // query just takes whatever you throw at it
            array('query'),
            array('query', array('foo')),
            array('query', array('stmt', 'foo', 'bar', 'baz', 'qux')),
        );
    }

    /**
     * @covers PDOK\Connector::connect
     * @covers PDOK\Connector::exec
     * @covers PDOK\Connector::prepare
     * @covers PDOK\Connector::query
     * @covers PDOK\Connector::quote
     * @covers PDOK\Connector::beginTransaction
     * @covers PDOK\Connector::commit
     * @covers PDOK\Connector::rollback
     * @dataProvider dataForAutoConnect
     */
    public function testAutoConnect($method, $args=array())
    {
        $connector = $this->getMockBuilder('PDOK\Connector')
            ->setMethods(array('createPDO'))
            ->disableOriginalConstructor()
            ->getMock();
        $pdo = $this->getMockBuilder('stdClass')
            ->setMethods(array($method))
            ->getMock();

        $connector->expects($this->once())->method('createPDO')->will($this->returnValue($pdo));
        $expect = $pdo->expects($this->once())->method($method)->will($this->returnValue(true));

        $this->assertFalse($connector->isConnected());
        call_user_func_array(array($connector, $method), $args);
        $this->assertTrue($connector->isConnected());
    }

    public function dataForAutoConnect()
    {
        return array(
            array('exec', array('yep')),
            array('prepare', array('stmt', array('k'=>'v'))),
            array('quote', array('q')),
            array('beginTransaction'),
            array('commit'),
            array('rollback'),
            array('query'),
        );
    }

    /**
     * @covers PDOK\Connector::__clone
     */
    public function testClone()
    {
        $conn = new \PDOK\Connector('sqlite::memory:');
        $conn->connect();
        $this->assertTrue($conn->isConnected());

        $conn2 = clone $conn;
        $this->assertTrue($conn->isConnected());
        $this->assertFalse($conn2->isConnected());

        $conn2->connect();
        $this->assertNotSame($conn->pdo, $conn2->pdo);
    }

    /**
     * @covers PDOK\Connector::__sleep
     */
    public function testSerialize()
    {
        $conn = new \PDOK\Connector('sqlite::memory:');
        $conn->connect();
        $this->assertTrue($conn->isConnected());

        $conn2 = unserialize(serialize($conn));
        $this->assertFalse($conn2->isConnected());
        $props = array('dsn', 'engine', 'username', 'password', 'driverOptions', 'connectionStatements');
        foreach ($props as $prop) {
            $this->assertEquals($conn->$prop, $conn2->$prop);
        }
        $this->assertEquals($this->getProtected($conn, 'useWrapper'), $this->getProtected($conn, 'useWrapper'));
    }

    /**
     * @covers PDOK\Connector::createPDO
     */
    public function testCreatePDOConnectionStatements()
    {
        $conn = \PDOK\Connector::create(array('dsn'=>'sqlite::memory:', 'connectionStatements'=>array(
            'CREATE TABLE foo(id INT);',
            'INSERT INTO foo VALUES(1);',
            'INSERT INTO foo VALUES(2);',
        )));
        $rows = $conn->query("SELECT * FROM foo")->fetchAll(\PDO::FETCH_COLUMN, 0);
        $this->assertEquals(array(1, 2), $rows);
    }

    /**
     * @covers PDOK\Connector::exec
     * @covers PDOK\Connector::execute
     */
    public function testExecuteWithParams()
    {
        $conn = new \PDOK\Connector('sqlite::memory:');
        $conn->execute("CREATE TABLE foo(id INT, val STRING);");
        $conn->execute("INSERT INTO foo(id, val) VALUES(:id, :val)", array(':id'=>3, ':val'=>'yep'));
        $rows = $conn->query("SELECT * FROM foo")->fetchAll(\PDO::FETCH_ASSOC);
        $this->assertEquals(array(array('id'=>3, 'val'=>'yep')), $rows);
    }

    /**
     * @covers PDOK\Connector::execAll
     * @covers PDOK\Connector::executeAll
     */
    public function testExecuteAll()
    {
        $conn = new \PDOK\Connector('sqlite::memory:');
        $conn->executeAll(array(
            "CREATE TABLE foo(id INT, val STRING);",
            "INSERT INTO foo(id, val) VALUES(3, 'yep');",
        ));
        $rows = $conn->query("SELECT * FROM foo")->fetchAll(\PDO::FETCH_ASSOC);
        $this->assertEquals(array(array('id'=>3, 'val'=>'yep')), $rows);
    }

    /**
     * @covers PDOK\Connector::quoteIdentifier
     * @dataProvider dataForQuoteIdentifier
     */
    public function testQuoteIdentifier($engine, $value, $quoted)
    {
        $conn = new \PDOK\Connector('sqlite::memory:');
        $conn->engine = $engine;
        $result = $conn->quoteIdentifier($value);
        $this->assertEquals($quoted, $result);
    }

    /**
     * @covers PDOK\Connector::inTransaction
     */
    public function testInTransactionReturnsFalseWhenDisconnected()
    {
        $conn = new \PDOK\Connector('sqlite::memory:');
        $this->assertFalse($conn->inTransaction());
    }

    public function dataForQuoteIdentifier()
    {
        return array(
            array('sqlite', 'foo bar', '`foo bar`'),
            array('mysql', 'foo bar', '`foo bar`'),
            array('pgsql', 'foo bar', '"foo bar"'),
            array('oci', 'foo bar', '"foo bar"'),
            array('mssql', 'foo bar', '[foo bar]'),
        );
    }
}
