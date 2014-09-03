<?php
/**
 * @file
 * DBA.
 *
 * It contains the definition to:
 * @code
abstract class DBA;
@endcode
 *
 * @author 1m.dm
 * @author dr.Pavka
 * @copyright Energine 2006
 *
 * @version 1.0.0
 */
namespace Energine\share\gears;

/**
 * Database Abstraction Layer.
 *
 * @code
abstract class DBA;
@endcode
 *
 * @abstract
 */
abstract class DBA extends Object {
    /**
     * Instance of PDO class (PHP Data Objects).
     * @var \PDO $pdo
     */
    protected $pdo;

    /**
     * Last query to the data base.
     * @var string $lastQuery
     */
    protected $lastQuery;

    /**
     * Data base cache.
     * @var DBStructureInfo $dbCache
     */
    private $dbCache;

    //Типы полей таблиц БД:
    /**
     * Column type: @c INTEGER
     * @var string COLTYPE_INTEGER
     */
    const COLTYPE_INTEGER = 'INT';

    /**
     * Column type: @c FLOAT
     * @var string COLTYPE_FLOAT
     */
    const COLTYPE_FLOAT = 'FLOAT';

    /**
     * Column type: @c DATE
     * @var string COLTYPE_DATE
     */
    const COLTYPE_DATE = 'DATE';

    /**
     * Column type: @c TIME
     * @var string COLTYPE_TIME
     */
    const COLTYPE_TIME = 'TIME';

    /**
     * Column type: @c TIMESTAMP
     * @var string COLTYPE_TIMESTAMP
     */
    const COLTYPE_TIMESTAMP = 'TIMESTAMP';

    /**
     * Column type: @c DATETIME
     * @var string COLTYPE_DATETIME
     */
    const COLTYPE_DATETIME = 'DATETIME';

    /**
     * Column type: @c VARCHAR
     * @var string COLTYPE_STRING
     */
    const COLTYPE_STRING = 'VARCHAR';

    //Типы строк только для внутреннего использования. Без комментариев :)
    //const COLTYPE_STRING1 = 'STRING';
    //const COLTYPE_STRING2 = 'VAR_STRING';

    /**
     * Column type: @c TEXT
     * @var string COLTYPE_TEXT
     */
    const COLTYPE_TEXT = 'TEXT';

    /**
     * Column type: @c BLOB
     * Binary data.
     *
     * @var string COLTYPE_BLOB
     */
    const COLTYPE_BLOB = 'BLOB';

    /**
     * Error type of the column.
     * @var string ERR_BAD_REQUEST
     */
    const ERR_BAD_REQUEST = 'ERR_DATABASE_ERROR';

    /**
     * Primary index
     * @var string PRIMARY_INDEX
     */
    const PRIMARY_INDEX = 'PRI';

    /**
     * Unique index.
     * @var string UNIQUE_INDEX
     */
    const UNIQUE_INDEX = 'UNI';

    /**
     * Index.
     * @var string INDEX
     */
    const INDEX = 'MUL';

    /**
     * @param string $dsn Data Source Name; for connecting to the data base.
     * @param string $username User name.
     * @param string $password Password.
     * @param array $driverOptions Specific DB driver parameters.
     * @param string $charset Encoding.
     *
     * @throws SystemException Unable to connect. The site is temporarily unavailable.
     */
    public function __construct($dsn, $username, $password, array $driverOptions, $charset = 'utf8') {
        try {
            $this->pdo = new \PDO($dsn, $username, $password, $driverOptions);
            $this->pdo->query('SET NAMES ' . $charset);

            $this->dbCache = new DBStructureInfo($this->pdo);

        } catch (\PDOException $e) {
            throw new SystemException('Unable to connect. The site is temporarily unavailable.', SystemException::ERR_DB, 'The site is temporarily unavailable');
        }

    }

    /**
     * Get @link DBA::$pdo \PDO@endlink.
     *
     * Use this for direct work with DB.
     *
     * @return \PDO
     */
    public function getPDO(){
        return $this->pdo;
    }

