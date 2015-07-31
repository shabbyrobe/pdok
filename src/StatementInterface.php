<?php
namespace PDOK;

interface StatementInterface
{
/*{{{ PDOStatement */
	function bindColumn(
        $column, &$param, $type=\PDO::PARAM_STR, $maxlen=null, $driverOptions=null
    );

	function bindParam(
        $parameter, &$variable, $type=\PDO::PARAM_STR, $length=null, $driverOptions=null
    );

	function bindValue($parameter, $value, $type=\PDO::PARAM_STR);

	function closeCursor();

	function columnCount();

	function debugDumpParams();

	function errorCode();

	function errorInfo();

	function execute($inputParameters=null);

	function fetch($fetchStyle=null, $cursorOrientation=\PDO::FETCH_ORI_NEXT, $cursorOffset=0);

	function fetchAll($fetchStyle=null, $fetchArgument=null, $ctorArgs=array());

	function fetchColumn($columnNumber=0);

	function fetchObject($className="stdClass", $ctorArgs);

	function getAttribute($attribute);

	function getColumnMeta($column);

    function getQueryString();

	function nextRowset();

	function rowCount();

	function setAttribute($attribute, $value);

    /**
     * @return PDOK\StatementInterface
     */
	function setFetchMode($mode, $params=null);
/*}}}*/

/*{{{ Provided by StatementTrait */
	function each($fetchStyle=null, $cursorOrientation=\PDO::FETCH_ORI_NEXT, $cursorOffset=0);

	function eachAssoc($cursorOrientation=\PDO::FETCH_ORI_NEXT, $cursorOffset=0);

	function eachNum($cursorOrientation=\PDO::FETCH_ORI_NEXT, $cursorOffset=0);

	function eachColumn($columnNumber=0);

	function eachObject($className="stdClass", array $ctorArgs=[]);

	function exec($inputParameters=null);

	function fetchAssoc($cursorOrientation=\PDO::FETCH_ORI_NEXT, $cursorOffset=0);

	function fetchNum($cursorOrientation=\PDO::FETCH_ORI_NEXT, $cursorOffset=0);
/*}}}*/
}

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
