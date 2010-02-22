<?php
    /**
     * Содержит класс SystemConfig (singleton)
     *
     * @package energine
     * @subpackage configurator
     * @author Tigrenok
     * @copyright Energine 2007
     * @version $Id: SystemConfig.class.php,v 1.5 2007/11/26 14:11:07 tigrenok Exp $
     */

    /**
     * Загружает xml-файл конфигурацииsingletonsingleton системы
     *
     * @package energine
     * @subpackage configurator
     */
    class SystemConfig {

        /**
         * SystemConfig instance
         *
         * @var SystemConfig
         * @access private
         */
        private static $instance;

        /**
         * Путь к файлу от корня сервера
         *
         * @var string
         * @access private
         */
        private static $systemConfigPath;

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
   		 * @return SystemConfig instance
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
   		 * Проверяет файл конфигурации
   		 *
   		 * @return void
   		 * @access private
   		 */
        private function checkXMLFile() {

            if (!file_exists($this->systemConfigPath)) {
                throw new Exception('Файла конфигурации не существует ('.$this->systemConfigPath.')!');
            }
            elseif (!is_readable($this->systemConfigPath)) {
                throw new Exception('Невозможно прочесть файла конфигурации ('.$this->systemConfigPath.')!');
            }

        }

        /**
   		 * Проверяет файл конфигурации
   		 *
   		 * @return boolean
   		 * @access public
   		 */
        public function getXMLFile() {

            $this->systemConfigPath = str_replace(SCRIPT_NAME,'',$_SERVER['SCRIPT_FILENAME']).'/'.PATH_SYSTEM_CONFIG;

            $this->checkXMLFile();

            if (!function_exists('simplexml_load_file')) {
                throw new Exception('Отсутстувует необходимый модуль PHP - SimpleXML. Продолжение работы невозможно.');
            }

            return simplexml_load_file($this->systemConfigPath);

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