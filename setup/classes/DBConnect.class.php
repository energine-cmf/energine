<?php
    /**
     * Содержит класс DBConnect
     *
     * @package energine
     * @subpackage configurator
     * @author Tigrenok
     * @copyright Energine 2007

     */

    /**
     * Создает копии (или симлинки) файлов
     *
     * @package energine
     * @subpackage configurator
     */
    class DBConnect {

        /**
         * DBConnect instance
         *
         * @var DBConnect
         * @access private
         */
        private static $instance;

        /**
         * SQL resource
         *
         * @var SQL resource
         * @access private
         */
        private $resource;

        /**
   		 * Заглушка конструктора
   		 *
   		 * @return void
   		 * @access private
   		 */
        private function __construct() {}

        /**
   		 * Создание экземпляра
   		 *
   		 * @return DBConnect instance
   		 * @access public
   		 */
        public static function run() {
            if (!isset(self::$instance)) {
                $c = __CLASS__;
                self::$instance = new $c;
            }

            return self::$instance;
        }

        /**
   		 * Соединение с сервером баз данных
   		 *
   		 * @param string
   		 * @param string
   		 * @param string
   		 * @return boolean
   		 * @access public
   		 */
        public function connect($host,$username,$password) {
            if (!@(mysql_connect($host,$username,$password))) {
    		    throw new Exception('Невозможно соедениться с базой данных! ('.mysql_error().')');
    		} else {
    		    return true;
    		}
        }

        /**
   		 * Выбор базы данных
   		 *
   		 * @param string
   		 * @return boolean
   		 * @access public
   		 */
        public function selectdb($dbname) {
            if (!mysql_select_db($dbname)) {
    		    throw new Exception('Невозможно выбрать базу данных! ('.mysql_error().')');
    		} else {
    		    return true;
    		}
        }

        /**
   		 * Выполнение запроса
   		 *
   		 * @param string
   		 * @return void
   		 * @access public
   		 */
        public function query($query) {
            if (!($this->resource = mysql_query($query))) {
    		    throw new Exception('Неверный MySQL запрос ('.$query.')! ('.mysql_error().')');
    		}
        }

        /**
   		 * Выборка массива
   		 *
   		 * @return array
   		 * @access public
   		 */
        public function fetchArray() {
            if (!is_resource($this->resource)) {
    		    throw new Exception('Неверный MySQL ресурс!');
    		}
    		$result = array();
    		while($line = mysql_fetch_row($this->resource)) {
    		    $result[] = $line;
    		}

    		return $result;
        }

        /**
   		 * Выборка хэша
   		 *
   		 * @return array
   		 * @access public
   		 */
        public function fetchHash() {
            if (!is_resource($this->resource)) {
    		    throw new Exception('Неверный MySQL ресурс!');
    		}
    		$result = array();
    		while($line = mysql_fetch_assoc($this->resource)) {
    		    $result[] = $line;
    		}

    		return $result;
        }

        /**
   		 * Закрытие соодинения
   		 *
   		 * @return void
   		 * @access public
   		 */
        public function disconnect() {
            mysql_close();
        }

        /**
   		 * Отдает SQL ошибку
   		 *
   		 * @return string
   		 * @access public
   		 */
        public function error() {
            return mysql_error();
        }

        /**
   		 * Отдает автоинкрементальный идентификатор последнего добавленного элемента
   		 *
   		 * @return int
   		 * @access public
   		 */
        public function lastId() {
            return mysql_insert_id();
        }

        /**
   		 * Подготавливает информацию для корректности запросов
   		 * Запускается !только! через array_walk
   		 *
   		 * @param array
   		 * @return void
   		 * @access public
   		 */
        public function prepare(&$value, $key) {
            $value = str_replace("\n",'\n',$value);
            $value = str_replace("\r",'',$value);
            $value = str_replace("'","\'",$value);
            if (!is_numeric($value)) {
                $value = '\''.$value.'\'';
            }
        }

        /**
   		 * Заглушка клонирования
   		 *
   		 * @return void
   		 * @access public
   		 */
        public function __clone() {
            trigger_error('Клонирование запрещено законом. За нарушение - криминальная ответственность! :)', E_USER_ERROR);
        }

    }

?>