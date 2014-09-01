<?php
/**
 * @file
 * ComponentConfig.
 *
 * It contains the definition to:
 * @code
class ComponentConfig;
@endcode
 *
 * @author dr.Pavka
 * @copyright Energine 2007
 *
 * @version 1.0.0
 */
namespace Energine\share\gears;
/**
 * Component configuration manager.
 *
 * @code
class ComponentConfig;
@endcode
 */
class ComponentConfig {
    /**
     * Site configuration directory.
     * Path to the directory, that contains configuration files for the component.
     * @var string SITE_CONFIG_DIR
     */
    const SITE_CONFIG_DIR = '/modules/%s/config/';

    /**
     * Core configuration directory.
     * Path to the directory, that contains configuration files for standard components.
     *
     * @var string CORE_CONFIG_DIR
     *
     * @note @c %s will be substituted to the module name, that contains the component.
     */
    const CORE_CONFIG_DIR = '/modules/%s/config/';

    /**
     * Configuration file.
     * @var \SimpleXMLElement $config
     */
    private $config = false;

    /**
     * Configuration of the current state.
     * @var \SimpleXMLElement $currentState
     */
    private $currentState = false;

    /**
     * Initialize configuration.
     *
     * @param mixed $config Configuration or configuration filename.
     * @param string $className Class name.
     * @param string $moduleName Module name.
     * @return ComponentConfig
     *
     * @throws SystemException 'ERR_DEV_BAD_CONFIG_FILE'
     * @throws SystemException 'ERR_DEV_STRANGE'
     */
    public function __construct($config, $className, $moduleName) {
        //Если это строка(с именем файла) или false
        if (!$config || is_string($config)) {
            $config = ($param = $this->getConfigPath($config, $moduleName)) ? $param
                    : $this->getConfigPath(simplifyClassName($className) . '.component.xml', $moduleName);

            if ($config) {
                try {
                    $this->config = simplexml_load_file($config /*, 'ConfigElement'*/);
                } catch (\Exception  $e) {
                    throw new SystemException('ERR_DEV_BAD_CONFIG_FILE', SystemException::ERR_DEVELOPER, $config);
                }
            }
        } //А может это конфиг из шаблона?
        elseif (is_a($config, 'SimpleXMLElement')) {
            $this->config = $config;
        } else {
            //Этого не может быть
            throw new SystemException('ERR_DEV_STRANGE', SystemException::ERR_DEVELOPER, func_get_args());
            //поскольку быть этого не может никогда
        }
    }

    /**
     * Get full path to the configuration file.
     *
     * It will return @c false if the file was not found.
     *
     * @param string $configFilename Configuration file name.
     * @param string $moduleName Module name.
     * @return string|bool
     */
    private function getConfigPath($configFilename, $moduleName) {
        $file = false;
        if ($configFilename && !file_exists($file = $configFilename)) {
            //Смотрим в директории текущего сайта с пользовательскими конфигами
            if (!file_exists($file = sprintf(SITE_DIR . self::SITE_CONFIG_DIR . $configFilename, E()->getSiteManager()->getCurrentSite()->folder))) {
                if (!file_exists($file = sprintf(CORE_DIR . self::CORE_CONFIG_DIR, $moduleName) . $configFilename)) {
                    //если файла с указанным именем нет ни в папке с пользовательскими конфигами, ни в папке модуля с конфигами
                    //throw new SystemException('ERR_DEV_NO_CONFIG', SystemException::ERR_DEVELOPER, $configFilename);
                    $file = false;
                }
            }
        }
        return $file;
    }

    /**
     * Set current state.
     *
     * @param string $methodName Method name.
     *
     * @throws SystemException 'ERR_NO_METHOD '
     */
    public function setCurrentState($methodName) {
        if (!($this->currentState = $this->getStateConfig($methodName))) {
            //inspect($this->c, $this->config->xpath(sprintf('/configuration/state[@name=\'%s\']', $methodName)));
            throw new SystemException('ERR_NO_METHOD ' . $methodName, SystemException::ERR_DEVELOPER, $methodName);
        }
    }

    /**
     * Register state.
     *
     * @param string $methodName Method name.
     * @param array|string $patterns Set of patterns.
     * @param bool|int $rights Rights.
     */
    protected function registerState($methodName, $patterns, $rights = false) {
        if ($this->config) {
            $newState = $this->config->addChild('state');
            $newState->addAttribute('name', $methodName);
            $newState->addAttribute('weight', true);
            if ($rights) {
                $newState->addAttribute('rights', $rights);
            }
            if (!is_array($patterns)) $patterns = array($patterns);
            $uriPatterns = $newState->addChild('uri_patterns');
            foreach ($patterns as $pattern) {
                $uriPatterns->addChild('pattern', $pattern);
            }
        }
    }

