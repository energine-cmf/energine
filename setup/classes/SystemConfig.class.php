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
     * Загружает php-файл конфигурации singletonsingleton системы
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
        private function checkFile() {

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
        public function getFile() {

            $this->systemConfigPath = str_replace(SCRIPT_NAME,'',$_SERVER['SCRIPT_FILENAME']).'/'.PATH_SYSTEM_CONFIG;

            $this->checkFile();

            return requery_once($this->systemConfigPath);

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