<?php
/**
 * Класс PageStructureDocument
 *
 * @package energine
 * @subpackage kernel
 * @author d.pavka@gmail.com
 * @copyright Energine 2010
 */
/**
 * Класс для генерации структуры документа
 */
class PageStructureDocument extends Object implements IDocument{
    /**
     * @var DOMDocument
     */
    private $doc;
    /**
     * @var SimpleXMLElement | bool
     */
    private $layout = false;
    /**
     * @var SimpleXMLElement | bool
     */
    private $content = false;

    public function setLayout(SimpleXMLElement $layout){
        $this->layout = $layout;
    }
    public function setContent(SimpleXMLElement $content){
        $this->content = $content;
    }
    public function getResult() {
        return $this->doc;
    }

    public function build() {
        $this->doc = new DOMDocument('1.0', 'UTF-8');
        if(!$this->layout) {
            $layout = $this->doc->createElement('page');
        }
        else {
            $layout = $this->doc->importNode(dom_import_simplexml($this->layout), true);
        }
        $this->doc->appendChild($layout);
        if($this->content){
            $layout->appendChild($this->doc->importNode(dom_import_simplexml($this->content),true));
        }
    }

}
