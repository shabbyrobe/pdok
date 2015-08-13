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
        // this crap is due to the PHP bug described in Connector->useWrapper
        $prop = (new \ReflectionClass('PDOK\Connector'))->getProperty('useWrapper');
        $prop->setAccessible(true);
        $expected = $prop->getValue($this->createConnector()) ? 'PDOK\StatementWrapper' : 'PDOK\Statemt';

        $this->assertInstanceOf($expected, $statement);
    }
}
