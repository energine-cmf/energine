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
class PageStructureDocument extends Primitive implements IDocument{
    /**
     * Document
     * @var \DOMDocument $doc
     */
    private $doc;

    private $structure;

    /**
     * Set layout.
     * @param \SimpleXMLElement $structure
     */
    public function setStructure(\SimpleXMLElement $structure) {
        $this->structure = $structure;
    }

    public function getResult() {
        return $this->doc;
    }

    public function build() {
        $this->doc = new \DOMDocument('1.0', 'UTF-8');
        if ($this->structure) {
            $this->doc->appendChild($this->doc->importNode(dom_import_simplexml($this->structure), true));
        }
    }

}
