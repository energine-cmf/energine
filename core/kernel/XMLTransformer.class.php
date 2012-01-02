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
     * @var Document
     */
    private $document;

    public function transform() {
        E()->getResponse()->setHeader('Content-Type', 'text/xml; charset=UTF-8');
        return trim($this->document->saveXML());
    }

    public function setDocument(Document $document){
        $this->document = $document->getResult();
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
