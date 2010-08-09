<?php

/**
 * Содержит класс ComponentConfig
 *
 * @package energine
 * @subpackage core
 * @author dr.Pavka
 * @copyright Energine 2007
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
	const SITE_CONFIG_DIR = 'site/%s/config/';

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
		if ($configFilename && !file_exists($file = $configFilename)){
			//Смотрим в директории текущего сайта с пользовательскими конфигами
			if (!file_exists($file = sprintf(self::SITE_CONFIG_DIR.$configFilename, SiteManager::getInstance()->getCurrentSite()->folder))) {
				if(!file_exists($file = sprintf(self::CORE_CONFIG_DIR, $moduleName).$configFilename)){
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

		foreach($patterns as $pattern => $methodName){
			$resPattern = $pattern;
			try{
				$resPattern = preg_replace(
				array('/\[any\]\//', '/\//', '/\[[a-zA-Z_]+\]/'),
				array('(.*)', '\/', '([^\/]+)'),
				$pattern
				);
			}
			catch (Exception $e){
				$resPattern = $pattern;
			}
			$matches = array();
			//inspect($resPattern);
			if(preg_match($resPattern = "/^$resPattern$/", $path, $matches)){
				array_shift($matches);
				$actionName = $methodName;
				
				if(!empty($matches)){
					if(strpos($pattern, '[any]') === false){
						preg_match($resPattern, $pattern, $varNames);
						array_shift($varNames);
	    				$varNames = str_replace(array('[',']'), '', $varNames);
						$actionParams = array_combine($varNames, $matches);
					}
					else{
						$actionParams = array('any'=>$matches[0]);
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
	 * Возвращает конфигурацию для указанного метода
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

	/**
	 * Аналог uksor
	 *
	 * @return array
	 * @access public
	 */
	/*
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
	 }*/
}
