<?php
namespace PDOK\Test;

use PDOK\Connector;

class ConnectorAttributeTest extends \CustomTestCase
{
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
        $c = new Connector('sqlite::memory:');
        $c->connect();
        $this->assertEquals(0, $c->getAttribute(\PDO::ATTR_CASE));
        $c->setAttribute(\PDO::ATTR_CASE, \PDO::CASE_UPPER);
        $this->assertEquals(\PDO::CASE_UPPER, $c->getAttribute(\PDO::ATTR_CASE));
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

    public function testAttributesSurviveDisconnection()
    {
        $expected = \PDO::CASE_UPPER;
        $conn = new \PDOK\Connector('sqlite::memory:');
        $conn->setAttribute(\PDO::ATTR_CASE, $expected);
        $this->assertEquals($expected, $conn->getAttribute(\PDO::ATTR_CASE));

        $conn->connect();
        $this->assertEquals($expected, $conn->getAttribute(\PDO::ATTR_CASE));

        $conn->disconnect();
        $this->assertEquals($expected, $conn->getAttribute(\PDO::ATTR_CASE));

        $conn->connect();
        $this->assertEquals($expected, $conn->getAttribute(\PDO::ATTR_CASE));
    }
}
