<?php
/**
 * @file
 * IRQ.
 *
 * It contains the definition to:
 * @code
class IRQ;
@endcode
 *
 * @author d.pavka@gmail.com
 * @copyright Energine 2010
 *
 * @version 1.0.0
 */
namespace Energine\share\gears;
/**
 * Fake Exception.
 *
 * @code
class IRQ;
@endcode
 *
 * It interrupts the normal flow of the program and returns the document of the page structure.
 */
class IRQ extends \Exception {
    /**
     * Content block.
     * @var \SimpleXMLElement $structure
     */
    private $structure = null;

    /**
     * Get layout block.
     *
     * @return bool|\SimpleXMLElement
     */
    public function getStructure(){
        return $this->structure;
    }

    /**
     * Add block.
     * @param \SimpleXMLElement $block Block.
     */
    public function setStructure(\SimpleXMLElement $block) {
            $this->structure = $block;
    }
}
