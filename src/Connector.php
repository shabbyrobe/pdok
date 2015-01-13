<?php
namespace PDOK;

/**
 * Database connector.
 * 
 * When using a regular PDO, the connection is made immediately. This object is a 
 * stand-in for a PDO that defers connecting to the database until a connection 
 * is actually required.
 * 
 * The only change to PDO's default behaviour is that this class sets the error
 * mode to throw exceptions by default.
 * 
 * It also offers some enhancements - it will tell you when there is an active
 * transaction (unless you grab the internal PDO and start one directly)
 */
class Connector
{
    /**
     * Underlying database connection
     * Will be null if the Connector has not established a connection.
     * @var \PDO|null
     */
    public $pdo;
    
    /**
     * DSN for the database connection
     * @var string
     */
    public $dsn;
    
    /**
     * Database engine
     * @var string
     */
    public $engine;
    
    /**
     * Database username
     * @var string
     */
    public $username;
    
    /**
     * Database password
     * @var string
     */
    public $password;
    
    /**
     * Database driver options
     * @var array
     */
    public $driverOptions;
    
    /**
     * List of statements to run when the connection is established
     * This is mostly here to allow you to set the connection encoding.
     * @var array
     */
    public $connectionStatements;

    public $queries = 0;
    
    private $attributes=array();

    private $useWrapper = false;

    private static $attributeIndex = array(
        \PDO::ATTR_CASE => 'PDO::ATTR_CASE',
        \PDO::ATTR_ERRMODE => 'PDO::ATTR_ERRMODE',
        \PDO::ATTR_ORACLE_NULLS => 'PDO::ATTR_ORACLE_NULLS',
        \PDO::ATTR_STRINGIFY_FETCHES => 'PDO::ATTR_STRINGIFY_FETCHES',
        \PDO::ATTR_STATEMENT_CLASS => 'PDO::ATTR_STATEMENT_CLASS',
        \PDO::ATTR_TIMEOUT => 'PDO::ATTR_TIMEOUT',
        \PDO::ATTR_AUTOCOMMIT => 'PDO::ATTR_AUTOCOMMIT',
        \PDO::ATTR_EMULATE_PREPARES => 'PDO::ATTR_EMULATE_PREPARES',
        \PDO::ATTR_DEFAULT_FETCH_MODE => 'PDO::ATTR_DEFAULT_FETCH_MODE',
        \PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => 'PDO::MYSQL_ATTR_USE_BUFFERED_QUERY',
    );
    
    public function __clone()
    {
        $this->pdo = null;
        $this->queries = 0;
    }
    
    public function __construct($dsn, $username=null, $password=null, array $driverOptions=null, array $connectionStatements=null)
    {
        $this->dsn = $dsn;
        $this->engine = strtolower(substr($dsn, 0, strpos($dsn, ':')));
        $this->username = $username;
        $this->password = $password;
        $this->driverOptions = $driverOptions ?: array();
        $this->connectionStatements = $connectionStatements ?: array();
    }
    
    public function __destruct()
    {
        $this->pdo = null;
    }

    /**
     * @ignore
     */
    public function __sleep()
    {
        $this->pdo = null;
        $this->queries = 0;
        $keys = array_keys(get_object_vars($this));
        $keys[] = 'attributes';
        return $keys;
    }

    /**
     * Creates a Connector from an array of connection parameters.
     * @param array Parameters to use to create the connection
     * @return Amiss\Sql\Connector
     */
    public static function create(array $params)
    {
        $options = $host = $port = $database = $user = $password = $connectionStatements = null;
        
        foreach ($params as $k=>$v) {
            $k = strtolower($k);
            if (strpos($k, "host")===0 || $k == 'server' || $k == 'sys') {
                $host = $v;
            } elseif ($k[0] == 'p' && $k[1] == 'o') {
                $port = $v;
            } elseif ($k=="database" || $k == 'db' || strpos($k, "db")===0) {
                $database = $v;
            } elseif (($k[0] == 'p' && $k[1] == 'a')) {
                $password = $v;
            } elseif ($k[0] == 'u') {
                $user = $v;
            } elseif ($k=='options' || $k=='driveroptions') {
                $options = $v;
            } elseif ($k=='connectionstatements' || $k=='statements') {
                $connectionStatements = $v;
            }
        }
       
        if (!isset($params['dsn'])) {
            $dsn = (isset($params['engine']) ? $params['engine'] : 'mysql').":host={$host};";
            if ($port) {
                $dsn .= "port=".$port.';';
            }
            if (!empty($database)) {
                $dsn .= "dbname={$database};";
            }
        }
        else {
            $dsn = $params['dsn'];
        }
        if ($options) {
            $nOptions = array();
            foreach ($options as $k=>$v) {
                $nOptions[is_string($k) ? constant($k) : $k] = $v;
            }
            $options = $nOptions;
        }
        return new static($dsn, $user, $password, $options, $connectionStatements);
    }
    
