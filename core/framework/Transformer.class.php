<?php

/**
 * Класс Transformer.
 *
 * @package energine
 * @subpackage core
 * @author dr.Pavka
 * @copyright Energine 2006
 * @version $Id$
 */

//require_once('core/framework/SystemConfig.class.php');

/**
 * Трансформер XML-документа страницы.
 *
 * @package energine
 * @subpackage core
 * @author dr.Pavka
 */
class Transformer extends Object {

	/**
	 * Директория, где находится основной трансформер
	 */
	const MAIN_TRANSFORMER_DIR = 'site/transformers/';

	private $fileName;

	/**
	 * Конструктор класса.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {
		parent::__construct();
		$this->setFileName($this->getConfigValue('document.transformer'));
	}
	/**
	 * Устанавливает имя основного файла трансформации
	 *
	 * @param string
	 * @return void
	 */
	public function setFileName($transformerFilename){
		$transformerFilename = self::MAIN_TRANSFORMER_DIR.$transformerFilename;
		if (!file_exists($transformerFilename)) {
			throw new SystemException('ERR_DEV_NO_MAIN_TRANSFORMER', SystemException::ERR_DEVELOPER, $transformerFilename);
		}
		$this->fileName = $transformerFilename;
	}

	/**
	 * Трансформирует XML-документ страницы в выходной формат.
	 *
	 * @param DOMDocument
	 * @param string
	 * @return string
	 * @access public
	 */
	public function transform($document, $transformerFilename = null) {
		if (!is_null($transformerFilename)) {
			$this->setFileName($transformerFilename);
		}
		//При наличии модуля xslcache http://code.nytimes.com/projects/xslcache
		//используем его
		if(extension_loaded('xslcache') && ($this->getConfigValue('document.xslcache') == 1)){
			$xsltProc = new xsltCache;
			//есть одна проблема с ним
			//при неправильном xslt - сваливается в корку с 500 ошибкой
		    $xsltProc->importStyleSheet($this->fileName);
			$result =  $xsltProc->transformToXML($document);
		}
		else {
			$xsltProc = new XSLTProcessor;
			$xsltDoc = new DOMDocument('1.0', 'UTF-8');
			if (!@$xsltDoc->load($this->fileName)) {
				throw new SystemException('ERR_DEV_NOT_WELL_FORMED_XSLT', SystemException::ERR_DEVELOPER);
			}
			$xsltProc->importStylesheet($xsltDoc);
			$result = $xsltProc->transformToXml($document);
		}
		return $result;
	}
}
