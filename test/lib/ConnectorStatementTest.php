<?php
namespace PDOK\Test;

use PDOK\Connector;

class ConnectorStatementTest extends \CustomTestCase
{
    /**
     * @covers PDOK\Connector::query()
     * @covers PDOK\Connector::prepare()
     */
    function testStatementReturn()
    {
        $connector = new \PDOK\Connector('sqlite::memory:');
        $connector->exec("CREATE TABLE foo(id INTEGER);");

        $stmt = $connector->prepare('SELECT * FROM foo');
        $this->assertInstanceOf('PDOK\Statement', $stmt);

        $stmt = $connector->query('SELECT * FROM foo');
        $this->assertInstanceOf('PDOK\Statement', $stmt);
    }

    /**
     * @covers PDOK\Connector::query()
     * @covers PDOK\Connector::prepare()
     */
    function testCustomStatementReturn()
    {
        $statementClass = __NAMESPACE__.'\ConnectorStatementTestStatement';
        $connector = new \PDOK\Connector('sqlite::memory:');
        $connector->setAttribute(\PDO::ATTR_STATEMENT_CLASS, array($statementClass));
        $connector->exec("CREATE TABLE foo(id INTEGER);");

        $stmt = $connector->prepare('SELECT * FROM foo');
        $this->assertInstanceOf('PDOK\StatementWrapper', $stmt);
        $this->assertInstanceOf($statementClass, $stmt->statement);

        $stmt = $connector->query('SELECT * FROM foo');
        $this->assertInstanceOf('PDOK\StatementWrapper', $stmt);
        $this->assertInstanceOf($statementClass, $stmt->statement);
    }
}

class ConnectorStatementTestStatement extends \PDOStatement
{
}
