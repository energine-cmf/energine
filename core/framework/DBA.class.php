<?php

/**
 * Класс DBA.
 *
 * @package energine
 * @subpackage core
 * @author 1m.dm
 * @copyright Energine 2006
 * @version $Id$
 */

//require_once('core/framework/SystemConfig.class.php');

/**
 * Database Abstraction Layer.
 *
 * @package energine
 * @subpackage core
 * @author 1m.dm
 * @abstract
 */
abstract class DBA extends Object {

    /**
     * @access protected
     * @var PDO экземпляр класса PDO (PHP Data Objects)
     */
    protected $pdo;

    /**
     * @access protected
     * @var string последний запрос к БД
     */
    protected $lastQuery;

    /**
     * @access protected
     * @var mixed результат последнего запроса к БД
     */
    protected $lastResult;

    /*
     * Типы полей таблиц БД:
     */

    /**
     * Целое число
     */
    const COLTYPE_INTEGER = 'INT';

    /**
     * Число с плавающей точкой
     */
    const COLTYPE_FLOAT = 'FLOAT';

    /**
     * Дата
     */
    const COLTYPE_DATE = 'DATE';

    /**
     * Время
     */
    const COLTYPE_TIME = 'TIME';

    /**
     * Timestamp
     */
    const COLTYPE_TIMESTAMP = 'TIMESTAMP';

    /**
     * Дата и время
     */
    const COLTYPE_DATETIME = 'DATETIME';

    /**
     * Строка
     */
    const COLTYPE_STRING = 'VARCHAR';

    /**
     * Типы строк только для внутреннего использования. Без комментариев :)
     */
    const COLTYPE_STRING1 = 'STRING';
    const COLTYPE_STRING2 = 'VAR_STRING';

    /**
     * Текст
     */
    const COLTYPE_TEXT = 'TEXT';

    /**
     * Бинарные данные
     */
    const COLTYPE_BLOB = 'BLOB';

    /**
     * Ошибки
     */
    const ERR_BAD_REQUEST = 'ERR_DATABASE_ERROR';

    /**
     * Первичный индекс
     *
     */
    const PRIMARY_INDEX = 'PRI';

    /**
     * Уникальный индекс
     *
     */
    const UNIQUE_INDEX = 'UNI';

    /**
     * Индекс
     *
     */
    const INDEX = 'MUL';

    /**
     * Конструктор класса.
     *
     * @access public
     * @param string $dsn Data Source Name для подключения к БД
     * @param string $username имя пользователя
     * @param string $password пароль
     * @param array $driverOptions специфические параметры драйвера БД
     * @return void
     */
    public function __construct($dsn, $username, $password, array $driverOptions, $charset = 'utf8') {
        parent::__construct();
        try {
            $this->pdo = new PDO($dsn, $username, $password, $driverOptions);
        }
        catch (PDOException $e) {
            throw new SystemException('Unable to connect. The site is temporarily unavailable.', SystemException::ERR_DB, 'The site is temporarily unavailable');
        }
        $this->pdo->query('SET NAMES '.$charset);
    }

    /**
     * Выполняет SELECT-запрос к БД.
     *
     * Если количество аргументов метода больше 1, тогда $query трактуется
     * как строка формата подобно функции printf, а дополнительные аргументы
     * экранируются и помещаются на место меток (placeholder) строки $query.
     *
     * Возвращает в результате:
     *     1. Массив вида
     *            array(
     *                rowID => array(fieldName => fieldValue, ...)
     *            )
     *        если запрос исполнился успешно и вернул какие-либо строки;
     *     2. true, если запрос исполнился успешно, но не вернул ни одной строки;
     *     3. false, если при выполнении запроса произошла ошибка.
     *
     * @access public
     * @param string $query SELECT-запрос к БД
     * @param mixed $var, ...
     * @return mixed
     * @see printf()
     */
    public function selectRequest($query) {
        if (!is_string($query) || strlen($query) == 0) {
            return false;
        }

        $result = false;

        $query = $this->constructQuery(func_get_args());
        //simple_log($query);
        $this->lastQuery = $query;
        $res = $this->pdo->query($query);

        if (!($res instanceof PDOStatement)) {
            $errorInfo = $this->pdo->errorInfo();
            throw new SystemException(self::ERR_BAD_REQUEST, SystemException::ERR_DB, array($this->getLastRequest(), $errorInfo[2]));
        }

        $result = array();
        $rowCount = 0;
        while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
            $fieldNum = 0;
            foreach ($row as $fieldName => $fieldValue) {
                $fieldMeta = @$res->getColumnMeta($fieldNum);
                if (isset($fieldMeta['native_type'])) {
                    if ($fieldMeta['native_type'] == self::COLTYPE_DATETIME ||
                        $fieldMeta['native_type'] == self::COLTYPE_DATE) {
                        $fieldValue = convertDatetimeToTimestamp($fieldValue);
                    }
                    elseif (in_array($fieldMeta['native_type'], array(self::COLTYPE_STRING1, self::COLTYPE_STRING2))) {
                        $fieldValue = stripslashes($fieldValue);
                    }
                }
                else {
                    if ($fieldMeta['len'] == 1) {
                        $fieldValue = (intval($fieldValue) == 0 ? false : true);
                    }
                }
                $result[$rowCount][$fieldName] = $fieldValue;
                $fieldNum++;
            }
            $rowCount++;
        }

