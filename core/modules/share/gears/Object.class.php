<?php
/**
 * @file
 * Object
 *
 * Contain the definition to:
 * - abstract class Object.
 *
 * @package kernel
 * @author 1m.dm
 * @copyright Energine 2006
 */

/**
 * Te main parent abstract class for all objects in the system.
 *
 * This provides the general functionality to the objects:
 * - measure the execution time;
 * - parse and process the configuration file.
 */
abstract class Object {
    /**
     * Configuration file name.
     */
    const CONFIG_FILE = 'system.config.php';

    /**
     * System configuration.
     * It has tree structure.
     * @var SimpleXMLElement $systemConfig
     */
    private static $systemConfig;

    /**
     * Execution time counter.
     * @var float $executionTime
     */
    private $executionTime;

    /**
     * Start the [execution time counter](@ref Object::$executionTime).
     */
    public function startTimer() {
        $this->executionTime = microtime(true);
    }

    /**
     * Stop the [execution time counter](@ref Object::$executionTime).
     *
     * @return Execution time.
     */
    public function stopTimer() {
        return ($this->executionTime = microtime(true) - $this->executionTime);
    }

    /**
     * Reset the [execution time counter](@ref Object::$executionTime).
     *
     * @return Last value.
     */
    public function resetTimer() {
        $result = $this->stopTimer();
        $this->startTimer();
        return $result;
    }

    /**
     * Get the current value of the [execution time counter](@ref Object::$executionTime).
     *
     * @return Current value.
     */
    public function getTimer() {
        return $this->executionTime;
    }

    /**
     * Get the configuration value by parameter path.
     *
     * @note Use dot character as separator between configuration's tree levels.
     * @code Object::_getConfigValue('database.dsn'); @endcode
     *
     * @param string $paramPath Parameter path.
     * @param mixed $initial Default value. It will be used if the looked value is not found.
     * @return string
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
     * Nonstatic method-wrapper over Object::_getConfigValue for simpler using inside the derivative classes.
     *
     * @see Object::_getConfigValue()
     *
     * @param string $paramPath Parameter path.
     * @param mixed $initial Default value. It will be used if the looked value is not found.
     * @return string
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
     * Get the [configurations](@ref Object::$systemConfig).
     * @return array
     */
    public static function getConfigArray() {
        return self::$systemConfig;
    }
}
