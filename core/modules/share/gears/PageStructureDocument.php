<?php
/**
 * @file
 * PageStructureDocument.
 *
 * It contains the definition to:
 * @code
class PageStructureDocument;
@endcode
 *
 * @author d.pavka@gmail.com
 * @copyright Energine 2010
 *
 * @version 1.0.0
 */
namespace Energine\share\gears;
/**
 * Generate document structure.
 *
 * @code
class PageStructureDocument;
@endcode
 */
class PageStructureDocument extends Object implements IDocument{
    /**
     * Document
     * @var \DOMDocument $doc
     */
    private $doc;

    /**
     * Layout.
     * @var \SimpleXMLElement|bool $layout
     */
    private $layout = false;

    /**
     * Content.
     * @var \SimpleXMLElement|bool $content
     */
    private $content = false;

    /**
     * Set layout.
     * @param \SimpleXMLElement $layout Layout.
     */
    public function setLayout(\SimpleXMLElement $layout){
        $this->layout = $layout;
    }

    /**
     * Set content.
     * @param \SimpleXMLElement $content Content.
     */
    public function setContent(\SimpleXMLElement $content){
        $this->content = $content;
    }

    public function getResult() {
        return $this->doc;
    }

    public function build() {
        $this->doc = new \DOMDocument('1.0', 'UTF-8');
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