    public function getPDO()
    {
        if ($this->pdo == null) {
            $this->pdo = $this->createPDO();
        }
        return $this->pdo;
    }
    
    public function createPDO()
    {
        if (isset($this->driverOptions[\PDO::ATTR_PERSISTENT]) && $this->driverOptions[\PDO::ATTR_PERSISTENT]) {
            $this->useWrapper = true;
        }
        elseif (isset($this->attributes[\PDO::ATTR_STATEMENT_CLASS])) {
            $this->useWrapper = true;
        }
        if (isset($this->attributes[\PDO::ATTR_ERRMODE]) && $this->attributes[\PDO::ATTR_ERRMODE] != \PDO::ERRMODE_EXCEPTION) {
            throw new \PDOException("Sorry, PDOK only supports ERRMODE_EXCEPTION"); 
        }

        if (!$this->useWrapper) {
            $this->attributes[\PDO::ATTR_STATEMENT_CLASS] = array(__NAMESPACE__.'\Statement', array($this));
        }

        $pdo = new \PDO($this->dsn, $this->username, $this->password, $this->driverOptions);
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        
        if ($this->attributes) {
            foreach ($this->attributes as $k=>$v) {
                $this->setPDOAttribute($pdo, $k, $v);
            }
        }
        foreach ($this->connectionStatements as $sql) {
            $pdo->exec($sql);
        }
        
        return $pdo;
    }
    
    public function isConnected()
    {
        return $this->pdo == true;
    }
    
    public function connect()
    {
        $this->getPDO();
        return $this;
    }
    
    /**
     * Allows the connector to be disconnected from the database
     * without nulling the connector object. This allows reconnection
     * later in the script.
     * 
     * This is an alternative to the standard PDO way of nulling all 
     * references to the PDO object, which also works with PDOConnector.
     * 
     * Regular PDO way (also works with Connector):
     *   $pdoConnector = null;
     * 
     * Using disconnect():
     *   $pdoConnector->query("SHOW PROCESSLIST");
     *   $pdoConnector->disconnect();
     *   $pdoConnector->query("SHOW PROCESSLIST");
     */
    public function disconnect()
    {
        $this->pdo = null;
        return $this;
    }
    
    private function setPDOAttribute($pdo, $attribute, $value)
    {
        if (!is_long($attribute)) {
            throw new \InvalidArgumentException("PDOK\Connector::setAttribute() expects parameter 1 to be long, ".gettype($attribute)." given");
        }
        if ($attribute == \PDO::ATTR_ERRMODE) {
            throw new \InvalidArgumentException("Cannot set PDO::ATTR_ERRMODE: PDOK requires it be set to ERRMODE_EXCEPTION");
        }
        $result = $pdo->setAttribute($attribute, $value);
        if ($result !== true) {
            $name = isset(self::$attributeIndex[$attribute]) ? self::$attributeIndex[$attribute] : "(unknown:$attribute)";
            throw new \PDOException("Attribute $name, value $value invalid for engine {$this->engine}");
        }
    }

    public function setAttribute($attribute, $value)
    {
        $this->attributes[$attribute] = $value;
        if ($this->pdo != null) {
            $this->setPDOAttribute($this->pdo, $attribute, $value);
        }
        return $this;
    }
    
    public function getAttribute($attribute)
    {
        if ($this->pdo == null) {
            return isset($this->attributes[$attribute]) ? $this->attributes[$attribute] : null;
        } else {
            $value = $this->pdo->getAttribute($attribute);
            $this->attributes[$attribute] = $value;
            return $value;
        }
    }
    
