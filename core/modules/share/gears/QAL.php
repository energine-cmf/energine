<?php
/**
 * @file
 * QAL.
 *
 * It contains the definition to:
 * @code
final class QAL;
 * @endcode
 *
 * @author 1m.dm
 * @copyright Energine 2006
 *
 * @version 1.0.0
 */

namespace Energine\share\gears;

/**
 * Query Abstraction Layer + Database Abstraction Layer
 *
 * @code
final class QAL;
 * @endcode
 *
 * @final
 */
final class QAL extends Primitive {
    /**
     * Instance of PDO class (PHP Data Objects).
     * @var \PDO $pdo
     */
    private $pdo;

    /**
     * Last query to the data base.
     * @var string $lastQuery
     */
    private $lastQuery;

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
     * Column type: @c DECIMAL/NUMERIC
     * @var string COLTYPE_DECIMAL
     */
    const COLTYPE_DECIMAL = 'DECIMAL';

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
     * Column type: @c SET
     * SET
     *
     * @var string COLTYPE_SET
     */
    const COLTYPE_SET = 'SET';

    /**
     * Column type: @c ENUM
     * ENUM
     *
     * @var string COLTYPE_ENUM
     */
    const COLTYPE_ENUM = 'ENUM';

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

    //Режимы модифицирующих операций
    /**
     * INSERT operation.
     * @var string INSERT
     */
    const INSERT = 'INSERT';
    /**
     * INSERT_IGNORE operation.
     * @var string INSERT_IGNORE
     */
    const INSERT_IGNORE = 'INSERT IGNORE';
    /**
     * UPDATE operation.
     * @var string UPDATE
     */
    const UPDATE = 'UPDATE';
    /**
     * DELETE operation.
     * @var string DELETE
     */
    const DELETE = 'DELETE';
    /**
     * REPLACE operation.
     * @var string REPLACE
     */
    const REPLACE = 'REPLACE';

    /**
     * SELECT operation.
     * @var string SELECT
     */
    const SELECT = 'SELECT';
    /** modBySD
     * COPY operation in one table.
     * @var string COPY     
     */    
    const COPY = 'COPY';
    /**
     * Ascending order.
     * @var string ASC
     */
    const ASC = 'ASC';
    /**
     * Descending order.
     * @var string DESC
     */
    const DESC = 'DESC';

    /**
     * Empty string.
     * @var string EMPTY_STRING
     */
    const EMPTY_STRING = NULL;

    /**
     * Errors.
     * @var string ERR_BAD_QUERY_FORMAT
     */
    const ERR_BAD_QUERY_FORMAT = 'Bad query format.';

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
            $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

