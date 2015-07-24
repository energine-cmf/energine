<?php
/**
 * @file
 * ComponentManager, IBlock
 *
 * It contains the definition to:
 * @code
final class ComponentManager;
 * interface IBlock;
 * @endcode
 *
 * @author dr.Pavka
 * @copyright Energine 2006
 *
 * @version 1.0.0
 */
namespace Energine\share\gears;

use Energine\share\components\Component;

/**
 * Block interface.

 * @code
interface IBlock;
 * @endcode
 */
interface IBlock {
    /**
     * Run execution.
     * @return void
     */
    public function run();

    /**
     * Is enabled?
     * @return bool
     */
    public function enabled();

    /**
     * Get current rights level of the user.
     * This is needed for running current action.
     * @return mixed
     */
    public function getCurrentStateRights();

    /**
     * Build block.
     * @return \DOMDocument
     */
    public function build();

    /**
     * Get name.
     * @return string
     */
    public function getName();

}

/**
 * Manager of the set of the document's components.

 * @code
final class ComponentManager;
 * @endcode
 * @final
 */
final class ComponentManager extends Object implements \Iterator {

    /**
     * Document.
     * @var Document $document
     */
    static private $document;
    /**
     * Set of components.
     * This set is used to quick find the component by ComponentManager::getComponentByName.
     * It is filled by adding an component in the stream.
     * @var array $registeredBlocks
     */
    private $registeredBlocks = [];
    /**
     * Array of blocks (IBlock).
     * It can contain components and containers.
     * @var array $blocks
     */
    private $blocks = [];
    /**
     * Array of block names.
     * This used for increasing the iterations.
     * It is filled by ComponentManager::rewind method.
     * @var array $blockNames
     */
    private $blockNames = [];
    /**
     * Iterator index.
     * @var int $iteratorIndex
     */
    private $iteratorIndex = 0;

    /**
     * @param Document $document Document.
     */
    public function __construct(Document $document) {
        self::$document = $document;
    }

    /**
     * Find block in the component XML description by his name.
     * @param \SimpleXMLElement $containerXMLDescription Component descriptions.
     * @param string $blockName Block name.
     * @return \SimpleXMLElement|bool
     */
    static public function findBlockByName(\SimpleXMLElement $containerXMLDescription, $blockName) {
        $blocks = $containerXMLDescription->xpath(
            'descendant-or-self::*[name()="container" or name() = "component"]' .
            '[@name="' . $blockName . '"]'
        );
        if (!empty($blocks)) {
            list($blocks) = $blocks;
        } else {
            $blocks = false;
        }

        return $blocks;
    }

    /**
     * Load the component description from the file.
     * @param string $blockDescriptionFileName File name.
     * @return \SimpleXMLElement
     * @throws SystemException ERR_DEV_NO_CONTAINER_FILE
     * @throws SystemException ERR_DEV_BAD_CONTAINER_FILE
     */
    static public function getDescriptionFromFile($blockDescriptionFileName) {
        if (!file_exists($blockDescriptionFileName)) {
            throw new SystemException('ERR_DEV_NO_CONTAINER_FILE', SystemException::ERR_CRITICAL,
                $blockDescriptionFileName);
        }
        if (!($blockDescription = simplexml_load_file($blockDescriptionFileName))) {
            throw new SystemException('ERR_DEV_BAD_CONTAINER_FILE', SystemException::ERR_CRITICAL,
                $blockDescriptionFileName);
        }

        return $blockDescription;
    }

    /**
     * Create block from description.
     *
     * @param \SimpleXMLElement $blockDescription Block description.
     * @param array $additionalProps Additional properties.
     * @return IBlock
     *
     */
    static public function createBlockFromDescription(\SimpleXMLElement $blockDescription, $additionalProps = []) {
        switch ($blockDescription->getName()) {
            case 'component':
                $result = Component::createFromDescription($blockDescription, $additionalProps);
                break;
            default:
                $result = ComponentContainer::createFromDescription($blockDescription, $additionalProps);
                break;
        }

        return $result;
    }

    /**
     * Add new IBlock to the ComponentManager::$registeredBlocks.
     * @param IBlock $block New block.
     */
    public function register(IBlock $block) {
        $this->registeredBlocks[$block->getName()] = $block;
    }

    /**
     * Add component.
     * @param Component $component
     * @deprecated С поялением концепции блоков нужно использовать ComponentManager::add
     */
    public function addComponent(Component $component) {
        $this->add($component);
    }

    /**
     * Add new IBlock to the ComponentManager::$blocks.
     * @param IBlock $block New block.
     */
    public function add(IBlock $block) {
        $this->blocks[$block->getName()] = $block;
    }

    /**
     * Get the block by his name.
     * @param string $name Block name.
     * @return Component
     */
    public function getBlockByName($name) {
        $result = null;
        if (isset($this->registeredBlocks[$name])) {
            $result = $this->registeredBlocks[$name];
        }

        return $result;
    }

    /**
     * Create component.
     * @param string $name Component name.
     * @param string $class Component class.
     * @param array $params Component properties.
     * @return Component
     */
    public function createComponent($name, $class, $params = null) {
        return call_user_func_array(['Energine\\share\\components\\Component', 'create'], func_get_args());
    }

    public function rewind() {
        $this->blockNames = array_keys($this->blocks);
        $this->iteratorIndex = 0;
    }

    public function valid() {
        return isset($this->blockNames[$this->iteratorIndex]);
    }

    public function key() {
        return $this->blockNames[$this->iteratorIndex];
    }

    public function next() {
        $this->iteratorIndex++;
    }

    public function current() {
        return $this->blocks[$this->blockNames[$this->iteratorIndex]];
    }
}