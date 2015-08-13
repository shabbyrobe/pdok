<?php
namespace PDOK\Test;

require_once __DIR__.'/StatementAcceptanceTestCase.php';

/**
 * @group statement
 * @group acceptance
 * @group faulty
 */
class StatementAcceptanceTest extends StatementAcceptanceTestCase
{
    function createConnector()
    {
        $connector = new \PDOK\Connector('sqlite::memory:');
        return $connector;
    }

    function assertStatement($statement)
    {
        $this->assertInstanceOf('PDOK\Statement', $statement);
    }
}
