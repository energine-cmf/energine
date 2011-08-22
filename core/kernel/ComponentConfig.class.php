<?php
/**
 * Содержит класс ComponentConfig
 *
 * @package energine
 * @subpackage core
 * @author dr.Pavka
 * @copyright Energine 2007
 */

/**
 * Класс реализующий работу с конфигурационным файлом компонента
 *
 * @package energine
 * @subpackage core
 * @author dr.Pavka
 * @final
 */
final class ComponentConfig
{
    /**
     * Путь к директории, содержащей пользовательские файлы конфигурации для компонентов
     */
    const SITE_CONFIG_DIR = 'site/modules/%s/config/';

    /**
     * Путь к директории, содержащей файлы конфигурации для стандартных компонентов
     * (вместо %s будет подставлено имя модуля, содержащего компонент)
     */
    const CORE_CONFIG_DIR = 'core/modules/%s/config/';

    /**
     * Конфигурационный файл
     *
     * @var SimpleXMLElement
     * @access private
     */
    private $config = false;

    /**
     * Конфигурация текущего метода
     *
     * @var SimpleXMLElement
     * @access private
     */
    private $currentState = false;

    /**
     * Инициализирует конфигурацию
     *
     * @param mixed $config конфигурация или имя конфигурационного файла
     * @param string $className
     * @param string $moduleName
     * @return \ComponentConfig
     *
     */
    public function __construct($config, $className, $moduleName)
    {
        //Если это строка(с именем файла) или false
        if (!$config || is_string($config)) {
            $config = ($param = $this->getConfigPath($config, $moduleName)) ? $param
                    : $this->getConfigPath($className . '.component.xml', $moduleName);
            if ($config) {
                try {
                    $this->config = simplexml_load_file($config /*, 'ConfigElement'*/);
                }
                catch (Exception  $e) {
                    throw new SystemException('ERR_DEV_BAD_CONFIG_FILE', SystemException::ERR_DEVELOPER, $config);
                }
            }
        }
            //А может это конфиг из шаблона?
        elseif (is_a($config, 'SimpleXMLElement')) {
            $this->config = $config;
        }
        else {
            //Этого не может быть
            throw new SystemException('ERR_DEV_STRANGE', SystemException::ERR_DEVELOPER, func_get_args());
            //поскольку быть этого не может никогда
        }
    }

    /**
     * Возвращает полный путь к конфигурационному файлу, или false если файл не существует.
     *
     * @param $configFilename
     * @param $moduleName
     * @return bool|string
     */
    private function getConfigPath($configFilename, $moduleName)
    {
        $file = false;
        if ($configFilename && !file_exists($file = $configFilename)) {
            //Смотрим в директории текущего сайта с пользовательскими конфигами
            if (!file_exists($file = sprintf(self::SITE_CONFIG_DIR . $configFilename, E()->getSiteManager()->getCurrentSite()->folder))) {
                if (!file_exists($file = sprintf(self::CORE_CONFIG_DIR, $moduleName) . $configFilename)) {
                    //если файла с указанным именем нет ни в папке с пользовательскими конфигами, ни в папке модуля с конфигами
                    //throw new SystemException('ERR_DEV_NO_CONFIG', SystemException::ERR_DEVELOPER, $configFilename);
                    $file = false;
                }
            }
        }
        return $file;
    }

    /**
     * Устанавливает имя текущего метода
     *
     * @throws SystemException
     * @param $methodName
     * @return void
     */
    public function setCurrentState($methodName)
    {
        if (!($this->currentState = $this->getStateConfig($methodName))) {
            //inspect($this->c, $this->config->xpath(sprintf('/configuration/state[@name=\'%s\']', $methodName)));
            throw new SystemException('ERR_NO_METHOD ' . $methodName, SystemException::ERR_DEVELOPER, $methodName);
        }
    }

    /**
     * Возвращает конфигурацию текущего метода
     *
     * @return ConfigElement
     * @access public
     */
    public function getCurrentStateConfig()
    {
        return $this->currentState;
    }

    /**
     * Возвращает флаг того, что конфиг пустой
     *
     * @return boolean
     * @access public
     */

    public function isEmpty()
    {
        return ($this->config) ? false : true;
    }

    /**
     * Возвращает имя действия из конфигурации, основываясь на URI запроса.
     * @param $path
     * @internal param $string
     * @access public
     * @return array
     */
    public function getActionByURI($path)
    {
        $actionName = false;
        $actionParams = array();
        $path = '/' . $path;

        $patterns = array();
        foreach ($this->config->state as $method) {
            if (isset($method->uri_patterns->pattern)) {
                foreach ($method->uri_patterns->pattern as $pattern) {
                    $patterns[(string)$pattern] = (string)$method['name'];
                }
            }
        }

        foreach ($patterns as $pattern => $methodName) {
            $resPattern = $pattern;
            try {
                $resPattern = preg_replace(
                    array('/\[any\]\//', '/\//', '/\[[a-zA-Z_]+\]/'),
                    array('(.*)', '\/', '([^\/]+)'),
                    $pattern
                );
            }
            catch (Exception $e) {
                $resPattern = $pattern;
            }
            $matches = array();
            //inspect($resPattern);
            if (preg_match($resPattern = "/^$resPattern$/", $path, $matches)) {
                array_shift($matches);
                $actionName = $methodName;

                if (!empty($matches)) {
                    if (strpos($pattern, '[any]') === false) {
                        preg_match($resPattern, $pattern, $varNames);
                        array_shift($varNames);
                        $varNames = str_replace(array('[', ']'), '', $varNames);
                        $actionParams = array_combine($varNames, $matches);
                    }
                    else {
                        $actionParams = array('any' => $matches[0]);
                    }
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
     * Возвращает конфигурацию для указанного состояния
     *
     * @access public
     * @param string $methodName имя метода
     * @return SimpleXMLElement
     */
    public function getStateConfig($methodName)
    {
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
     * Возвращает перечень параметров состояния заданных в конфигурационном файле
     * @return array || bool
     */
    public function getCurrentStateParams(){
        $result = false;
        //Если уже задано текущее состояние
        // и конфиг не пуст
        //и в нем есть узел параметров
        //и в этом узле есть дочерние
        if($this->currentState && !$this->isEmpty() && isset($this->currentState->params) && sizeof($this->currentState->params->children())){
            $result = array();
            foreach($this->currentState->params->param as $tagName=>$param){
                if(($tagName == 'param') && isset($param['name'])){
                    $result[(string)$param['name']] = (string)$param;
                }
            }
        }
        return $result;
    }

    /**
     * Возвращает флаг, указывающий какой из предложенных паттернов более специфичен
     * Вызывается как callback для uksort
     *
     * @access private
     * @param string $patternA
     * @param string $patternB
     * @return int
     * @static
     */

    static private function uriPatternsCmp($patternA, $patternB)
    {
        $placeholders = array('/[int]/', '/[string]/', '/[any]/');
        //inspect($patternA, $patternB);
        if (in_array($patternA, $placeholders)) {
            $result = 1;
        }
        elseif (in_array($patternB, $placeholders)) {
            $result = -1;
        }
        else {
            $result = -(strlen($patternA) - strlen($patternB));
        }

        return $result;
    }

}

