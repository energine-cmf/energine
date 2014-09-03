<?php
/**
 * @file
 * XMLTransformer.
 *
 * It contains the definition to:
 * @code
class XMLTransformer;
@endcode
 *
 * @author pavka
 * @copyright Energine 2010
 *
 * @version 1.0.0
 */
namespace Energine\share\gears;
/**
 * XML Transformer.
 *
 * @code
class XMLTransformer;
@endcode
 */
class XMLTransformer implements ITransformer{
    /**
     * Document.
     * @var \DOMDocument $document
     */
    private $document;

    public function transform() {
        E()->getResponse()->setHeader('Content-Type', 'text/xml; charset=UTF-8');
        return trim($this->document->saveXML());
    }

    public function setDocument(\DOMDocument $document){
        $this->document = $document;
    }

    //todo VZ: remove that comment?
    public function setFileName($transformerFilename, $isAbsolutePath = false) {
       //throw new SystemException('ERR_UNIMPLEMENTED', SystemException::ERR_DEVELOPER);
    }
}


