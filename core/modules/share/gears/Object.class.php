<?php
/**
 * @file
 * Object
 *
 * It contains the definition to:
 * @code
abstract class Object;
@endcode
 *
 * @author 1m.dm
 * @copyright Energine 2006
 *
 * @version 1.0.0
 */

/**
 * The main abstract class for all objects in the system.
 *
 * @code
abstract class Object
@endcode
 *
 * This provides the general functionality to the objects:
 * - measure the execution time;
 * - parse and process the configuration file.
 *
 * @abstract
 */
namespace Energine\share\gears;

abstract class Object {
    /**
     * Configuration file name.
     */
    const CONFIG_FILE = 'system.config.php';

    /**
     * System configuration.
     * It has tree structure.
     * @var \SimpleXMLElement $systemConfig
     */
    private static $systemConfig;

    /**
     * Execution time counter.
     * @var float $executionTime
     */
    private $executionTime;

    /**
     * Start the execution time counter.
     */
    public function startTimer() {
        $this->executionTime = \microtime(true);
    }

    /**
     * Stop the execution time counter.
     *
     * @return float
     */
    public function stopTimer() {
        return ($this->executionTime = \microtime(true) - $this->executionTime);
    }

    /**
     * Reset the execution time counter.
     *
     * @return float
     */
    public function resetTimer() {
        $result = $this->stopTimer();
        $this->startTimer();
        return $result;
    }

    /**
     * Get the current value of the execution time counter.
     *
     * @return float
     */
    public function getTimer() {
        return $this->executionTime;
    }

    /**
     * Get the configuration value by parameter path.
     *
     * @code
Object::_getConfigValue('database.dsn');
@endcode
     *
     * @param string $paramPath Parameter path.
     * @param mixed $initial Default value. It will be used if the looked value is not found.
     * @return string
     *
     * @note Use dot character as separator between configuration's tree levels.
     */
    public static function _getConfigValue($paramPath, $initial = null) {
        if(is_null(self::$systemConfig)) self::setConfigArray(include(self::CONFIG_FILE));
        $result = self::$systemConfig;
        $paramPath = explode('.', $paramPath);
        foreach($paramPath as $segment) {
            if(isset($result[$segment]))
                $result = $result[$segment];
            else {
                return $initial;
            }
        }

        return $result;
    }

    /**
     * Non-static method-wrapper over Object::_getConfigValue for simpler using inside the derivative classes.
     *
     * @param string $paramPath Parameter path.
     * @param mixed $initial Default value. It will be used if the looked value is not found.
     * @return string
     *
     * @see Object::_getConfigValue()
     */
    public function getConfigValue($paramPath, $initial = null) {
        return self::_getConfigValue($paramPath, $initial);
    }

    /**
     * Set the Object::$systemConfig.
     * @param array $config
     */
    public static function setConfigArray($config) {
        self::$systemConfig = $config;
    }

    /**
     * Get the @link Object::$systemConfig configurations@endlink.
     * @return array
     */
    public static function getConfigArray() {
        return self::$systemConfig;
    }
}