    //todo VZ: What is the alternative?
    //todo VZ: I think it will be better to throw some value instead of returning false or true.
    /**
     * Execute SELECT request.
     *
     * It returns one from the following:
     *  - an array for non-empty result like
     * @code
array(
    rowID => array(
                   fieldName => fieldValue,
                   ...
                  )
)
@endcode
     *  - @c true for empty result;
     *  - @c false by fail.
     *
     * @param string $query SELECT query.
     * @return mixed
     *
     * @throws SystemException
     *
     * @see DBA::constructQuery
     *
     * @note If the total amount of arguments is more than 1, then this function process the input arguments like @c printf function.
     *
     * @deprecated
     */
    public function selectRequest($query) {
        $res = call_user_func_array(array($this, 'fulfill'), func_get_args());

        if (!($res instanceof \PDOStatement)) {
            $errorInfo = $this->pdo->errorInfo();
            throw new SystemException($errorInfo[2], SystemException::ERR_DB, array($this->getLastRequest()));
        }

        $result = array();
        while ($row = $res->fetch(\PDO::FETCH_ASSOC)) {
            array_push($result, $row);
        }
        if (empty($result)) {
            $result = true;
        }

        return $result;
    }

    /**
     * Execute modification request like INSERT, UPDATE, DELETE.
     *
     *
     * It returns one from the following:
     * - last generated ID for field type AUTO_INCREMENT;
     * - @c true by success;
     * - @c false by fail.
     *
     * @param string $query Query.
     * @return mixed
     *
     * @note If the total amount of arguments is more than 1, then this function process the input arguments like @c printf function.
     *
     * @see DBA::constructQuery
     *
     * @throws SystemException
     */
    public function modifyRequest($query) {
        $res = call_user_func_array(array($this, 'fulfill'), func_get_args());

        if (!($res instanceof \PDOStatement)) {
            $errorInfo = $this->pdo->errorInfo();
            throw new SystemException($errorInfo[2], SystemException::ERR_DB, array($this->getLastRequest(),));
        }
        $result = intval($this->pdo->lastInsertId());

        if ($result == 0) {
            $result = true;
        }

        return $result;
    }

    /**
     * Call procedure.
     *
     * @param  string $name Procedure name.
     * @param  array $args Procedure arguments.
     * @return array|bool
     */
    public function call($name, &$args = null) {
        if (!$args) {
            $res = $this->pdo->query("call $name();", \PDO::FETCH_NAMED);
        } else {
            $argString = implode(',', array_fill(0, count($args), '?'));
            $stmt = $this->pdo->prepare("CALL $name($argString)");
            foreach ($args as $index => &$value) {
                $stmt->bindParam($index + 1, $value);
            }
            if ($res = $stmt->execute()) {
                $res = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            }
        }
        return $res;
    }

    /**
     * Get data for further iteration.
     *
     * @param string $query SQL request.
     * @return bool|\PDOStatement
     */
    public function get($query) {
        $res = call_user_func_array(array($this, 'fulfill'), func_get_args());
        if ($res instanceof \PDOStatement) {
            return $res;
        }
        return false;
    }

    /**
     * Execute the request.
     *
     * @param string $request Request.
     * @return bool|\PDOStatement
     */
    protected function fulfill($request) {
        if (!is_string($request) || empty($request)) {
            return false;
        }
        if ($this->getConfigValue('database.prepare')) {
            $res = $this->runQuery(func_get_args());
            if ($res instanceof \PDOStatement)
                $this->lastQuery = $res->queryString;
        } else {
            $request = $this->constructQuery(func_get_args());
            $res = $this->pdo->query($request);
            $this->lastQuery = $request;
        }
        return $res;
    }

    /**
     * Process string.
     *
     * Place, if needed, double quotes around the input string and isolates special symbols inside the string.
     *
     * @param string $string Some string.
     * @return string
     */
    public function quote($string) {
        return $this->pdo->quote($string);
    }

    /**
     * Get the @link DBA::$lastQuery last query@endlink.
     *
     * @return string
     */
    public function getLastRequest() {
        return $this->lastQuery;
    }

    /**
     * Get last error message.
     *
     * @return string
     */
    public function getLastError() {
        return $this->pdo->errorInfo();
    }

    /**
     * Begin an transaction.
     *
     * @return boolean
     */
    public function beginTransaction() {
        return $this->pdo->beginTransaction();
    }

    /**
     * Execute @c commit transaction.
     *
     * @return boolean
     */
    public function commit() {
        return $this->pdo->commit();
    }

    /**
     * Open transaction.
     *
     * @return boolean
     */
    public function rollback() {
        return $this->pdo->rollBack();
    }

    /**
     * Get columns info of the table.
     *
     * The returned array looks like:
     * @code
array(
    'columnName' => array(
        'type'      => column type,
        'length'    => length,
        'nullable'  => accept NULL?,
        'key'       => description of the columns key (if exist),
        'default'   => default value,
        'index'     => index type
    )
)
@endcode
     *
     * @param string $tableName Table name.
     * @return array
     */
    public function getColumnsInfo($tableName) {
        $result = $this->dbCache->getTableMeta($tableName);
        return $result;
    }


