<?php
namespace PDOK;

/**
 * If you implement this, you will probably want to include
 * ``use PDOK\StatementTrait``
 *
 * You also probably want to implement Iterator or IteratorAggregate
 */
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

    function fetchAll($fetchStyle=null, $fetchArgument=null, $ctorArgs=null);

    function fetchColumn($columnNumber=0);

    function fetchObject($className="stdClass", $ctorArgs=null);

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

    function eachObject($className="stdClass", array $ctorArgs=null);

    function exec($inputParameters=null);

    function fetchAssoc($cursorOrientation=\PDO::FETCH_ORI_NEXT, $cursorOffset=0);

    function fetchNum($cursorOrientation=\PDO::FETCH_ORI_NEXT, $cursorOffset=0);
/*}}}*/
}
