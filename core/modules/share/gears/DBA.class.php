<?php

/**
 * Класс DBA.
 *
 * @package energine
 * @subpackage kernel
 * @author 1m.dm
 * @copyright Energine 2006
 */


/**
 * Database Abstraction Layer.
 *
 * @package energine
 * @subpackage kernel
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
     * @var DBStructureInfo
     */
    private $dbCache;

    /*
      * Типы полей таблиц БД:
      */

    /**
     * Целое числ
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
     * Дата и врем
     */
    const COLTYPE_DATETIME = 'DATETIME';

    /**
     * Строка
     */
    const COLTYPE_STRING = 'VARCHAR';

    /**
     * Типы строк только для внутреннего использования. Без комментариев :)
     */
    //const COLTYPE_STRING1 = 'STRING';
    //const COLTYPE_STRING2 = 'VAR_STRING';

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
     * @param string $charset кодировка соединения
     * @throws SystemException
     */
    public function __construct($dsn, $username, $password, array $driverOptions, $charset = 'utf8') {
        try {
            $this->pdo = new PDO($dsn, $username, $password, $driverOptions);
            $this->pdo->query('SET NAMES ' . $charset);

            $this->dbCache = new DBStructureInfo($this->pdo);

        } catch (PDOException $e) {
            throw new SystemException('Unable to connect. The site is temporarily unavailable.', SystemException::ERR_DB, 'The site is temporarily unavailable');
        }

    }

    /*
     * Возвращает обьект ПДО для
     * работы с БД напрямую.
     *
     * @access public
     * @return PDO
     */

    public function getPDO() {
        return $this->pdo;
    }

    /**
     * Выполняет SELECT-запрос к БД.
     *
     * Если количество аргументов метода больше 1, тогда $query трактуется
     * как строка формата подобно функции printf, а дополнительные аргументы
     * экранируются и помещаются на место меток (placeholder) строки $query.
     *
     * Возвращает в результате
     *     1. Массив вида
     *            array(
     *                rowID => array(fieldName => fieldValue, ...)
     *            )
     *        если запрос исполнился успешно и вернул какие-либо строки
     *     2. true, если запрос исполнился успешно, но не вернул ни одной строки;
     *     3. false, если при выполнении запроса произошла ошибка.
     *
     * @access public
     * @param string $query SELECT-запрос к БД
     * @return mixed
     * @throws SystemException
     * @see printf()
     * @deprecated
     */
    public function selectRequest($query) {
        $res = call_user_func_array(array($this, 'fulfill'), func_get_args());

        if (!($res instanceof PDOStatement)) {
            $errorInfo = $this->pdo->errorInfo();
            throw new SystemException($errorInfo[2], SystemException::ERR_DB, array($this->getLastRequest()));
        }

        $result = array();
        while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
            array_push($result, $row);
        }
        if (empty($result)) {
            $result = true;
        }

        return $result;
    }

    /**
     * Выполняет модифицирующую (INSERT, UPDATE, DELETE) операцию в БД.
     *
     * Если количество аргументов метода больше 1, тогда $query трактуется
     * как строка формата подобно функции printf, а дополнительные аргументы
     * экранируются и помещаются на место меток (placeholder) строки $query.
     *
     * Возвращает в результате
     *     1. Последний сгенерированный ID для поля типа AUTO_INCREMENT, или
     *     2. true, если запрос выполнен успешно;
     *     2. false, в случае неудачи.
     *
     * @access public
     * @param string $query
     * @return mixed
     * @throws SystemException
     * @see printf()
     */
    public function modifyRequest($query) {
        $res = call_user_func_array(array($this, 'fulfill'), func_get_args());

        if (!($res instanceof PDOStatement)) {
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
     * Вызов процедуры
     *
     * @param  string $name
     * @param  array $args
     * @return array|bool
     */
    public function call($name, &$args = null) {
        if (!$args) {
            $res = $this->pdo->query("call $name();", PDO::FETCH_NAMED);
        } else {
            $argString = implode(',', array_fill(0, count($args), '?'));
            $stmt = $this->pdo->prepare("CALL $name($argString)");
            foreach ($args as $index => &$value) {
                $stmt->bindParam($index + 1, $value);
            }
            if ($res = $stmt->execute()) {
                $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
        }
        return $res;
    }

    /**
     * Метод для получения данных с последующей итерацией
     *
     * @param string $query SQL запрос
     * @return bool|PDOStatement
     */
    public function get($query) {
        $res = call_user_func_array(array($this, 'fulfill'), func_get_args());
        if ($res instanceof PDOStatement) {
            return $res;
        }
        return false;
    }

    /**
     * Executes the request
     *
     * @param $request
     * @return bool|PDOStatement
     */
    protected function fulfill($request) {
        if (!is_string($request) || empty($request)) {
            return false;
        }
        if ($this->getConfigValue('database.prepare')) {
            $res = $this->runQuery(func_get_args());
            if ($res instanceof PDOStatement)
                $this->lastQuery = $res->queryString;
        } else {
            $request = $this->constructQuery(func_get_args());
            $res = $this->pdo->query($request);
            $this->lastQuery = $request;
        }
        return $res;
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
     */
    public function getColumnsInfo($tableName) {
        $result = $this->dbCache->getTableMeta($tableName);
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
        return $this->tableExists($tableName . '_translation');
    }

    /**
     * Существует ли таблица
     *
     * @param $tableName string имя таблицы
     * @return boolean
     * @access public
     */
    public function tableExists($tableName) {
        return ($this->dbCache->tableExists($tableName)) ? $tableName : false;
    }

    /**
     * Существует ли процедура
     *
     * @param $procName string имя процедуры
     * @return boolean
     * @access public
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
     * Существует ли функция
     *
     * @param $funcName string имя функции
     * @return boolean
     * @access public
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
     * Возвращает имя таблицы с именем базы данных(Fully Qualified) в мускульных кавычках
     *
     * @static
     * @param  string $tableName
     * @param bool Возвращать как массив
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
     * Формирует строку запроса к БД.
     *
     * @param array $args массив аргументов, переданных в методы selectRequest и modifyRequest
     * @return string
     * @see DBA::selectRequest()
     * @see DBA::modifyRequest()
     * @deprecated
     */
    protected function constructQuery(array $args) {
        if (sizeof($args) > 1) {
            $query = array_shift($args); // отбрасываем первый аргумент $query
            foreach ($args as &$arg) {
                $arg = $this->pdo->quote($arg);
            }
            array_unshift($args, $query);
            $query = call_user_func_array('sprintf', $args);
        } else {
            $query = $args[0];
        }
        return $query;
    }

    /**
     * @param array $args
     * @return PDOStatement
     * @throws SystemException
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
                throw new SystemException('ERR_PREPARE_REQUEST', $query);
            }
            if (!$result->execute($data)) {
                throw new SystemException('ERR_EXECUTE_REQUEST', $query);
            }

        } else {
            $result = $this->pdo->query($query);
        }
        return $result;
    }
}