    /**
     * Get the table name with translations.
     *
     * Get the table name with translations for some table name, if such exist.
     * Otherwise false will be returned.
     *
     * @param string $tableName
     * @return string|bool
     */
    public function getTranslationTablename($tableName) {
        return $this->tableExists($tableName . '_translation');
    }

    /**
     * Check whether some table name exist.
     *
     * @param $tableName string Table name.
     * @return string|bool
     */
    public function tableExists($tableName) {
        return ($this->dbCache->tableExists($tableName)) ? $tableName : false;
    }

    /**
     * Check whether some procedure exist.
     *
     * @param string $procName Procedure name.
     * @return boolean
     */
    public function procExists($procName) {
        return ($this->getScalar(
            'SELECT ROUTINE_NAME
            FROM information_schema.ROUTINES
            WHERE
            ROUTINE_TYPE="PROCEDURE"
            AND ROUTINE_SCHEMA=%s
            AND ROUTINE_NAME=%s',
            E()->getConfigValue('database.db'),
            $procName
        )) ? true : false;
    }

    /**
     * Check whether some function exist.
     *
     * @param string $funcName Function name.
     * @return boolean
     */
    public function funcExists($funcName) {
        return ($this->getScalar(
            'SELECT ROUTINE_NAME
            FROM information_schema.ROUTINES
            WHERE
            ROUTINE_TYPE="FUNCTION"
            AND ROUTINE_SCHEMA=%s
            AND ROUTINE_NAME=%s',
            E()->getConfigValue('database.db'),
            $funcName
        )) ? true : false;
    }

    /**
     * Get the fully qualified table name in MySQL quotes.
     *
     * @param string $tableName Table name.
     * @param bool $returnAsArray Return as array?
     * @return string | array
     */
    public static function getFQTableName($tableName, $returnAsArray = false) {
        $result = array();

        $tableName = str_replace('`', '', $tableName);

        if ($pos = strpos($tableName, '.')) {
            array_push($result, substr($tableName, 0, $pos));
            $tableName = substr($tableName, $pos + 1);
        }
        array_push($result, $tableName);
        return (!$returnAsArray) ? implode('.', array_map(function ($row) {
            return '`' . $row . '`';
        }, $result)) : $result;
    }

    /**
     * Construct query.
     *
     * If the number of the arguments is > 1, then this method behaves like printf() function.
     *
     * @param array $args Array from which the single query string will be built.
     * @return string
     *
     * @deprecated
     *
     * @see DBA::selectRequest()
     * @see DBA::modifyRequest()
     */
    protected function constructQuery(array $args) {
        if (sizeof($args) > 1) {
            $query = array_shift($args); // отбрасываем первый аргумент $query
            foreach ($args as &$arg) {
                if(!is_null($arg))
                    $arg = $this->pdo->quote($arg);
                else {
                    $arg = 'NULL';
                }
            }
            array_unshift($args, $query);
            $query = call_user_func_array('sprintf', $args);
        } else {
            $query = $args[0];
        }
        return $query;
    }

    /**
     * Run query.
     *
     * @param array $args Query arguments. First argument is SQL string
     * @return \PDOStatement
     *
     * @throws SystemException 'ERR_BAD_REQUEST'
     */
    protected function runQuery(array $args) {
        if (empty($args)) {
            throw new SystemException('ERR_BAD_REQUEST');
        }
        $query = array_shift($args);
        $query = str_replace('%%', '%', $query);
        if (!empty($args)) {
            if (preg_match_all('(%(?:(\d)\$)?s)', $query, $matches)) {
                $query = preg_replace('(%(?:(\d)\$)?s)', '?', $query);
                $argIndex = 0;
                foreach ($matches[1] as $a) {
                    if ($a = (int)$a) {
                        $data[] = $args[$a - 1];
                    } else {
                        $data[] = $args[$argIndex++];
                    }
                }
            } else {
                $data = $args;
            }

            if (!($result = $this->pdo->prepare($query))) {
                throw new SystemException('ERR_PREPARE_REQUEST', SystemException::ERR_DB, $query);
            }
            if (!$result->execute($data)) {
                throw new SystemException('ERR_EXECUTE_REQUEST', SystemException::ERR_DB, array($query, $data));
            }

        } else {
            $result = $this->pdo->query($query);
        }
        return $result;
    }
}
