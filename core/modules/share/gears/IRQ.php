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
     * @var \SimpleXMLElement|bool $contentBlock
     */
    private $contentBlock = false;
    /**
     * Layout block.
     * @var \SimpleXMLElement|bool $layoutBlock
     */
    private $layoutBlock = false;

    /**
     * Add block.
     * @param \SimpleXMLElement $block Block.
     */
    public function addBlock(\SimpleXMLElement $block) {
        if($block->getName() == 'page'){
            $this->layoutBlock = $block;
        }
        else {
            $this->contentBlock = $block;
        }
    }

    /**
     * Get content block.
     *
     * @return bool|\SimpleXMLElement
     */
    public function getContentBlock(){
        return $this->contentBlock;
    }

    /**
     * Get layout block.
     *
     * @return bool|\SimpleXMLElement
     */
    public function getLayoutBlock(){
        return $this->layoutBlock;
    }

}
