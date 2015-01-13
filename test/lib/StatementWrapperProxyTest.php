<?php
namespace PDOK\Test;

class StatementWrapperProxyTest extends \CustomTestCase
{
    function setUp()
    {
        $this->statement = $this->getMockBuilder('PDOStatement')
            ->setMethods(array())
            ->getMock();

        $this->connector = new \PDOK\Connector('sqlite::memory:');
        $this->wrapper = new \PDOK\StatementWrapper($this->connector, $this->statement);
    }

    /**
     * @covers PDOK\StatementWrapper::bindColumn
     */
    function testBindColumn()
    {
        $this->statement->expects($this->once())
            ->method('bindColumn')
            ->with('a', 'b', 'c', null, null)
            ->will($this->returnValue(true));

        $val = 'b';
        $this->assertEquals($this->wrapper, $this->wrapper->bindColumn('a', $val, 'c'));
    }

    /**
     * @covers PDOK\StatementWrapper::bindValue
     */
    function testBindValue()
    {
        $this->statement->expects($this->once())
            ->method('bindValue')
            ->with('a', 'b', 'c')
            ->will($this->returnValue(true));

        $val = 'b';
        $this->assertEquals($this->wrapper, $this->wrapper->bindValue('a', $val, 'c'));
    }

    /**
     * @covers PDOK\StatementWrapper::bindParam
     */
    function testBindParam()
    {
        $this->statement->expects($this->once())
            ->method('bindParam')
            ->with('a', 'b', 'c')
            ->will($this->returnValue(true));

        $val = 'b';
        $this->assertEquals($this->wrapper, $this->wrapper->bindParam('a', $val, 'c'));
    }

    /**
     * @covers PDOK\StatementWrapper::closeCursor
     */
    function testCloseCursor()
    {
        $this->statement->expects($this->once())
            ->method('closeCursor')
            ->will($this->returnValue(true));
        $this->assertEquals($this->wrapper, $this->wrapper->closeCursor());
    }

    /**
     * @covers PDOK\StatementWrapper::execute
     */
    function testExecute()
    {
        $this->statement->expects($this->once())
            ->method('execute')
            ->with(array('a'))
            ->will($this->returnValue(true));
        $this->assertEquals($this->wrapper, $this->wrapper->execute(array('a')));
    }

    /**
     * @covers PDOK\StatementWrapper::nextRowset
     */
    function testNextRowset()
    {
        $this->statement->expects($this->once())
            ->method('nextRowset')
            ->will($this->returnValue(true));
        $this->assertEquals($this->wrapper, $this->wrapper->nextRowset());
    }

    /**
     * @covers PDOK\StatementWrapper::setAttribute
     */
    function testSetAttribute()
    {
        $this->statement->expects($this->once())
            ->method('setAttribute')
            ->with('a', 'b')
            ->will($this->returnValue(true));
        $this->assertEquals($this->wrapper, $this->wrapper->setAttribute('a', 'b'));
    }

    /**
     * @covers PDOK\StatementWrapper::setFetchMode
     */
    function testSetFetchMode()
    {
        $this->statement->expects($this->once())
            ->method('setFetchMode')
            ->with('a', 'b', 'c')
            ->will($this->returnValue(true));
        $this->assertEquals($this->wrapper, $this->wrapper->setFetchMode('a', 'b', 'c'));
    }

    /**
     * @covers PDOK\StatementWrapper::columnCount
     */
    function testColumnCount()
    {
        $this->statement->expects($this->once())->method('columnCount')->will($this->returnValue(9999));
        $this->assertEquals(9999, $this->wrapper->columnCount());
    }

    /**
     * @covers PDOK\StatementWrapper::debugDumpParams
     */
    function testDebugDumpParams()
    {
        $this->statement->expects($this->once())->method('debugDumpParams')->will($this->returnValue(9999));
        $this->assertEquals(9999, $this->wrapper->debugDumpParams());
    }
    
    /**
     * @covers PDOK\StatementWrapper::errorCode
     */
    function testErrorCode()
    {
        $this->statement->expects($this->once())->method('errorCode')->will($this->returnValue(9999));
        $this->assertEquals(9999, $this->wrapper->errorCode());
    }
    
    /**
     * @covers PDOK\StatementWrapper::errorInfo
     */
    function testErrorInfo()
    {
        $this->statement->expects($this->once())->method('errorInfo')->will($this->returnValue(9999));
        $this->assertEquals(9999, $this->wrapper->errorInfo());
    }
    
    /**
     * @covers PDOK\StatementWrapper::rowCount
     */
    function testRowCount()
    {
        $this->statement->expects($this->once())->method('rowCount')->will($this->returnValue(9999));
        $this->assertEquals(9999, $this->wrapper->rowCount());
    }

    /**
     * @covers PDOK\StatementWrapper::fetchColumn
     */
    function testFetchColumn()
    {
        $this->statement->expects($this->once())->method('fetchColumn')->with('a')->will($this->returnValue(9999));
        $this->assertEquals(9999, $this->wrapper->fetchColumn('a'));
    }

    /**
     * @covers PDOK\StatementWrapper::getAttribute
     */
    function testGetAttribute()
    {
        $this->statement->expects($this->once())->method('getAttribute')->with('a')->will($this->returnValue(9999));
        $this->assertEquals(9999, $this->wrapper->getAttribute('a'));
    }

    /**
     * @covers PDOK\StatementWrapper::getColumnMeta
     */
    function testGetColumnMeta()
    {
        $this->statement->expects($this->once())->method('getColumnMeta')->with('a')->will($this->returnValue(9999));
        $this->assertEquals(9999, $this->wrapper->getColumnMeta('a'));
    }

    /**
     * @covers PDOK\StatementWrapper::fetch
     */
    function testFetch()
    {
        $this->statement->expects($this->once())->method('fetch')->with('a', 'b', 'c')->will($this->returnValue(9999));
        $this->assertEquals(9999, $this->wrapper->fetch('a', 'b', 'c'));
    }

    /**
     * @covers PDOK\StatementWrapper::fetchObject
     */
    function testFetchObject()
    {
        $this->statement->expects($this->once())->method('fetchObject')->with('a', array('b'))->will($this->returnValue(9999));
        $this->assertEquals(9999, $this->wrapper->fetchObject('a', array('b')));
    }

    /**
     * @covers PDOK\StatementWrapper::fetchAll
     */
    function testFetchAll()
    {
        $this->statement->expects($this->once())->method('fetchAll')->with('a', 'b', array('c'))->will($this->returnValue(9999));
        $this->assertEquals(9999, $this->wrapper->fetchAll('a', 'b', array('c')));
    }

    /**
     * @covers PDOK\StatementWrapper::__get
     */
    function testQueryString()
    {
        $this->connector->exec("CREATE TABLE foo(id INT);");
        $this->setProtected($this->connector, 'useWrapper', true);
        $sql = "SELECT * FROM foo";
        $stmt = $this->connector->prepare($sql);
        $this->assertInstanceOf('PDOK\StatementWrapper', $stmt);
        $this->assertEquals($sql, $stmt->queryString);
    }

    /**
     * @covers PDOK\StatementWrapper::__get
     */
    function testInvalidPropertyGet()
    {
        $this->setProtected($this->connector, 'useWrapper', true);
        $stmt = $this->connector->prepare("SELECT 1");
        $this->setExpectedException('BadMethodCallException');
        $stmt->foobar;
    }
}
