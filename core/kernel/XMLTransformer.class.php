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
     * @param  $dummy
     * @return void
     */
    public function setFileName($dummy){
        
    }
}
