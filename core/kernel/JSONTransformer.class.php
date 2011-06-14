<?php
/**
 * Класс JSONTransformer
 *
 * @package energine
 * @subpackage core
 * @author pavka
 * @copyright Energine 2010
 */
 
class JSONTransformer implements ITransformer{
    /**
     * @var DOMDocument
     */
    private $document;
    /**
     * @param DOMDocument $document
     * @return void
     */
    public function setDocument(DOMDocument $document){
        $this->document = $document;    
    }

    public function transform() {
        E()->getResponse()->setHeader('Content-Type', 'text/javascript; charset=utf-8');
        $component = $this->document->getElementById('result');
        return $component->nodeValue;
    }
}
