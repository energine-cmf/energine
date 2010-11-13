<?php
/**
 * Содержит класс DBStructureInfo
 *
 * @package energine
 * @subpackage core
 * @author d.pavka@gmail.com
 * @copyright Energine 2010
 */

/**
 * Класс хранящий информацию о структуре БД
 * Имеет возможность хранить данные в кеше
 *
 * @package energine
 * @subpackage core
 * @final
 */
final class DBStructureInfo extends Object {
    /**
     * Массив информации о структуре БД
     *
     * @var array($tableName => array($coulmnName => array($columnPropName => $columnPropValue)))
     */
    private $structure;
    /**
     * Объект pdo - передается из DBA
     * @var PDO
     */
    private $pdo;

    /**
     *
     * @param PDO $pdo
     * @return void
     */
    public function __construct(PDO $pdo) {
        parent::__construct();
        $this->pdo = $pdo;
        $mc = Memcacher::getInstance();
        if($mc->isEnabled()){
            if(!($this->structure = $mc->retrieve('structure'))){
                $mc->store('structure', $this->structure = $this->collectDBInfo());
            }
        }
    }

    /**
     * Собирает информацию о структуре всех таблиц в БД
     * вызывается только при использовании кеша
     *
     * @return array
     * @see $this->structure
     */
    private function collectDBInfo() {
        $res = $this->pdo->query('SHOW TABLES');
        if ($res) {
            while ($tableName = $res->fetchColumn()) {
                $result[$tableName] = $this->getTableMeta($tableName);
            }
        }

        return $result;
    }

    /**
     * Проверка таблицы на существование
     * $this->structure[$tableName] может быть или массив или false ну или null
     * 
     * @param  string $tableName
     * @return bool
     */
    public function tableExists($tableName) {
        $result = false;
        //Если не существует в кеше
        if (!isset($this->structure[$tableName])) {
            //dump_log('tableExists '.$tableName, true);
            //если существует в списке таблиц
            if ($this->pdo->query(
                'SHOW TABLES LIKE \'' . $tableName . '\'')->rowCount()) {
                $result = true;
                $this->structure[$tableName] = array();
            }
            else {
                //в списке таблиц  - не значится
                $this->structure[$tableName] = false;
            }
        }
        else {
            $result = is_array($this->structure[$tableName]) ;

        }
        return $result;
    }

    public function getTableMeta($tableName) {
        if (!isset($this->structure[$tableName]) ||
                ($this->structure[$tableName] === array())) {
            $this->structure[$tableName] = $this->analyzeCreateTableSQL($this->pdo->query("SHOW CREATE TABLE `$tableName`")->fetchColumn(1));
            $this->structure[$tableName] = array_map(function($row) use($tableName){ $row['tableName'] = $tableName; return $row;}, $this->structure[$tableName]);
            //dump_log('getTableMeta '.$tableName, true);
        }
        return $this->structure[$tableName];
    }


    private function analyzeCreateTableSQL($sql) {
        $res = array();
        $s = strpos($sql, '(');
        $l = strrpos($sql, ')') - $s;

        // работаем только с полями и индексами
        $fields = substr($sql, $s + 1, $l);

        $trimQ = function($s) {
            return trim($s, '`');
        };

        $row = '(?:^\s*`(?<name>\w+)`' . // field name
                '\s+(?<type>\w+)' . //field type
                '(?:\((?<len>\w+)\))?' . //field len
                '(?:\s+(?<unsigned>unsigned))?' .
                '(?:\s*(?<is_null>(?:NOT )?NULL))?' .
                '(?:\s+DEFAULT (?<default>(?:NULL|\'[^\']+\')))?' .
                '.*$)';
        $constraint =
                '(?:^\s*CONSTRAINT `\w+` FOREIGN KEY \(`(?<cname>\w+)`\) REFERENCES `(?<tableName>\w+)` \(`(?<fieldName>\w+)`\)' .
                        '.*$)';
        $mul = '(?:^\s*(?:UNIQUE\s*)?KEY\s+`\w+`\s+\((?<muls>.*)\),?$)';

        $pri = '(?:PRIMARY KEY \((?<pri>[^\)]*)\))';

        $pattern = "/(?:$row|$constraint|$mul|$pri)/im";

        if (preg_match_all($pattern, trim($fields), $matches)) {
            if ($matches['name']) {
                // список полей в первичном ключе
                $pri = array();
                if (isset($matches['pri'])) {
                    foreach ($matches['pri'] as $priField) {
                        if ($priField) {
                            $pri = array_map($trimQ, explode(',', $priField));
                            break;
                        }
                    }
                }

                // список полей входящих в индексы
                $muls = array();
                if (isset($matches['muls'])) {
                    $mulStr = '';
                    foreach ($matches['muls'] as $s) {
                        if ($s) $mulStr .= ($mulStr ? ',' : '') . $s;
                    }
                    $muls = array_map($trimQ, explode(',', $mulStr));
                }

                foreach ($matches['name'] as $index => $fieldName) {
                    if (!$fieldName) continue;
                    $res[$fieldName] = array(
                        'nullable' => (
                                $matches['is_null'][$index] != 'NOT NULL'),
                        'length' => (int)$matches['len'][$index],
                        'default' => trim($matches['default'][$index], "'"),
                        'key' => false,
                        'type' => self::convertType($matches['type'][$index]),
                        'index' => false,
                    );

                    // входит ли поле в индекс
                    if (in_array($fieldName, $pri)) {
                        $res[$fieldName]['index'] = 'PRI';
                        $res[$fieldName]['key'] = true;
                    }
                    elseif (in_array($fieldName, $muls)) {
                        $res[$fieldName]['index'] = 'MUL';
                    }

                    // внешний ключ
                    $cIndex = array_search($fieldName, $matches['cname']);
                    if ($cIndex !== false) {
                        $res[$fieldName]['key'] = array(
                            'tableName' => $matches['tableName'][$cIndex],
                            'fieldName' => $matches['fieldName'][$cIndex],
                        );
                    }
                }
            }
        }
        return $res;
    }

    /**
     * Конвертирует MYSQL типы полей в Energine типы полей
     *
     * @static
     * @param  string $mysqlType
     * @return string
     */
    static private function convertType($mysqlType) {
        $result = $mysqlType = strtoupper($mysqlType);
        switch ($mysqlType) {
            case 'TINYINT':
            case 'MEDIUM':
            case 'SMALLINT':
            case 'INT':
            case 'BIGINT':
                $result = DBA::COLTYPE_INTEGER;
                break;
            case 'FLOAT':
            case 'DOUBLE':
            case 'DECIMAL':
            case 'NUMERIC':
                $result = DBA::COLTYPE_FLOAT;
                break;
            case 'DATE':
                $result = DBA::COLTYPE_DATE;
                break;
            case 'TIME':
                $result = DBA::COLTYPE_TIME;
                break;
            case 'TIMESTAMP':
                $result = DBA::COLTYPE_TIMESTAMP;
                break;
            case 'DATETIME':
                $result = DBA::COLTYPE_DATETIME;
                break;
            case 'VARCHAR':
            case 'CHAR':
                $result = DBA::COLTYPE_STRING;
                break;
            case 'TEXT':
            case 'TINYTEXT':
            case 'MEDIUMTEXT':
            case 'LONGTEXT':
                $result = DBA::COLTYPE_TEXT;
                break;
            case 'BLOB':
            case 'TINYBLOB':
            case 'MEDIUMBLOB':
            case 'LONGBLOB':
                $result = DBA::COLTYPE_BLOB;
                break;
            default: // не используется
        }
        return $result;
    }
}
