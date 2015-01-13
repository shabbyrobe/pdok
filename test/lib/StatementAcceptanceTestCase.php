<?php
namespace PDOK\Test;

abstract class StatementAcceptanceTestCase extends \CustomTestCase
{
    function setUp()
    {
        $this->connector = $this->createConnector();

        $this->connector->execute("CREATE TABLE food(
            id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
            type STRING,
            name STRING
        )");
        $this->connector->execute("INSERT INTO food(name, type) VALUES('chocolate', 'junk');");
        $this->connector->execute("INSERT INTO food(name, type) VALUES('cake', 'junk');");
        $this->connector->execute("INSERT INTO food(name, type) VALUES('celery', 'boring');");
    }

    abstract function assertStatement($statement);
    abstract function createConnector();

    function testExecuteSelectNoParams()
    {
        $stmt = $this->connector->query("SELECT COUNT(*) FROM food");
        $this->assertStatement($stmt);
        $this->assertStatement($stmt->execute());
        $this->assertEquals(3, $stmt->fetchColumn(0));
    }

    function testExecCallsExecute()
    {
        $stmt = $this->connector->prepare("SELECT COUNT(*) FROM food WHERE type=? AND id<=?");
        $this->assertStatement($stmt);
        $this->assertStatement($stmt->exec(array('junk', 2)));
        $this->assertEquals(2, $stmt->fetchColumn(0));
    }

    function testExecuteSelectPositionalParams()
    {
        $stmt = $this->connector->prepare("SELECT COUNT(*) FROM food WHERE type=? AND id<=?");
        $this->assertStatement($stmt);
        $this->assertStatement($stmt->execute(array('junk', 2)));
        $this->assertEquals(2, $stmt->fetchColumn(0));
    }

    function testExecuteSelectNamedParams()
    {
        $stmt = $this->connector->prepare("SELECT COUNT(*) FROM food WHERE type=:type AND id<=:id");
        $this->assertStatement($stmt);
        $this->assertStatement($stmt->execute(array(':type'=>'junk', ':id'=>2)));
        $this->assertEquals(2, $stmt->fetchColumn(0));
    }

    function testExecuteSelectBoundParams()
    {
        $stmt = $this->connector->prepare("SELECT COUNT(*) FROM food WHERE type=:type AND id<=:id");
        $this->assertStatement($stmt);
        $type = 'junk';
        $id = 2;
        $this->assertStatement(
            $stmt->bindParam(':type', $type)
                ->bindParam(':id', $id)
                ->execute()
        );
        $this->assertEquals(2, $stmt->fetchColumn(0));
    }

    function testExecuteSelectBoundValues()
    {
        $stmt = $this->connector->prepare("SELECT COUNT(*) FROM food WHERE type=:type AND id<=:id");
        $this->assertStatement(
            $stmt->bindValue(':type', 'junk')
                ->bindValue(':id', 2)
                ->execute()
        );
        $this->assertEquals(2, $stmt->fetchColumn(0));
    }

    function testExecuteSelectBoundColumn()
    {
        $stmt = $this->connector->prepare("SELECT * FROM food LIMIT 2");
        $this->assertStatement(
            $stmt->bindColumn('name', $name)
                ->bindColumn('type', $type)
                ->execute()
        );
        $stmt->fetch();
        $this->assertEquals('chocolate', $name);
    }

    function testSetFetchMode()
    {
        $stmt = $this->connector->prepare("SELECT * FROM food LIMIT 2");
        $this->assertStatement($stmt->setFetchMode(\PDO::FETCH_ASSOC)->execute());
        $row = $stmt->fetch();
        $this->assertEquals(array('id', 'type', 'name'), array_keys($row));
    }
}
