<?php

/**
 * Класс Transformer
 *
 * @package energine
 * @subpackage core
 * @author dr.Pavka
 * @copyright Energine 2006

 */

/**
 * Трансформер XML-документа страницы.
 *
 * @package energine
 * @subpackage core
 * @author dr.Pavka
 */
class XSLTTransformer extends Object implements ITransformer {

    /**
     * Директория, где находится основной трансформер
     */
    const MAIN_TRANSFORMER_DIR = 'site/modules/%s/transformers/';

    /**
     * Имя файла трансформации
     * @var string
     */
    private $fileName;

    /**
     * Документ
     * @var DOMDocument
     */
    private $document;

    /**
     * Конструктор класса.
     *
     * @access public
     * @return void
     */
    public function __construct() {
        $this->setFileName($this->getConfigValue('document.transformer'));
    }

    /**
     * Устанавливает имя основного файла трансформации
     *
     * @param string
     * @param bool флаг указывающий на что путь абсолютный
     * @return void
     */
    public function setFileName($transformerFilename, $isAbsolutePath = false) {
        if (!$isAbsolutePath)
            $transformerFilename =
                    sprintf(self::MAIN_TRANSFORMER_DIR, SiteManager::getInstance()->getCurrentSite()->folder) .
                            $transformerFilename;
        if (!file_exists($transformerFilename)) {
            throw new SystemException('ERR_DEV_NO_MAIN_TRANSFORMER', SystemException::ERR_DEVELOPER, $transformerFilename);
        }
        $this->fileName = $transformerFilename;
    }

    public function setDocument(DOMDocument $document) {
        $this->document = $document;
    }

    /**
     * Трансформирует XML-документ страницы в выходной формат.
     *
     * @param DOMDocument
     * @param string
     * @return string
     * @access public
     */
    public function transform() {
        //При наличии модуля xslcache http://code.nytimes.com/projects/xslcache
        //используем его
        if (extension_loaded('xslcache') &&
                ($this->getConfigValue('document.xslcache') == 1)) {
            $xsltProc = new xsltCache;
            //есть одна проблема с ним
            //при неправильном xslt - сваливается в корку с 500 ошибкой
            $xsltProc->importStyleSheet($this->fileName);
            $result = $xsltProc->transformToXML($this->document);
        }
        else {
            $xsltProc = new XSLTProcessor;
            $xsltDoc = new DOMDocument('1.0', 'UTF-8');
            if (!@$xsltDoc->load($this->fileName)) {
                throw new SystemException('ERR_DEV_NOT_WELL_FORMED_XSLT', SystemException::ERR_DEVELOPER);
            }
            if ($this->getConfigValue('document.xslprofile') == 1) {
                $xsltProc->setProfiling('logs/xslt_profile.log');
            }
            $xsltProc->importStylesheet($xsltDoc);
            $result = $xsltProc->transformToXml($this->document);
        }
        E()->getResponse()->setHeader('Content-Type', 'text/html; charset=UTF-8', false);
        return $result;
    }
}

