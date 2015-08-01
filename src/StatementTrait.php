<?php
namespace PDOK;

trait StatementTrait
{
    function __get($name)
    {
        if ($name == 'queryString') {
            return $this->getQueryString();
        } else {
            throw new \BadMethodCallException();
        }
    }

    function __isset($name)
    {
        $this->__get($name);
        return true;
    }

    function exec($inputParameters=null) 
    {
        return $this->execute($inputParameters);
    }

    function fetchAssoc($cursorOrientation=\PDO::FETCH_ORI_NEXT, $cursorOffset=0)
    {
        return $this->fetch(\PDO::FETCH_ASSOC, $cursorOrientation, $cursorOffset);
    }

    function fetchNum($cursorOrientation=\PDO::FETCH_ORI_NEXT, $cursorOffset=0)
    {
        return $this->fetch(\PDO::FETCH_NUM, $cursorOrientation, $cursorOffset);
    }

    function each($fetchStyle=null, $cursorOrientation=\PDO::FETCH_ORI_NEXT, $cursorOffset=0)
    {
        return new StatementIterator($this, 'fetch', [$fetchStyle, $cursorOrientation, $cursorOffset]);
    }

    function eachAssoc($cursorOrientation=\PDO::FETCH_ORI_NEXT, $cursorOffset=0)
    {
        return new StatementIterator($this, 'fetch', [\PDO::FETCH_ASSOC, $cursorOrientation, $cursorOffset]);
    }

    function eachNum($cursorOrientation=\PDO::FETCH_ORI_NEXT, $cursorOffset=0)
    {
        return new StatementIterator($this, 'fetch', [\PDO::FETCH_NUM, $cursorOrientation, $cursorOffset]);
    }

    function eachColumn($columnNumber=0)
    {
        return new StatementIterator($this, 'fetchColumn', [$columnNumber]);
    }

    function eachObject($className="stdClass", array $ctorArgs=[])
    {
        return new StatementIterator($this, 'fetchObject', [$className, $ctorArgs]);
    }
}