            $this->dbCache = new DBStructureInfo($this->pdo);

        } catch (\PDOException $e) {
            throw new SystemException('Unable to connect. The site is temporarily unavailable.', SystemException::ERR_DB, 'The site is temporarily unavailable');
        }
    }

    /**
     * Get @link QAL::$pdo \PDO@endlink.
     *
     * Use this for direct work with DB.
     *
     * @return \PDO
     */
    public function getPDO() {
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
     * rowID => array(
     * fieldName => fieldValue,
     * ...
     * )
     * )
     * @endcode
     *  - @c empty array for empty result;
     *
     * @param string $query SELECT query.
     * @return mixed
     *
     * @throws SystemException
     *
     *
     * @note If the total amount of arguments is more than 1, then this function process the input arguments like @c printf function.
     *
     */
    private function selectRequest($query) {
        $result = [];
        /**
         * @var \PDOStatement
         */
        $res = call_user_func_array([$this, 'query'], func_get_args());
        while ($row = $res->fetch(\PDO::FETCH_ASSOC)) {
            array_push($result, $row);
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
     *
     */
    private function modifyRequest($query) {
        call_user_func_array([$this, 'query'], func_get_args());

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
    public function call($name, &$args = NULL) {
        if (!$args) {
            $res = $this->pdo->query("call $name();", \PDO::FETCH_NAMED);
        } else {
            $argString = implode(',', array_fill(0, count($args), '?'));
            $stmt = $this->pdo->prepare("CALL $name($argString)");
            foreach ($args as $index => &$value) {
                $stmt->bindParam($index + 1, $value);
            }
            if (($res = $stmt->execute()) && $stmt->rowCount()) {
                $res = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            }
        }
        return $res;
    }

    /**
     * Get data for further iteration.
     * @param string $request
     * @return \PDOStatement
     */
    public function query($request) {
        $args = func_get_args();
        try {
            $request = array_shift($args);
            $request = str_replace('%%', '%', $request);
            if (!empty($args)) {
                if (preg_match_all('(%(?:(\d)\$)?s)', $request, $matches)) {
                    $data = [];
                    $request = preg_replace('(%(?:(\d)\$)?s)', '?', $request);
                    $argIndex = 0;
                    foreach ($matches[1] as $a) {
                        if ($a = (int)$a) {
                            $v = $a - 1;
                        } else {
                            $v = $argIndex++;
                        }
                        array_push($data, $args[$v]);
                    }
                } else {
                    $data = $args;
                }
                $realData = [];

                $replaceRule = array_map(
                    function ($v) use (&$realData) {
                        if (is_array($v)) {
                            if (empty($v)) {
                                $v = [-1];
                            }
                            $realData = array_merge($realData, $v);
                        } else {
                            array_push($realData, $v);
                        }

                        if (is_null($v)) $v = 1;

                        return implode(',', array_fill(0, ($s = sizeof($v)) ? $s : 1, '?'));
                    },
                    $data
                );
                $qIndex = 0;
                $realQuery = '';
                for ($i = 0; $i < strlen($request); $i++) {
                    if ($request[$i] == '?') {
                        $realQuery .= $replaceRule[$qIndex++];
                    } else {
                        $realQuery .= $request[$i];
                    }
                }


                if (!($result = $this->pdo->prepare($realQuery))) {
                    throw new \UnexpectedValueException();
                }

                if (!$result->execute($realData)) {
                    throw new \UnexpectedValueException();
                }

            } else {
                if (!$result = $this->pdo->query($request)) {
                    throw new \UnexpectedValueException();
                }
            }
        } catch (\UnexpectedValueException $e) {
            new \PDOException($this->pdo->errorInfo()[2]);
        }
        $this->lastQuery = $result->queryString;
        return $result;
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
     * Get the @link QAL::$lastQuery last query@endlink.
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
     * return table list
     * this is draft method - I do not understand its necessity
     *
     * @todo use db cache if need be
     * @todo add pattern param - if need be
     * @return array
     */
    public function getTables() {
        return $this->getPDO()->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN, 0);
    }

    /**
     * Get columns info of the table.
     *
     * The returned array looks like:
     * @code
    array(
     * 'columnName' => array(
     * 'type'      => column type,
     * 'length'    => length,
     * 'nullable'  => accept NULL?,
     * 'key'       => description of the columns key (if exist),
     * 'default'   => default value,
     * 'index'     => index type
     * )
     * )
     * @endcode
     *
     * @param string $tableName Table name.
     * @return array
     */
    public function getColumnsInfo($tableName, $columns = NULL) {
        $result = $this->dbCache->getTableMeta($tableName);
        if (is_array($columns)) {

        }
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

    public function getTagsTablename($tableName) {
        return $this->tableExists($tableName . '_tags');
    }

    public function getUploadsTablename($tableName) {
        return $this->tableExists($tableName . '_uploads');
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
        $result = [];

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

    //todo VZ: There is not clear the order of arguments.
    /**
     * Execute simple SELECT-request and return the result.
     *
     * Field names of @c $fields can be:
     *   -# an array of names;
     *   -# single name;
     *   -# true - all table rows wil be selected.
     *
     * Selecting condition @c $condition is given by:
     *   -# an array like <tt>array(field_name => value)</tt>;
     *   -# a sting of @c WHERE condition like <tt>'field1 = 4 AND field2 = 8'</tt>.
     *
     * The sort order is given by:
     *   -# an array like <tt>array(field_name => sort_order)</tt>;
     *   -# a string of <tt>ORDER BY</tt> like <tt>'field1 DESC, field2 ASC'</tt>.
     *
     * Limit is given by:
     *   -# an array like <tt>array(offset, amount_of_rows)</tt>;
     *   -# a string of @c LIMIT like <tt>'32'</tt>
     *
     * @c true will be returned if the the result is empty.
     *
     * @param string $tableOrText Table name or SQL-request text (in this case all further arguments follows as variables)
     * @param array|string|true $fields Field names.
     * @param array|string $condition Condition.
     * @param array|string $sortOrder Sort order.
     * @param array|string $lim Limit.
     * @return array|true
     *
     * @see QAL::selectRequest()
     * @see QAL::buildSQL
     * @throws SystemException
     */
    public function select() {
        $args = func_get_args();
        if (empty($args)) {
            throw new SystemException('ERR_NO_QUERY', SystemException::ERR_DEVELOPER);
        }
        if (!strpos($args[0], ' ')) {
            //если в имени таблицы есть пробелы
            //будем считать что это просто SQL код
            $args = $this->buildSQL($args);
        }
        return call_user_func_array([$this, 'selectRequest'], $args);
    }

    /**
     * Execute simple modification (INSERT, UPDATE, DELETE) operation in the data base.
     *
     * The operation mode defines by one of the following constants:
     *   -# QAL::INSERT - inserting;
     *   -# QAL::UPDATE - updating;
     *   -# QAL::DELETE - removing.
     *
     * Data for operation mode QAL::INSERT and QAL::UPDATE is given by an array like @code array(field_name => value) @endcode
     *
     * Operation condition is given by:
     *   -# an array like <tt>array(field_name => value)</tt>;
     *   -# a string of @c WHERE like <tt>'field1 = 4 AND field2 = 8'</tt>.
     *
     * Return values:
     *   - Mode QAL::INSERT:
     *     - last generated ID for field type @c AUTO_INCREMENT, or
     *     - @c true if such field is not exist in the table.
     *   - Mode QAL::UPDATE, QAL::DELETE:
     *     - @c true by success
     *   - @c false by execution error.
     *
     * @param int $mode Operation mode.
     * @param string $tableName Table name.
     * @param array $data Data for operation.
     * @param mixed $condition Operation condition.
     * @return int|bool
     *
     * @throws SystemException
     *
     * @see QAL::modifyRequest()
     */
    public function modify($mode, $tableName = NULL, $data = NULL, $condition = NULL) {
        //Если в первом параметре не один из зарегистрированных режимов - считаем что это запрос
        if (!in_array($mode, [self::INSERT, self::INSERT_IGNORE, self::REPLACE, self::DELETE, self::UPDATE])) {
            return call_user_func_array([$this, 'modifyRequest'], func_get_args());
        }

        if (empty($mode) || empty($tableName)) {
            throw new SystemException(self::ERR_BAD_QUERY_FORMAT, SystemException::ERR_DB);
        }
        $tableName = QAL::getFQTableName($tableName);

        $args = [];

        $buildQueryBody = function ($data, &$args) {
            if (!empty($data)) {
                $key = 0;
                foreach ($data as $fieldValue) {
                    $args[$key] = $fieldValue;
                    if ($fieldValue === self::EMPTY_STRING) {
                        $args[$key] = '';
                    } elseif ($fieldValue == '') {
                        $args[$key] = NULL;
                    }
                    $key++;
                }
            }
        };

        switch ($mode) {
            case self::INSERT:
            case self::INSERT_IGNORE:
            case self::REPLACE:
                if (!empty($data)) {
                    $buildQueryBody($data, $args);
                    $sqlQuery = $mode . ' INTO ' . $tableName . ' (' . implode(', ', array_keys($data)) . ') VALUES (' . implode(', ', array_fill(0, sizeof($data), '%s')) . ')';
                } else {
                    $sqlQuery = 'INSERT INTO ' . $tableName . ' VALUES ()';
                }
                break;
            case self::UPDATE:
                if (!empty($data)) {
                    $buildQueryBody($data, $args);
                    $sqlQuery = 'UPDATE ' . $tableName . ' SET ' . implode(', ', array_map(function ($fieldName) {
                            return $fieldName . '= %s';
                        }, array_keys($data)));
                } else {
                    throw new SystemException(self::ERR_BAD_QUERY_FORMAT, SystemException::ERR_DB);
                }
                break;
            case self::DELETE:
                $sqlQuery = 'DELETE FROM ' . $tableName;
                break;
  /*          case self::COPY://modBySD
		throw new SystemException(self::ERR_BAD_QUERY_FORMAT, SystemException::ERR_DB);
                if (!empty($data)&&!empty($condition)) {
                    $buildQueryBody($data, $args);
                    $sqlArgs=implode(', ', array_map(function ($fieldName) {
                            return $fieldName . '= %s';
                        }, array_keys($data)));
                    $sqlQuery = 'INSERT INTO ' . $tableName . ' SELECT ' .$sqlArgs. ' FROM '. $tableName;
                } else {
                    throw new SystemException(self::ERR_BAD_QUERY_FORMAT, SystemException::ERR_DB);
                }            
		break;*/
            default:
                throw new SystemException(self::ERR_BAD_QUERY_FORMAT, SystemException::ERR_DB);
        }

        if (isset($condition) && $mode != self::INSERT) {
            $sqlQuery .= $this->buildWhereCondition($condition, $args);
        }
        array_unshift($args, $sqlQuery);
        //throw new SystemException(self::ERR_BAD_QUERY_FORMAT, $sqlQuery);
        return call_user_func_array([$this, 'modifyRequest'], $args);
    }

    /**
     * Get the scalar value of the column in the table.
     *
     * @param string $tableName Table name.
     * @param string $colName Column name.
     * @param array|mixed $cond Condition.
     * @return mixed
     */
    public function getScalar() {
        return call_user_func_array([$this, 'query'], $this->buildSQL(func_get_args()))->fetchColumn();
    }

    public function getRow() {
        return call_user_func_array([$this, 'query'], $this->buildSQL(func_get_args()))->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Get column values from the table.
     *
     * @param string $tableName Table name.
     * @param string $colName Column name.
     * @param array|mixed $cond Condition.
     * @return array
     */
    public function getColumn() {
        return call_user_func_array([$this, 'query'], $this->buildSQL(func_get_args()))->fetchAll(\PDO::FETCH_COLUMN);
    }

    /**
     * Build @c WHERE condition for SQL request.
     *
     * @param mixed $condition Condition.
     * @param array $args
     * @return string
     *
     * @see QAL::selectRequest()
     */
    public function buildWhereCondition($condition, array &$args = NULL) {
        if (!is_null($args)) {
            $result = '';
            if (!empty($condition)) {
                $result = ' WHERE ';
                if (is_array($condition)) {
                    $cond = [];
                    foreach ($condition as $fieldName => $value) {
                        if (is_null($value)) {
                            $cond[] = $fieldName . ' IS NULL';
                        } elseif (is_numeric($fieldName)) {
                            $cond[] = $value;
                        } elseif (is_array($value)) {
                            $value = array_filter($value);
                            $value = implode(',', array_map(function ($row) use (&$args) {
                                array_push($args, $row);
                                return '%s';
                            }, $value));

                            if (!empty($value))
                                $cond[] = $fieldName . ' IN (' . $value . ')';
                            else $cond[] = ' FALSE ';
                        } else {
                            $cond[] = $fieldName . ' = %s';
                            array_push($args, $value);
                        }
                    }
                    $result .= implode(' AND ', $cond);
                } else {
                    $result .= $condition;
                }
            }
        } else {
            $result = '';
            if (!empty($condition)) {
                $result = ' WHERE ';

                if (is_array($condition)) {
                    $cond = [];
                    foreach ($condition as $fieldName => $value) {
                        //$fieldName = strtolower($fieldName);
                        if (is_null($value)) {
                            $cond[] = "$fieldName IS NULL";
                        } elseif (is_numeric($fieldName)) {
                            $cond[] = $value;
                        } elseif (is_array($value)) {
                            $value = array_filter($value);

                            $value = implode(',', array_map(create_function('$row', 'return \'"\'.$row.\'"\';'), $value));

                            if (!empty($value))
                                $cond[] = $fieldName . ' IN (' . $value . ')';
                            else $cond[] = ' FALSE ';
                        } else {
                            $cond[] = "$fieldName = " . $this->quote($value);
                        }
                    }
                    $result .= implode(' AND ', $cond);
                } else {
                    $result .= $condition;
                }
            }
        }

        return $result;
    }


    /**
     * Get foreign key data.
     *
     * It returns a data from the linked table by foreign key.
     *
     * @param string $fkTableName Table name.
     * @param string $fkKeyName Key name.
     * @param int $currentLangID Current language ID.
     * @param mixed $filter Restriction for selecting.
     * @param mixed $order Order condition
     *
     * @return array
     */
    public function getForeignKeyData($fkTableName, $fkKeyName, $currentLangID, $filter = NULL, $order = NULL) {
        $fkValueName = substr($fkKeyName, 0, strrpos($fkKeyName, '_')) . '_name';
        $columns = $this->getColumnsInfo($fkTableName);

        if (!$order)
            foreach (array_keys($columns) as $columnName) {
                if (strpos($columnName, '_order_num')) {
                    $order = $columnName . ' ' . QAL::ASC;
                    break;
                }
            }

        $transTableName = $this->getTranslationTablename($fkTableName);
        //если существует таблица с переводами для связанной таблицы
        //нужно брать значения оттуда
        if (isset($columns[$fkValueName]) || !$transTableName) {
            //Если не существует поля с name берем в качестве поля со значением то же самое поле что и с id
            if (!isset($columns[$fkValueName])) $fkValueName = $fkKeyName;

            $columns = array_filter($columns,
                function ($value) {
                    return !($value["type"] == QAL::COLTYPE_TEXT);
                }
            );
            $res = $this->select($fkTableName, array_keys($columns), $filter, $order);
        } else {
            $columns = $this->getColumnsInfo($transTableName);
            if (!isset($columns[$fkValueName])) $fkValueName = $fkKeyName;

            if ($filter) {
                $filter = ' AND ' . str_replace('WHERE', '', $this->buildWhereCondition($filter));
            } else {
                $filter = '';
            }

            $res = $this->selectRequest(sprintf('SELECT
                                %2$s.*, %3$s.%s
                                FROM %s
                                LEFT JOIN %s on %3$s.%s = %2$s.%s
                                WHERE lang_id =%s' . $filter . $this->buildOrderCondition($order),
                $fkValueName,
                QAL::getFQTableName($fkTableName),
                QAL::getFQTableName($transTableName),
                $fkKeyName,
                $fkKeyName,
                $currentLangID));
        }

        return [$res, $fkKeyName, $fkValueName];
    }

    /**
     * Build <tt>ORDER BY</tt> line for SQL request.
     *
     * @param mixed $clause Clause.
     * @return string
     *
     * @see QAL::selectRequest()
     */
    public function buildOrderCondition($clause) {
        $orderClause = '';
        if (!empty($clause)) {
            $orderClause = ' ORDER BY ';

            if (is_array($clause)) {
                $cls = [];
                foreach ($clause as $fieldName => $direction) {
                    $direction = strtoupper($direction);
                    $cls[] = "$fieldName " . constant("self::$direction");
                }
                $orderClause .= implode(', ', $cls);
            } else {
                $orderClause .= $clause;
            }
        }
        return $orderClause;
    }

    /**
     * Build @c LIMIT line for SQL request.
     *
     * @param mixed $clause Clause.
     * @return string
     *
     * @see QAL::selectRequest()
     */
    public
    function buildLimitStatement($clause) {
        $limitClause = '';
        if (is_array($clause)) {
            $limitClause = " LIMIT {$clause[0]}";
            if (isset($clause[1])) {
                $limitClause .= ", {$clause[1]}";
            }
        }

        return $limitClause;
    }

    /**
     * Build SQL query
     *
     * @param array $args Arguments for query.
     * @return array
     *
     * @throws SystemException
     */
    private
    function buildSQL(array $args) {
        //If first argument contains space  - assume this is SQL string
        if (strpos($args[0], ' ')) {
            return $args;
        }
        //Want to do it this way - but it throws Notice with our level of error reporting
        //list($tableName, $fields, $condition, $order, $limit )  = $args;

        $fields = true;
        $condition = $order = $limit = NULL;
        $tableName = $args[0];
        if (isset($args[1])) {
            $fields = $args[1];
        }
        if (isset($args[2])) {
            $condition = $args[2];
        }
        if (isset($args[3])) {
            $order = $args[3];
        }
        if (isset($args[4])) {
            $limit = $args[4];
        }

        if (is_array($fields) && !empty($fields)) {
            $fields = array_map('strtolower', $fields);
            $fields = implode(', ', $fields);
        } elseif (is_string($fields)) {
            $fields = strtolower($fields);
        } elseif ($fields === true) {
            $fields = '*';
        } else {
            throw new SystemException(self::ERR_BAD_QUERY_FORMAT, SystemException::ERR_DB, [$tableName, $fields, $condition, $order, $limit]);
        }


        $sqlQuery = "SELECT $fields FROM " . QAL::getFQTableName($tableName);

        if (isset($condition)) {
            $sqlQuery .= $this->buildWhereCondition($condition);
        }

        if (isset($order)) {
            $sqlQuery .= $this->buildOrderCondition($order);
        }

        if (isset($limit)) {
            if (is_array($limit)) {
                $sqlQuery .= ' LIMIT ' . implode(', ', $limit);
            } else {
                $sqlQuery .= " LIMIT $limit";
            }
        }
        return [$sqlQuery];
    }
}