    /**
     * @see \PDO::beginTransaction
     */
    public function beginTransaction()
    {
        if (!$this->pdo) {
            $this->connect();
        }
        if (!$this->pdo->beginTransaction()) {
            throw new \PDOException("PDOK: beginTransaction() failed");
        }
        return $this;
    }
    
    /**
     * @see \PDO::commit
     */
    public function commit()
    {
        if (!$this->pdo) {
            $this->connect();
        }
        if (!$this->pdo->commit()) {
            throw new \PDOException("PDOK: commit() failed");
        }
        return $this;
    }
    
    public function rollBack()
    {
        if (!$this->pdo) {
            $this->connect();
        }
        if (!$this->pdo->rollBack()) {
            throw new \PDOException("PDOK: rollBack() failed");
        }
        return $this;
    }
    
    public function errorCode()
    {
        if ($this->pdo == null) {
            return null;
        }
        return $this->pdo->errorCode();
    }
    
    public function errorInfo()
    {
        if ($this->pdo == null) {
            return null;
        }
        return $this->pdo->errorInfo();
    }
    
    public function exec($sql, $params=null)
    {
        if (!$this->pdo) {
            $this->connect();
        }
        ++$this->queries;
        if (!$params) {
            return $this->pdo->exec($sql);
        }
        else {
            $stmt = $this->prepare($sql);
            $stmt->execute($params);
            return $stmt->rowCount();
        }
    }

    /**
     * PDO calls it 'exec'. PDOStatement calls it 'execute'. Crazy!
     */
    public function execute($sql, $params=null)
    {
        return $this->exec($sql, $params);
    }

    public function execAll($statements, $transaction=null)
    {
        $transaction = $transaction === null ? true : $transaction == true;
        if (!$statements) {
            throw new \InvalidArgumentException();
        }
        if (!$this->pdo) {
            $this->connect();
        }

        $out = array();
        if ($transaction) {
            $this->beginTransaction();
        }
        foreach ($statements as $k=>$statement) {
            ++$this->queries;
            $out[$k] = $this->pdo->exec($statement);
        }
        if ($transaction) {
            $this->commit();
        }
        return $out;
    }

    public function executeAll($statements, $transaction=null)
    {
        return $this->execAll($statements, $transaction);
    }
    
    public function lastInsertId()
    {
        if (!$this->isConnected()) {
            throw new \PDOException("PDOK: Not connected");
        }
        return $this->pdo->lastInsertId();
    }
    
    public function prepare($sql, array $driverOptions=array())
    {
        if (!$this->pdo) {
            $this->connect();
        }
        $stmt = $this->pdo->prepare($sql, $driverOptions);
        if ($stmt instanceof \PDOStatement) {
            return $this->useWrapper ? new StatementWrapper($this, $stmt) : $stmt;
        } else {
            return $stmt;
        }
    }
    
    public function query()
    {
        if (!$this->pdo) {
            $this->connect();
        }
        $args = func_get_args();
    
        $stmt = call_user_func_array(array($this->pdo, 'query'), $args);
        if ($stmt instanceof \PDOStatement) {
            ++$this->queries;
            return $this->useWrapper ? new StatementWrapper($this, $stmt) : $stmt;
        } else {
            return $stmt;
        }
    }
    
    public function quote($string, $parameterType=null)
    {
        if (!$this->pdo) {
            $this->connect();
        }
        return $this->pdo->quote($string, $parameterType);
    }

	public function quoteIdentifier($name)
	{
        $name = filter_var($name, FILTER_UNSAFE_RAW, FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH);
        if (!$name) {
            throw new \InvalidArgumentException("No valid characters in identifier");
        }

        switch ($this->engine) {
        case 'mysql': case 'sqlite':
            return '`'.str_replace('`', '', $name).'`';

        case 'pgsql': case 'oci':
            return '"'.str_replace('"', '', $name).'"';

        case 'mssql':
            return '['.strtr($name, array('['=>'', ']'=>'')).']';

        default:
            throw new \PDOException("Unsupported engine {$this->engine}");
        }
	}

    public function inTransaction()
    {
        if (!$this->pdo) {
            return false;
        }
        return $this->pdo->inTransaction();
    }
}
