<?php
/**
 * Класс JSONTransformer
 *
 * @package energine
 * @subpackage kernel
 * @author pavka
 * @copyright Energine 2010
 */
 
class JSONTransformer implements ITransformer{
    /**
     * @var Document
     */
    private $document;
    /**
     * @param Document $document
     * @return void
     */
    public function setDocument(Document $document){
        $this->document = $document;    
    }

    public function transform() {
        E()->getResponse()->setHeader('Content-Type', 'text/javascript; charset=utf-8');
        return $this->document->getResult();
    }
}
