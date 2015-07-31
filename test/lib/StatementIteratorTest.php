<?php
namespace PDOK\Test;

require_once __DIR__.'/StatementAcceptanceTestCase.php';

/**
 * @group statement
 * @group acceptance
 */
class StatementIteratorTest extends StatementAcceptanceTestCase
{
    function createConnector()
    {
        return new \PDOK\Connector('sqlite::memory:');
    }

    function assertStatement($statement)
    {
        $this->assertInstanceOf('PDOK\Statement', $statement);
    }

    function testEach()
    {
        $stmt = $this->connector->query("SELECT * FROM food LIMIT 2");
        $rows = iterator_to_array($stmt->each(\PDO::FETCH_ASSOC));
        $expected = [
            ['id' => 1, 'type' => 'junk', 'name' => 'chocolate'],
            ['id' => 2, 'type' => 'junk', 'name' => 'cake'],
        ];
        $this->assertEquals($expected, $rows);
    }

    function testEachAssoc()
    {
        $stmt = $this->connector->query("SELECT * FROM food LIMIT 2");
        $rows = iterator_to_array($stmt->eachAssoc());
        $expected = [
            ['id' => 1, 'type' => 'junk', 'name' => 'chocolate'],
            ['id' => 2, 'type' => 'junk', 'name' => 'cake'],
        ];
        $this->assertEquals($expected, $rows);
    }

    function testEachNum()
    {
        $stmt = $this->connector->query("SELECT * FROM food LIMIT 2");
        $rows = iterator_to_array($stmt->eachNum());
        $expected = [
            [1, 'junk', 'chocolate'],
            [2, 'junk', 'cake'],
        ];
        $this->assertEquals($expected, $rows);
    }

    function testEachColumn()
    {
        $stmt = $this->connector->query("SELECT * FROM food LIMIT 2");
        $rows = iterator_to_array($stmt->eachColumn(1));
        $expected = ['junk', 'junk'];
        $this->assertEquals($expected, $rows);
    }

    function testEachObject()
    {
        $stmt = $this->connector->query("SELECT * FROM food LIMIT 2");
        $rows = iterator_to_array($stmt->eachObject('stdClass'));
        $expected = [
            (object)['id' => 1, 'type' => 'junk', 'name' => 'chocolate'],
            (object)['id' => 2, 'type' => 'junk', 'name' => 'cake'],
        ];
        $this->assertEquals($expected, $rows);
    }
}
