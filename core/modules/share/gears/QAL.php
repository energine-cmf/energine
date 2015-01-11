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
 * Query Abstraction Layer.
 *
 * @code
final class QAL;
 * @endcode
 *
 * @final
 */
final class QAL extends DBA {
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
    const EMPTY_STRING = null;

    /**
     * Errors.
     * @var string ERR_BAD_QUERY_FORMAT
     */
    const ERR_BAD_QUERY_FORMAT = 'Bad query format.';

    //todo VZ: I think this can be removed from here.
    /**
     * @copydoc DBA::__construct
     */
    public function __construct($dsn, $username, $password, array $driverOptions, $charset = 'utf8') {
        parent::__construct($dsn, $username, $password, $driverOptions, $charset);
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
     * @see DBA::selectRequest()
     * @see QAL::buildSQL
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
        return call_user_func_array(array($this, 'selectRequest'), $args);
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
     * @see DBA::modifyRequest()
     */
    public function modify($mode, $tableName = null, $data = null, $condition = null) {
        //Если в первом параметре не один из зарегистрированных режимов - считаем что это запрос
        if (!in_array($mode, array(self::INSERT, self::INSERT_IGNORE, self::REPLACE, self::DELETE, self::UPDATE))) {
            return call_user_func_array(array($this, 'modifyRequest'), func_get_args());
        }

        if (empty($mode) || empty($tableName)) {
            throw new SystemException(self::ERR_BAD_QUERY_FORMAT, SystemException::ERR_DB);
        }
        $tableName = DBA::getFQTableName($tableName);

        $args = array();

        $buildQueryBody = function ($data, &$args) {
            if (!empty($data)) {
                $key = 0;
                foreach ($data as $fieldValue) {
                    $args[$key] = $fieldValue;
                    if ($fieldValue === self::EMPTY_STRING) {
                        $args[$key] = '';
                    } elseif ($fieldValue == '') {
                        $args[$key] = null;
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
            default:
                throw new SystemException(self::ERR_BAD_QUERY_FORMAT, SystemException::ERR_DB);
        }

        if (isset($condition) && $mode != self::INSERT) {
            $sqlQuery .= $this->buildWhereCondition($condition, $args);
        }
        array_unshift($args, $sqlQuery);
        return call_user_func_array(array($this, 'modifyRequest'), $args);
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
    public function buildWhereCondition($condition, array &$args = null) {
        if ($this->getConfigValue('database.prepare') && !is_null($args)) {
            $result = '';
            if (!empty($condition)) {
                $result = ' WHERE ';
                if (is_array($condition)) {
                    $cond = array();
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
                    $cond = array();
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
     * @return array
     */
    public function getForeignKeyData($fkTableName, $fkKeyName, $currentLangID, $filter = null) {
        $fkValueName = substr($fkKeyName, 0, strrpos($fkKeyName, '_')) . '_name';
        $columns = $this->getColumnsInfo($fkTableName);

        $order = '';
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
            //$res = $this->selectRequest('SELECT '.implode(',', array_keys($columns)).' FROM '.$fkTableName.)
        } else {
            $columns = $this->getColumnsInfo($transTableName);
            if (!isset($columns[$fkValueName])) $fkValueName = $fkKeyName;

            if ($filter) {
                $filter = ' AND ' . str_replace('WHERE', '', $this->buildWhereCondition($filter));
            } else {
                $filter = '';
            }

            $request = sprintf(
                'SELECT 
                    %2$s.*, %3$s.%s 
                    FROM %s
                    LEFT JOIN %s on %3$s.%s = %2$s.%s
                    WHERE lang_id =%s' . $filter . (($order) ? ' ORDER BY ' . $order : ''),
                $fkValueName,
                DBA::getFQTableName($fkTableName),
                DBA::getFQTableName($transTableName),
                $fkKeyName,
                $fkKeyName,
                $currentLangID
            );
            $res = $this->selectRequest($request);
        }

        return array($res, $fkKeyName, $fkValueName);
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
                $cls = array();
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
    public function buildLimitStatement($clause) {
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
    protected function buildSQL(array $args) {
        //If first argument contains space  - assume this is SQL string
        if (strpos($args[0], ' ')) {
            return $args;
        }
        //Want to do it this way - but it throws Notice with our level of error reporting
        //list($tableName, $fields, $condition, $order, $limit )  = $args;

        $fields = true;
        $condition = $order = $limit = null;
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
            throw new SystemException(self::ERR_BAD_QUERY_FORMAT, SystemException::ERR_DB, array($tableName, $fields, $condition, $order, $limit));
        }


        $sqlQuery = "SELECT $fields FROM " . DBA::getFQTableName($tableName);

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
        return array($sqlQuery);
    }

    /**
     * Get the scalar value of the column in the table.
     *
     * @param string $tableName Table name.
     * @param string $colName Column name.
     * @param array|mixed $cond Condition.
     * @return null|string
     */
    public function getScalar() {
        $res = call_user_func_array(array($this, 'fulfill'), $this->buildSQL(func_get_args()));

        if ($res instanceof \PDOStatement) {
            return $res->fetchColumn();
        }

        return null;
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
        $res = call_user_func_array(array($this, 'fulfill'), $this->buildSQL(func_get_args()));

        $result = array();
        if ($res instanceof \PDOStatement) {
            while ($row = $res->fetch(\PDO::FETCH_NUM)) {
                array_push($result, $row[0]);
            }
        }

        return $result;
    }
}
