<?php
/**
 * @file
 * Tag
 *
 * Contains the definition to:
 * @code
class Tag;
 * @endcode
 *
 * @author dr.Pavka
 * @copyright Energine 2015
 *
 * @version 1.0.0
 */

namespace Energine\share\gears;

/**
 * Tag.php
 *
 * @code
class Tag;
 * @endcode
 */
class Tag extends Object implements IBlock {
    /**
     * Array of IBlock.
     * @var array $blocks
     */
    private $blocks = [];
    private $attributes;
    private $tagName;
    private $name;

    function __construct(\SimpleXMLElement $xmlDescription) {
        $this->attributes = $xmlDescription->attributes();
        $this->name = md5((string)$xmlDescription) . '_name';
        $this->tagName = $xmlDescription->getName();

        foreach ($xmlDescription->children() as $blockDescription) {
            array_push($this->blocks, ComponentManager::createBlockFromDescription($blockDescription));
        }
    }

    static public function createFromDescription(\SimpleXMLElement $XMLDescription) {
        return new Tag($XMLDescription);
    }

    /**
     * Run execution.
     * @return void
     */
    public function run() {
        foreach ($this->blocks as $block) {
            if ($block->enabled()
                && (E()->getDocument()->getRights() >= $block->getCurrentStateRights())
            ) {
                $block->run();
            }
        }
    }

    /**
     * Is enabled?
     * @return bool
     */
    public function enabled() {
        return true;
    }

    /**
     * Get current rights level of the user.
     * This is needed for running current action.
     * @return mixed
     */
    public function getCurrentStateRights() {
        return 0;
    }

    /**
     * Build block.
     * @return \DOMDocument
     */
    public function build() {
        $doc = new \DOMDocument('1.0', 'UTF-8');
        $containerDOM = $doc->createElement($this->tagName);

        $doc->appendChild($containerDOM);
        foreach ($this->attributes as $propertyName => $propertyValue) {
            $containerDOM->setAttribute($propertyName, $propertyValue);
        }

        foreach ($this->blocks as $block) {
            if (
                $block->enabled()
                &&
                (E()->getDocument()->getRights() >= $block->getCurrentStateRights())
            ) {
                $blockDOM = $block->build();
                if ($blockDOM instanceof \DOMDocument) {
                    $blockDOM =
                        $doc->importNode($blockDOM->documentElement, true);
                    $containerDOM->appendChild($blockDOM);
                }
            }
        }

        return $doc;
    }

    /**
     * Get name.
     * @return string
     */
    public function getName() {
        return $this->name;
    }

}  