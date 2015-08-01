<?php
namespace PDOK;

class Statement extends \PDOStatement implements StatementInterface
{
    use StatementTrait;

    private $connector;

    protected function __construct($connector)
    {
        $this->connector = $connector;
    }

    function getQueryString() { return $this->queryString; }

    function bindColumn($column, &$param, $type=\PDO::PARAM_STR, $maxlen=null, $driverOptions=null) 
    {
        return ($ret = parent::bindColumn($column, $param, $type, $maxlen, $driverOptions)) ? $this : $ret;
    }

    function bindParam($parameter, &$variable, $type=\PDO::PARAM_STR, $length=null, $driverOptions=null) 
    {
        return ($ret = parent::bindParam($parameter, $variable, $type, $length, $driverOptions)) ? $this : $ret;
    }

    function bindValue($parameter, $value, $type=\PDO::PARAM_STR) 
    {
        return ($ret = parent::bindValue($parameter, $value, $type)) ? $this : $ret;
    }

    function closeCursor() 
    {
        return ($ret = parent::closeCursor()) ? $this : $ret;
    }

    function execute($inputParameters=null) 
    {
        ++$this->connector->queries;
        if ($ret = parent::execute($inputParameters)) {
            return $this;
        } else {
            throw new \PDOException("Statement execution failed with falsey return value ".gettype($ret));
        }
    }

    /**
     * It's called exec on PDO, but execute on PDOStatement. Go figure.
     */
    function exec($inputParameters=null) 
    {
        return $this->execute($inputParameters);
    }

    function nextRowset() 
    {
        return ($ret = parent::nextRowset()) ? $this : $ret;
    }

    function setAttribute($attribute, $value) 
    {
        return ($ret = parent::setAttribute($attribute, $value)) ? $this : $ret;
    }

    function setFetchMode($mode, $params=null) 
    {
        // setFetchMode is sensitive to the number of parameters it receives
        $ret = call_user_func_array(array('parent', 'setFetchMode'), func_get_args());
        return $ret ? $this : $ret;
    }
}
