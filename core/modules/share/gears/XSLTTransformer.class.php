<?php
/**
 * @file
 * XSLTTransformer.
 *
 * It contains the definition to:
 * @code
class XSLTTransformer;
@endcode
 *
 * @author dr.Pavka
 * @copyright Energine 2006
 *
 * @version 1.0.0
 */
namespace Energine\share\gears;
/**
 * XSLT transformer.
 *
 * @code
class XSLTTransformer;
@endcode
 */
class XSLTTransformer extends Object implements ITransformer {
    /**
     * Directory where the main transform file is stored.
     * @var string MAIN_TRANSFORMER_DIR
     */
    const MAIN_TRANSFORMER_DIR = '/modules/%s/transformers/';

    /**
     * XSLT-filename.
     * @var string $fileName
     */
    private $fileName;

    /**
     * Document.
     * @var \DOMDocument $document
     */
    private $document;

    public function __construct() {
        $this->setFileName($this->getConfigValue('document.transformer'));
    }

    /**
     * @copydoc ITransformer::setFileName
     *
     * @throws SystemException 'ERR_DEV_NO_MAIN_TRANSFORMER'
     */
    public function setFileName($transformerFilename, $isAbsolutePath = false) {
        if (!$isAbsolutePath)
            $transformerFilename =
                    sprintf(SITE_DIR.self::MAIN_TRANSFORMER_DIR, E()->getSiteManager()->getCurrentSite()->folder) .
                            $transformerFilename;
        if (!file_exists($transformerFilename)) {
            throw new SystemException('ERR_DEV_NO_MAIN_TRANSFORMER', SystemException::ERR_DEVELOPER, $transformerFilename);
        }
        $this->fileName = $transformerFilename;
    }

    public function setDocument(\DOMDocument $document) {
        $this->document = $document;
    }

    //todo VZ: Where is xsltCache?
    /**
     * @copydoc ITransformer::transform
     *
     * @throws SystemException 'ERR_DEV_NOT_WELL_FORMED_XSLT'
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
            $xsltProc = new \XSLTProcessor;
            $xsltDoc = new \DOMDocument('1.0', 'UTF-8');
            if (!@$xsltDoc->load($this->fileName)) {
                throw new SystemException('ERR_DEV_NOT_WELL_FORMED_XSLT', SystemException::ERR_DEVELOPER);
            }
            $xsltDoc->documentURI = $this->fileName;
            $xsltProc->importStylesheet($xsltDoc);
            $result = $xsltProc->transformToXml($this->document);
        }
        E()->getResponse()->setHeader('Content-Type', 'text/html; charset=UTF-8', false);
        return $result;
    }
}


