<?php

/**
 * Содержит класс ComponentConfig
 *
 * @package energine
 * @subpackage core
 * @author dr.Pavka
 * @copyright ColoCall 2007
 * @version $Id$
 */

//require_once('core/framework/Object.class.php');
//require_once('core/framework/ConfigElement.class.php');
/**
 * Класс реализующий работу с конфигурационным файлом компонента
 *
 * @package energine
 * @subpackage core
 * @author dr.Pavka
 * @final
 */
final class ComponentConfig extends Object {
    /**
     * Путь к директории, содержащей пользовательские файлы конфигурации для компонентов
     */
    const SITE_CONFIG_DIR = 'site/config/';

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
     * Имя текущего метода
     *
     * @var ConfigElement
     * @access private
     */
    private $currentMethod = false;

    /**
     * Конструктор класса
     *
     * @param string имя конфигурационного файла
     * @return void
     */

    public function __construct($configFileName, $className, $moduleName) {
        parent::__construct();
        $configFileName = ($param = $this->getConfigPath($configFileName, $moduleName))?$param:$this->getConfigPath($className.'.component.xml', $moduleName);

        if ($configFileName) {
            try {
                $this->config = simplexml_load_file($configFileName, 'ConfigElement');
            }
            catch (Exception  $e) {
                throw new SystemException('ERR_DEV_BAD_CONFIG_FILE', SystemException::ERR_DEVELOPER, $configFileName);
            }
        }
    }

    /**
     * Возвращает полный путь к конфигурационному файлу, или false если файл не существует.
     *
     * @access private
     * @param string $configFilename имя конфигурационного файла
     * @return mixed
     */
    private function getConfigPath($configFilename, $moduleName) {
        $file = false;
        if ($configFilename && !file_exists($file = $configFilename))
        //Смотрим в директории с пользовательскими конфигами
        if (!file_exists($file = self::SITE_CONFIG_DIR.$configFilename)) {
            if(!file_exists($file = sprintf(self::CORE_CONFIG_DIR, $moduleName).$configFilename)){
            //если файла с указанным именем нет ни в папке с пользовательскими конфигами, ни в папке модуля с конфигами
        	//throw new SystemException('ERR_DEV_NO_CONFIG', SystemException::ERR_DEVELOPER, $configFilename);
        	$file = false;
            }
        }
        return $file;
    }
    /**
     * Устанавливает имя текущего метода
     *
     * @param string имя метода
     * @return void
     * @access public
     */

    public function setCurrentMethod($methodName) {
        if(!($this->currentMethod = $this->getMethodConfig($methodName))){
            throw new SystemException('ERR_NO_METHOD', SystemException::ERR_DEVELOPER);
        }
    }

    /**
     * Возвращает конфигурацию текущего метода
     *
     * @return ConfigElement
     * @access public
     */

    public function getCurrentMethodConfig() {
        return $this->currentMethod;
    }

    /**
     * Возвращает флаг того, что конфиг пустой
     *
     * @return boolean
     * @access public
     */

    public function isEmpty() {
        return ($this->config)?false:true;
    }

    /**
     * Возвращает имя действия из конфигурации, основываясь на URI запроса.
     *
     * @access public
     * @return array
     */
    public function getActionByURI($path) {
        $actionName = false;
        $actionParams = array();
        $path = '/'.$path;

        $patterns = array();
        foreach ($this->config->methods->method as $method) {
            if (isset($method->uri_patterns->pattern)) {
                foreach ($method->uri_patterns->pattern as $pattern) {
                    $patterns[$pattern->getValue()] = $method->getAttribute('name');
                }
            }
        }

        // сортируем шаблоны URI от более специфичных к менее специфичным
        //uksort($patterns,array('ComponentConfig', 'uriPatternsCmp'));
        /**
         * @todo Нужно заменить на uksort когда будет ликвидирован глюк с segfault
         */
        $patterns = $this->sortByKeys($patterns, array('ComponentConfig','uriPatternsCmp'));

        foreach ($patterns as $pattern => $methodName) {
            $regexpr = str_replace(
                array('/',  '[int]', '[string]', '[any]\/', '[any]'),
                array('\/', '(\d+)', '([^\/]+)', '(.*)',    '(.*)'),
                $pattern
            );
            if (preg_match("/^$regexpr$/", $path, $matches)) {
                array_shift($matches);
                if (strpos($pattern, '[any]') !== false) {
                    array_pop($matches);
                }
                $actionName = $methodName;
                $actionParams = $matches;
                //inspect($this->getName());
                //$this->request->setPathOffset($this->request->getPathOffset()+sizeof($actionParams));
                break;
            }
        }

        if ($actionName == false) {
            return false;
        }

        return array('name' => $actionName, 'params' => $actionParams);
    }

    /**
     * Возвращает конфигурацию для указанного метода.
     *
     * @access public
     * @param string $methodName имя метода
     * @return SimpleXMLElement
     */
    public function getMethodConfig($methodName) {
        $result = false;
        if (!$this->isEmpty()) {
            $methodConfig = $this->config->xpath(sprintf('/configuration/methods/method[@name=\'%s\']', $methodName));
            if (!empty($methodConfig)) {
                $result = $methodConfig[0];
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

    static private function uriPatternsCmp($patternA, $patternB) {
        $placeholders = array('/[int]/', '/[string]/', '/[any]/');
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

    /**
     * Аналог uksort
     *
     * @return array
     * @access public
     */

    public function sortByKeys($data, $callback) {
        $result = array();
        //Получаем исходное количество элементов в массиве
        $arrayLength = sizeof($data);

    	//до тех пор пока размер результирующего массива меньше размера исходного масива
    	while (sizeof($result)<$arrayLength){
    		$currentElement = array(key($data) => current($data));
    		do {
    			if (($haveNext = next($data)) && (call_user_func($callback, key($currentElement), key($data))<0)){
    				$currentElement = array(key($data) => current($data));
    			}
    		}
    		while($haveNext);

    		$result = array_merge($currentElement, $result);
    		unset($data[key($currentElement)]);
            reset($data);
    	}
        return $result;
    }
}