        if (empty($result)) {
            $result = true;
        }

        $this->lastResult = $result;
        return $result;
    }

    /**
     * Выполняет модифицирующую (INSERT, UPDATE, DELETE) операцию в БД.
     *
     * Если количество аргументов метода больше 1, тогда $query трактуется
     * как строка формата подобно функции printf, а дополнительные аргументы
     * экранируются и помещаются на место меток (placeholder) строки $query.
     *
     * Возвращает в результате:
     *     1. Последний сгенерированный ID для поля типа AUTO_INCREMENT, или
     *     2. true, если запрос выполнен успешно;
     *     2. false, в случае неудачи.
     *
     * @access public
     * @param string $query
     * @return mixed
     * @see printf()
     */
    public function modifyRequest($query) {
        if (!is_string($query) || strlen($query) == 0) {
            return false;
        }

        $result = false;

        $query = $this->constructQuery(func_get_args());
        $this->lastQuery = $query;
        $res = $this->pdo->query($query);

        if (!($res instanceof PDOStatement)) {
            $errorInfo = $this->pdo->errorInfo();
            throw new SystemException(self::ERR_BAD_REQUEST, SystemException::ERR_DB, array($this->getLastRequest(), $errorInfo[2]));
        }

        $result = intval($this->pdo->lastInsertId());

        if ($result == 0) {
            $result = true;
        }

        $this->lastResult = $result;
        return $result;
    }

    /**
     * Ставит кавычки вокруг входной строки (если необходимо) и экранирует
     * специальные символы внутри входной строки.
     *
     * @access public
     * @param string $string
     * @return string
     */
    public function quote($string) {
        return $this->pdo->quote($string);
    }

    /**
     * Возвращает последний запрос к БД.
     *
     * @access public
     * @return string
     */
    public function getLastRequest() {
        return $this->lastQuery;
    }

    /**
     * Возвращает результат последнего запроса к БД.
     *
     * @access public
     * @return mixed
     */
    public function getLastResult() {
        return $this->lastResult;
    }
    /**
     * Возвращает последнюю ошибку
     *
     * @return string
     * @access public
     */

    public function getLastError() {
        return $this->pdo->errorInfo();
    }
    /**
     * Стартует транзакцию.
     *
     * @access public
     * @return boolean
     */
    public function beginTransaction() {
        return $this->pdo->beginTransaction();
    }

    /**
     * Выполняет (commit) транзакцию.
     *
     * @access public
     * @return boolean
     */
    public function commit() {
        return $this->pdo->commit();
    }

    /**
     * Откатывает транзакцию.
     *
     * @access public
     * @return boolean
     */
    public function rollback() {
        return $this->pdo->rollBack();
    }

    /**
     * Возвращает информацию о колонках таблицы $tableName в виде массива:
     *     array(
     *         'columnName' => array(
     *             'type' => тип колонки,
     *             'length' => длина,
     *             'nullable' => принимает ли значение NULL?,
     *             'key' => описание ключа колонки (если есть),
     *             'default' => значение по-умолчанию,
     *             'index'=> тип индекса
     *         )
     *     )
     *
     * @access public
     * @param string $tableName
     * @return array
     * @staticvar $columnsInfo  - кеш полей таблицы
     */
    public function getColumnsInfo($tableName) {
    	static $columnsInfo;
    	 
    	if(!isset($columnsInfo[$tableName])){
    		$res = $this->selectRequest("SHOW COLUMNS FROM `$tableName`");
	    	foreach ($res as $row) {
	            $name = $row['Field'];
	            $type = strtoupper($row['Type']);
	            $length = false;
	            $nullable = (strtolower($row['Null']) == 'yes' ? true : false);
	            $key = $row['Key'];
	            $index = $key;
	            $default = (empty($row['Default']))?false:$row['Default'];
	
	            // получаем тип и размер поля
	            preg_match('/([A-Z]+)(\(([0-9]+)(,[0-9]+)?\))?/', $type, $matches);
	            if (count($matches) >= 2) {
	                $type = $matches[1];
	                if (isset($matches[3])) {
	                    $length = intval($matches[3]);
	                }
	            }
	            $type = $this->convertType($type);
	
	            // получаем информацию о ключе поля
	            switch ($key) {
	                case 'PRI':
	                    $fk = $this->getForeignKeyInfo($tableName, $name);
	                    $key = ($fk == false ? true : $fk);
	                    break;
	                case 'MUL':
	                    $key = $this->getForeignKeyInfo($tableName, $name);
	                    break;
	                default:
	                    $key = false;
	            }
	
	            $columnsInfo[$tableName][$name] = compact('length', 'nullable', 'default', 'key', 'type' , 'tableName', 'index');
	        }
    	}
        return $columnsInfo[$tableName];
    }

    /**
     * Возвращает информацию о внешнем ключе поля $fieldName таблицы $tableName
     * в виде массива
     *     array(
     *         'tableName' => имя таблицы,
     *         'fieldName' => имя поля
     *     )
     * или false, если $tableName.$fieldName не является первичным ключем.
     *
     * @access public
     * @param string $tableName имя таблицы
     * @param string $fieldName имя поля
     * @return mixed
     * @staticvar $foreignKeyInfo кеш результатов
     */
    public function getForeignKeyInfo($tableName, $fieldName) {
        static $foreignKeyInfo;
        if(!isset($foreignKeyInfo[$tableName][$fieldName])){
	        $res = $this->selectRequest("SHOW CREATE TABLE `$tableName`");
	        $res = preg_match_all("/FOREIGN KEY \(`([_a-z0-9]+)`\) REFERENCES `([^`]+)` \(`([^`]+)`\)/m", $res[0]['Create Table'], $matches, PREG_SET_ORDER);
	        if(!empty($res)){
	        	foreach($matches as $row){
	        	  $foreignKeyInfo[$tableName][$row[1]] = array('tableName' => $row[2], 'fieldName' => $row[3]);	
	        	}
	        }
        }
        
        if(!isset($foreignKeyInfo[$tableName][$fieldName])){
            $foreignKeyInfo[$tableName][$fieldName] = false;
        }
        return $foreignKeyInfo[$tableName][$fieldName];
    }

    /**
     * Конвертирует тип данных из описания БД (MySQL) в наш, системный тип.
     *
     * @access private
     * @param string $mysqlType
     * @return string
     */
    private function convertType($mysqlType) {
        $result = $mysqlType;
        switch ($mysqlType) {
            case 'TINYINT':
            case 'MEDIUM':
            case 'SMALLINT':
            case 'INT':
            case 'BIGINT':
                $result = self::COLTYPE_INTEGER;
                break;
            case 'FLOAT':
            case 'DOUBLE':
            case 'DECIMAL':
            case 'NUMERIC':
                $result = self::COLTYPE_FLOAT;
                break;
            case 'DATE':
                $result = self::COLTYPE_DATE;
                break;
            case 'TIME':
                $result = self::COLTYPE_TIME;
                break;
            case 'TIMESTAMP':
                $result = self::COLTYPE_TIMESTAMP;
                break;
            case 'DATETIME':
                $result = self::COLTYPE_DATETIME;
                break;
            case 'VARCHAR':
            case 'CHAR':
                $result = self::COLTYPE_STRING;
                break;
            case 'TEXT':
            case 'TINYTEXT':
            case 'MEDIUMTEXT':
            case 'LONGTEXT':
                $result = self::COLTYPE_TEXT;
                break;
            case 'BLOB':
            case 'TINYBLOB':
            case 'MEDIUMBLOB':
            case 'LONGBLOB':
                $result = self::COLTYPE_BLOB;
                break;
            default: // не используется
        }
        return $result;
    }

    /**
     * Возвращает для таблицы $tableName имя таблицы с переводами,
     * если такая существует. В противном случае возвращает false.
     *
     * @access public
     * @param string $tableName
     * @return mixed
     */
    public function getTranslationTablename($tableName) {
    	static $translationTables;
    	
    	if(!isset($translationTables[$tableName])){
    	   $translationTableName = $tableName.'_translation';
    	   $res = $this->selectRequest('SHOW TABLES LIKE \''.$translationTableName.'\'');
    	   $translationTables[$tableName] = (empty($res) || $res === true) ? false : $translationTableName;   	
    	}
        return $translationTables[$tableName];
    }

    /**
     * Формирует строку запроса к БД.
     *
     * @access private
     * @param array $args массив аргументов, переданных в методы selectRequest и modifyRequest
     * @return string
     * @see DBA::selectRequest()
     * @see DBA::modifyRequest()
     */
    private function constructQuery(array $args) {
        if (sizeof($args) > 1) {
            $query = array_shift($args); // отбрасываем первый аргумент $query
            foreach ($args as &$arg) {
                $arg = $this->pdo->quote($arg);
            }
            array_unshift($args, $query);
            $query = call_user_func_array('sprintf', $args);
        }
        else {
        	$query = $args[0];
        }
        return $query;
    }
}
