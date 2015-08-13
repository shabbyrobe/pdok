<?php
namespace PDOK\Test;

class DestructTest extends \CustomTestCase
{
    function setUp()
    {
        DestructConnector::$ref = 0;
    }

    function testDestructDisconnected()
    {
        $f = function() {
            $pdok = new DestructConnector('sqlite::memory:');
        };
        $f();
        $this->assertEquals(0, DestructConnector::$ref);
    }

    function testDestructConnected()
    {
        $f = function() {
            $pdok = new DestructConnector('sqlite::memory:');
            $pdok->connect();
        };
        $f();
        $f();
        $this->assertEquals(0, DestructConnector::$ref);
    }
}

class DestructConnector extends \PDOK\Connector
{
    static $ref = 0;

    function __construct()
    {
        ++static::$ref;
        call_user_func_array(['parent', '__construct'], func_get_args());
    }

    function __destruct()
    {
        --static::$ref;
        parent::__destruct();
    }
}
