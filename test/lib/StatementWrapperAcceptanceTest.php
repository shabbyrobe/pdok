<?php
namespace PDOK\Test;

require_once __DIR__.'/StatementAcceptanceTestCase.php';

/**
 * @group statement
 * @group acceptance
 */
class StatementWrapperAcceptanceTest extends StatementAcceptanceTestCase
{
    function createConnector()
    {
        $connector = new \PDOK\Connector('sqlite::memory:');
        $this->setProtected($connector, 'useWrapper', true);
        return $connector;
    }

    function assertStatement($statement)
    {
        $this->assertInstanceOf('PDOK\StatementWrapper', $statement);
    }
}
