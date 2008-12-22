<?php

/**
 * Класс Transformer.
 *
 * @package energine
 * @subpackage core
 * @author dr.Pavka
 * @copyright ColoCall 2006
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

    /**
     * Конструктор класса.
     *
     * @access public
     * @return void
     */
	public function __construct() {
		parent::__construct();
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
        if (!isset($transformerFilename)) {
            $transformerFilename = $this->getConfigValue('document.transformer');
        }
        if (empty($transformerFilename)) {
        	throw new SystemException('ERR_DEV_NO_TRANSFORMER', SystemException::ERR_DEVELOPER);
        }
        $transformerFilename = self::MAIN_TRANSFORMER_DIR.$transformerFilename;
        if (!file_exists($transformerFilename)) {
            throw new SystemException('ERR_DEV_NO_MAIN_TRANSFORMER', SystemException::ERR_DEVELOPER, $transformerFilename);
        }
        $xsltDoc = new DOMDocument('1.0', 'UTF-8');
        if (!@$xsltDoc->load($transformerFilename)) {
            throw new SystemException('ERR_DEV_NOT_WELL_FORMED_XSLT', SystemException::ERR_DEVELOPER);
        }
        $xsltProc->importStylesheet($xsltDoc);
        $result = $xsltProc->transformToXml($document);
        return $result;
	}
}
