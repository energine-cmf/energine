<?php

/**
 * Класс Object.
 *
 * @package energine
 * @subpackage core
 * @author 1m.dm
 * @copyright Energine 2006
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
     * Имя файла конфигурации
     */
    const CONFIG_FILE = 'system.config.php';

    /**
     * @access private
     * @static
     * @var SimpleXMLElement конфигурация системы
     */
    private static $systemConfig;

    /**
     * @access private
     * @var float счетчик времени выполнения
     */
    private $executionTime;

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
        if(is_null(self::$systemConfig)) self::$systemConfig = include_once(SITE_DIR.'/'.self::CONFIG_FILE);
        $result = self::$systemConfig;
        $paramPath = explode('.', $paramPath);
        foreach($paramPath as $segment) {
            if(isset($result[$segment]))
                $result = $result[$segment];
            else {
                return null;
            }
        }

        return $result;
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
