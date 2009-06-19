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
		$xsltProc = new XSLTProcessor;
		if (!is_null($transformerFilename)) {
			$this->setFileName($transformerFilename);
		}
		$xsltDoc = new DOMDocument('1.0', 'UTF-8');
		if (!@$xsltDoc->load($this->fileName)) {
			throw new SystemException('ERR_DEV_NOT_WELL_FORMED_XSLT', SystemException::ERR_DEVELOPER);
		}
		$xsltProc->importStylesheet($xsltDoc);
		$result = $xsltProc->transformToXml($document);
		return $result;
	}
}
