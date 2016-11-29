<?php
/**
 * @file
 * Primitive
 *
 * It contains the definition to:
 * @code
abstract class Primitive;
 * @endcode
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
abstract class Primitive
 * @endcode
 *
 * This provides the general functionality to the objects:
 * - measure the execution time;
 * - parse and process the configuration file.
 *
 * @abstract
 * @method static mixed getConfigValue(string $paramPath, mixed $initial)
 */
namespace Energine\share\gears;

abstract class Primitive {
    /**
     * Configuration file name.
     */
    const CONFIG_FILE = 'package.json';

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

    public function __construct() {
    }

    /**
     * Get the @link Primitive::$systemConfig configurations@endlink.
     * @return array
     */
    public static function getConfigArray() {
        return self::$systemConfig;
    }

    /**
     * Reset the execution time counter.
     * @return float
     */
    public function resetTimer() {
        $result = $this->stopTimer();
        $this->startTimer();

        return $result;
    }

    /**
     * Stop the execution time counter.
     * @return float
     */
    public function stopTimer() {
        return ($this->executionTime = \microtime(true) - $this->executionTime);
    }

    /**
     * Start the execution time counter.
     */
    public function startTimer() {
        $this->executionTime = \microtime(true);
    }

    /**
     * Get the current value of the execution time counter.
     * @return float
     */
    public function getTimer() {
        return $this->executionTime;
    }

    /**
     * Non-static method-wrapper over Primitive::getConfigValue for simpler using inside the derivative classes.
     * @param string $paramPath Parameter path.
     * @param mixed $initial Default value. It will be used if the looked value is not found.
     * @return mixed
     * @see Object::getConfigValue()
     */
    public static function getConfigValue($paramPath, $initial = NULL) {
        if (is_null(self::$systemConfig)) {
            self::setConfig(json_decode(file_get_contents(ROOT_DIR.self::CONFIG_FILE), true));
        }
        $result = self::$systemConfig;
        $paramPath = explode('.', $paramPath);
        foreach ($paramPath as $segment) {
            if (isset($result[$segment])) {
                $result = $result[$segment];
            } else {
                return $initial;
            }
        }

        return $result;
    }

    /**
     * Set the Primitive::$systemConfig.
     * @param array $config
     */
    public static function setConfig(array $config) {
        self::$systemConfig = $config;
    }
}