    /**
     * Get configuration of current state.
     *
     * @return \SimpleXMLElement
     */
    public function getCurrentStateConfig() {
        return $this->currentState;
    }

    /**
     * Check if configuration is empty.
     *
     * @return boolean
     */
    public function isEmpty() {
        return ($this->config) ? false : true;
    }

    /**
     * Get action name from configuration.
     * It based on URI request.
     *
     * @param string $path URI path.
     * @return array
     */
    public function getActionByURI($path) {
        $actionName = false;
        $actionParams = array();
        $path = '/' . $path;

        $patterns = array();
        //счетчик приоритета паттерна
        $weightInc = $maxWeightInc = sizeof($this->config->state);

        //Дальше идет что то мутное
        //наша цель расставиьт приоритеты паттернов состояний
        foreach ($this->config->state as $method) {
            if (isset($method->uri_patterns->pattern)) {

                $weightInc--;
                //А это счетчик приоритета внутри набора паттернов
                $maxPatternPriority = sizeof($method->uri_patterns->pattern) - 1;
                //вообщем в результате мы получаем что у нас приоритеты выставлены как нужно
                //то есть чем выше - тем важнее
                foreach ($method->uri_patterns->pattern as $pattern) {
                    $patterns[(string)$pattern] = array(
                        'method' => (string)$method['name'],
                        //'rights' => ((isset($method['rights']))?(int)$method['rights']),
                        'weight' => ((!isset($method['weight'])) ? ($weightInc + $maxPatternPriority * 0.1) : ($maxWeightInc++))
                    );
                    $maxPatternPriority--;
                }
            }
        }
        //сортируем  по приоритету
        uasort($patterns, function ($a, $b) {
            return $a['weight'] < $b['weight'];
        });

        foreach ($patterns as $pattern => $methodInfo) {
            $methodName = $methodInfo['method'];
            try {
                $resPattern = preg_replace(
                    array('/\[any\]\//', '/\//', '/\[[a-zA-Z_]+\]/'),
                    array('(.*)', '\/', '([^\/]+)'),
                    $pattern
                );
            } catch (Exception $e) {
                $resPattern = $pattern;
            }
            $matches = array();

            if (preg_match($resPattern = "/^$resPattern$/", $path, $matches)) {
                $useSegments = sizeof(array_filter(explode('/', $pattern)));
                if ($useSegments) {
                    if (strpos($pattern, '[any]') !== false) {
                        $useSegments--;
                    }
                    E()->getRequest()->useSegments(E()->getRequest()->getUsedSegments() + $useSegments);
                }
                array_shift($matches);
                $actionName = $methodName;
                if (!empty($matches)) {
                    preg_match($resPattern, $pattern, $varNames);
                    array_shift($varNames);
                    $varNames = str_replace(array('[', ']'), '', $varNames);
                    $actionParams = array_combine($varNames, $matches);
                }

                break;
            }
        }

        if ($actionName == false) {
            return false;
        }

        return array('name' => $actionName, 'params' => $actionParams);
    }

    /**
     * Get state configuration for specific state.
     *
     * @param string $methodName Method name.
     * @return \SimpleXMLElement|false
     */
    public function getStateConfig($methodName) {
        $result = false;
        if (!$this->isEmpty()) {
            $methodConfig = $this->config->xpath(sprintf('state[@name=\'%s\'][parent::configuration]', $methodName));
            if (!empty($methodConfig)) {
                $result = $methodConfig[0];
            }
        }
        return $result;
    }

    /**
     * Get list of current state parameters from configuration file.
     *
     * @return array|bool
     */
    public function getCurrentStateParams() {
        $result = false;
        //Если уже задано текущее состояние
        // и конфиг не пуст
        //и в нем есть узел параметров
        //и в этом узле есть дочерние
        if ($this->currentState && !$this->isEmpty() && isset($this->currentState->params) && sizeof($this->currentState->params->children())) {
            $result = array();
            foreach ($this->currentState->params->param as $tagName => $param) {
                if (($tagName == 'param') && isset($param['name'])) {
                    $result[(string)$param['name']] = (string)$param;
                }
            }
        }
        return $result;
    }
}

