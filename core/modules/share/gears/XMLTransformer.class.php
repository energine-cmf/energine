<?php
/**
 * Класс XMLTransformer.
 *
 * @package energine
 * @subpackage kernel
 * @author pavka
 * @copyright Energine 2010
 */

class XMLTransformer implements ITransformer{
    /**
     * @var DOMDocument
     */
    private $document;

    public function transform() {
        E()->getResponse()->setHeader('Content-Type', 'text/xml; charset=UTF-8');
        return trim($this->document->saveXML());
    }

    public function setDocument(DOMDocument $document){
        $this->document = $document;
    }

    /**
     * Введено для совместимти в режиме отладки
     *
     * @param string $transformerFilename
     * @param bool $isAbsolutePath
     * @throws SystemException
     */
    public function setFileName($transformerFilename, $isAbsolutePath = false) {
        throw new SystemException('ERR_UNIMPLEMENTED', SystemException::ERR_DEVELOPER);
    }
}


