<?php
namespace PDOK;

class StatementWrapper implements StatementInterface
{
    use StatementTrait;

	public $statement;
    private $connector;

	public function __construct($connector, \PDOStatement $statement)
	{
        $this->connector = $connector;
		$this->statement = $statement;
	}

	function bindColumn($column, &$param, $type=\PDO::PARAM_STR, $maxlen=null, $driverOptions=null) 
	{
        return ($ret = $this->statement->bindColumn($column, $param, $type, $maxlen, $driverOptions)) ? $this : $ret;
	}

	function bindParam($parameter, &$variable, $type=\PDO::PARAM_STR, $length=null, $driverOptions=null) 
	{
        return ($ret = $this->statement->bindParam($parameter, $variable, $type, $length, $driverOptions)) ? $this : $ret;
	}

	function bindValue($parameter, $value, $type=\PDO::PARAM_STR) 
	{
        return ($ret = $this->statement->bindValue($parameter, $value, $type)) ? $this : $ret;
	}

	function closeCursor() 
	{
        return ($ret = $this->statement->closeCursor()) ? $this : $ret;
	}

	function columnCount() 
	{
        return $this->statement->columnCount();
	}

	function debugDumpParams() 
	{
        return $this->statement->debugDumpParams();
	}

	function errorCode() 
	{
        return $this->statement->errorCode();
	}

	function errorInfo() 
	{
        return $this->statement->errorInfo();
	}

	function execute($inputParameters=null) 
	{
        ++$this->connector->queries;
        return ($ret = $this->statement->execute($inputParameters)) ? $this : $ret;
	}

	function fetch($fetchStyle=null, $cursorOrientation=\PDO::FETCH_ORI_NEXT, $cursorOffset=0) 
	{
        return $this->statement->fetch($fetchStyle, $cursorOrientation, $cursorOffset);
	}

	function fetchAll($fetchStyle=null, $fetchArgument=null, $ctorArgs=array()) 
	{
        // PDOStatement is sensitive to the number of arguments passed. If you try to do
        // a straight proxy, you get "General error: Extraneous additional parameters"
        return call_user_func_array(array($this->statement, 'fetchAll'), func_get_args());
	}

	function fetchColumn($columnNumber=0) 
	{
        return $this->statement->fetchColumn($columnNumber);
	}

	function fetchObject($className="stdClass", $ctorArgs) 
	{
        return $this->statement->fetchObject($className, $ctorArgs);
	}

	function getAttribute($attribute) 
	{
        return $this->statement->getAttribute($attribute);
	}

	function getColumnMeta($column) 
	{
        return $this->statement->getColumnMeta($column);
	}

    function getQueryString()
    {
        return $this->statement->queryString;
    }

	function nextRowset() 
	{
        return ($ret = $this->statement->nextRowset()) ? $this : $ret;
	}

	function rowCount() 
	{
        return $this->statement->rowCount();
	}

	function setAttribute($attribute, $value) 
	{
        return ($ret = $this->statement->setAttribute($attribute, $value)) ? $this : $ret;
	}

	function setFetchMode($mode, $params=null) 
	{
        // setFetchMode is sensitive to the number of parameters it receives
        $ret = call_user_func_array(array($this->statement, 'setFetchMode'), func_get_args());
        return $ret ? $this : $ret;
	}
}
