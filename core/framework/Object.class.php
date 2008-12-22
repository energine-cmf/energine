<?php

/**
 * Класс Object.
 *
 * @package energine
 * @subpackage core
 * @author 1m.dm
 * @copyright ColoCall 2006
 * @version $Id$
 */


/**
 * Родительский класс для всех объектов системы.
 * Обеспечивает общую функциональность объектов
 * измерение времени выполнения и загрузку данных из конфигурационного файла
 *
 * @package energine
 * @subpackage core
 * @author 1m.dm
 * @abstract
 */
abstract class Object {
    /**
     * Путь к файлу конфигурации
     */
    const CONFIG_DIR = 'site/';

    /**
     * Имя файла конфигурации
     */
    const CONFIG_FILE = 'system.config.xml';

    /**
     * @access public
     * @static
     * @var SimpleXMLElement конфигурация системы
     */
    public static $systemConfig;


    /**
     * @access private
     * @var float счетчик времени выполнения
     */
    private $executionTime;

    /**
      * Пустой конструктор
      *
      * @return void
      * @access public
      */

     public function __construct() {

     }

    /**
     * Запускает счетчик времени выполнения.
     *
     * @access public
     * @return void
     */
    public function startTimer() {
        $this->executionTime = microtime(true);
    }

    /**
     * Останавливает счетчик времени выполнения.
     *
     * @access public
     * @return float
     */
    public function stopTimer() {
        return ($this->executionTime = microtime(true) - $this->executionTime);
    }

    /**
     * Сбрасывает счетчик времени, возвращает предыдущее значение счетчика
     *
     * @return float
     * @access public
     */

    public function resetTimer() {
        $result = $this->stopTimer();
        $this->startTimer();
        return $result;
    }

    /**
     * Возвращает значение счетчика времени выполнения.
     *
     * @access public
     * @return float
     */
    public function getTimer() {
        return $this->executionTime;
    }

    /**
     * Возвращает значение указанного параметра конфигурации.
     * Конфигурация представляет из себя дерево параметров;
     * в качестве разделителя уровней дерева используется точка.
     * Пример:
     *     Object::_getConfigValue('database.dsn');
     *
     * @access public
     * @static
     * @param string $paramPath путь к параметру в дереве конфигурации
     * @return string
     */
    public static function _getConfigValue($paramPath) {
        //при первом обращении загружает SimpleXML данные в  статическую переменную $systemConfig
        if (!isset(self::$systemConfig)) {
            if (!file_exists(self::CONFIG_DIR.self::CONFIG_FILE)) {
            	trigger_error('ERR_DEV_NO_CONFIG', E_USER_ERROR);
            }
            self::$systemConfig = simplexml_load_file(self::CONFIG_DIR.self::CONFIG_FILE);
        }
        $paramPath = str_replace('.', '->', trim($paramPath));
        eval("\$value = (string)self::\$systemConfig->$paramPath;");
        return $value;
    }

    /**
     * Нестатический метод-обёртка над Object::_getConfigValue -
     * для удобства использования внутри производных классов.
     *
     * @access public
     * @param string $paramPath путь к параметру в дереве конфигурации
     * @return string
     * @see Object::_getConfigValue()
     */
    public function getConfigValue($paramPath) {
        return self::_getConfigValue($paramPath);
    }
}
