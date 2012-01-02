<?php
/**
 * Класс IRQ
 *
 * @package energine
 * @subpackage kernel
 * @author d.pavka@gmail.com
 * @copyright Energine 2010
 */

/**
 * Фейковый Exception відвигающийся для прерывания нормального течения программы
 * и возврата документа содержащего структуру страницы
 *  
 */
class IRQ extends Exception {
    /**
     * @var SimpleXMLElement | bool
     */
    private $contentBlock = false;
    /**
     * @var SimpleXMLElement | bool
     */
    private $layoutBlock = false;

    /**
     * @param SimpleXMLElement $block
     * @return void
     */
    public function addBlock(SimpleXMLElement $block) {
        if($block->getName() == 'page'){
            $this->layoutBlock = $block;
        }
        else {
            $this->contentBlock = $block;
        }
    }
    /**
     * @return bool|SimpleXMLElement
     */
    public function getContentBlock(){
        return $this->contentBlock;
    }
    /**
     * @return bool|SimpleXMLElement
     */
    public function getLayoutBlock(){
        return $this->layoutBlock;
    }

}
