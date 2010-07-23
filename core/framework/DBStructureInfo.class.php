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
     * ОБъект мемкеш
     * @var Memcached
     */
    private $memcache;
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

        if($this->getConfigValue('cache.enable')) {
            $this->memcache = new Memcached();
            $this->memcache->addServer($this->getConfigValue('cache.host'), $this->getConfigValue('cache.port'));
            if(!($str = $this->memcache->get('structure'))) {
                $this->memcache->set('structure', serialize($this->structure = $this->collectDBInfo()));
            }
            else {
                $this->structure = unserialize($str);
                inspect($this->structure);
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
        if($res) {
            while ($tableName = $res->fetchColumn()) {
                $result[$tableName] = $this->getTableMeta($tableName);
            }
        }

        return $result;
    }
    /**
     * Проверка таблицы на существование
     *
     * @param  string $tableName
     * @return bool
     */
    public function tableExists($tableName) {
        $result = false;
        //Если не существует в кеше
        if(!isset($this->structure[$tableName])) {
            //если существует в списке таблиц
            if($this->pdo->query('SHOW TABLES LIKE \''.$tableName.'\'')->rowCount()) {
                $result = true;
                $this->structure[$tableName] = array();
            }
            else {
                //в списке таблиц  - не значится
                $this->structure[$tableName] = false;
            }
        }
        else {
            $result = true;
        }
        return $result;
    }
    /**
     * Возвращает информацию о колонках таблицы
     * Записывает данные в $this->structure для последующего использования
     *
     * @param  string $tableName
     * @return array|mixed
     */
    public function getTableMeta($tableName) {
		if(!isset($this->structure[$tableName]) || ($this->structure[$tableName] === array())){
			$res = $this->pdo->query("SHOW COLUMNS FROM `$tableName`");
            if($res) {
                $this->structure[$tableName] = array();
                while($row = $res->fetch(PDO::FETCH_ASSOC)){
                    $name = $row['Field'];
                    
                    $type = self::convertType(strstr($row['Type'], '(', true));
                    $nullable = (strtolower($row['Null']) == 'yes' ? true : false);
                    $index = $row['Key'];
                    $length = 10; 
                    switch ($index) {
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
                    $default = (empty($row['Default']))?false:$row['Default'];
                    $this->structure[$tableName][$name] = compact( 'nullable', 'length', 'default', 'key', 'type' , 'tableName', 'index');
                }
            }
		}

        return $this->structure[$tableName];
    }
    /**
     * Возвращает информацию о  внешних ключах
     *
     * @param  string$tableName
     * @param  string $fieldName
     * @return array | bool
     * @staticvar array $foreignKeyInfo
     * @staticvar array $createTableInfo
     */
    private function getForeignKeyInfo($tableName, $fieldName) {
		static $foreignKeyInfo, $createTableInfo;
        if(!isset($createTableInfo[$tableName])){
            $createTableInfo[$tableName] = $this->pdo->query("SHOW CREATE TABLE `$tableName`")->fetchColumn(1);
        }
		if(!isset($foreignKeyInfo[$tableName][$fieldName])){
			$res = preg_match_all("/FOREIGN KEY \(`([_a-z0-9]+)`\) REFERENCES `([^`]+)` \(`([^`]+)`\)/m", $createTableInfo[$tableName], $matches, PREG_SET_ORDER);
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
     * Конвертирует MYSQL типы полей в Energine типы полей
     *
     * @static
     * @param  string $mysqlType
     * @return string
     */
    static private function convertType($mysqlType) {
		$result = $mysqlType;
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
