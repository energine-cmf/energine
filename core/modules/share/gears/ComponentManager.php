<?php
/**
 * @file
 * ComponentManager, IBlock
 *
 * It contains the definition to:
 * @code
final class ComponentManager;
interface IBlock;
@endcode
 *
 * @author dr.Pavka
 * @copyright Energine 2006
 *
 * @version 1.0.0
 */
namespace Energine\share\gears;

/**
 * Manager of the set of the document's components.
 *
 * @code
final class ComponentManager;
@endcode
 *
 * @final
 */
final class ComponentManager extends Object implements \Iterator {

    /**
     * Set of components.
     * This set is used to quick find the component by ComponentManager::getComponentByName.
     * It is filled by adding an component in the stream.
     *
     * @var array $registeredBlocks
     */
    private $registeredBlocks = array();

    /**
     * Document.
     * @var Document $document
     */
    static private $document;

    /**
     * Array of blocks (IBlock).
     * It can contain components and containers.
     *
     * @var array $blocks
     */
    private $blocks = array();
    /**
     * Array of block names.
     * This used for increasing the iterations.
     * It is filled by ComponentManager::rewind method.
     *
     * @var array $blockNames
     */
    private $blockNames = array();
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
     * Add new IBlock to the ComponentManager::$registeredBlocks.
     *
     * @param IBlock $block New block.
     */
    public function register(IBlock $block) {
        $this->registeredBlocks[$block->getName()]  = $block;
    }

    /**
     * Add new IBlock to the ComponentManager::$blocks.
     *
     * @param IBlock $block New block.
     */
    public function add(IBlock $block) {
        $this->blocks[$block->getName()] = $block;
        /*
                $iterateContainer = function(Block $block) use(&$iterateContainer) {
                    $result = array();
                    if ($block instanceof ComponentContainer) {
                        foreach ($block as $blockChildName => $blockChild) {
                            $result[$blockChildName] = $blockChild;
                            $result = array_merge($result, $iterateContainer($blockChild));
                        }
                    }
                    else {
                         $result[$block->getName()] = $block;
                    }
                    return $result;
                };

                $this->blockCache = array_merge($this->blockCache, $iterateContainer($block));
         *
         */
    }


    /**
     * Add component.
     *
     * @param Component $component
     *
     * @deprecated С поялением концепции блоков нужно использовать ComponentManager::add
     */
    public function addComponent(Component $component) {
        $this->add($component);
    }

    /**
     * Get the block by his name.
     *
     * @param string $name Block name.
     * @return Component
     */
    public function getBlockByName($name) {
        $result = false;
        if (isset($this->registeredBlocks[$name])) {
            $result = $this->registeredBlocks[$name];
        }
        return $result;
    }

    /**
     * Create component from XML description.
     *
     * @param \SimpleXMLElement $componentDescription Component description.
     * @return Component
     *
     * @throws SystemException ERR_DEV_NO_REQUIRED_ATTRIB [attribute_name]
     */
    static public function createComponentFromDescription(\SimpleXMLElement $componentDescription) {
        // перечень необходимых атрибутов компонента
        $requiredAttributes = array('name', 'module', 'class');

        $name = $class = $module = null;
        //после отработки итератора должны получить $name, $module, $class
        foreach ($requiredAttributes as $attrName) {
            if (!isset($componentDescription[$attrName])) {
                throw new SystemException("ERR_DEV_NO_REQUIRED_ATTRIB $attrName", SystemException::ERR_DEVELOPER);
            }
            $$attrName = (string) $componentDescription[$attrName];
        }


        // извлекаем параметры компонента
        $params = null;
        if (isset($componentDescription->params)) {
            $params = array();
            foreach ($componentDescription->params->param as $tagName => $paramDescr) {
                if ($tagName == 'param') {
                    if (isset($paramDescr['name'])) {
                        $paramName = (string) $paramDescr['name'];
                        //Если count больше ноля значит это вложенный SimpleXML елемент
                        if(!$paramDescr->count()){
                            $paramValue = (string)$paramDescr;
                        }
                        else {
                            list($paramValue) = $paramDescr->children();
                        }

                        //$paramValue = (string) $paramDescr;

                        //Если в массиве параметров уже существует параметр с таким именем, превращаем этот параметр в массив
                        if (isset($params[$paramName])) {
                            if (!is_array($params[$paramName])) {
                                $params[$paramName] =
                                    array($params[$paramName]);
                            }
                            array_push($params[$paramName], $paramValue);
                        }
                        else {
                            $params[$paramName] = $paramValue;
                        }
                    }
                }
            }
        }
        /*        $result = false;
                if(
                    !isset($params['rights'])
                    ||
                    (isset($params['rights']) && self::$document->getRights() >= $params['rights'])
                ) {
                    $result = self::_createComponent($name, $module, $class, $params);
                }*/

        $result = self::_createComponent($name, $module, $class, $params);

        return $result;
    }

    /**
     * Create component.
     *
     * @param string $name Component name.
     * @param string $module Component module name.
     * @param string $class Component class.
     * @param array $params Component properties.
     * @return Component
     */
    public function createComponent($name, $module, $class, $params = null) {
        return call_user_func_array(array('Energine\\share\\gears\\ComponentManager', '_createComponent'), func_get_args());
    }

    /**
     * Find block in the component XML description by his name.
     *
     * @param \SimpleXMLElement $containerXMLDescription Component descriptions.
     * @param string $blockName Block name.
     * @return IBlock|bool
     */
    static public function findBlockByName(\SimpleXMLElement $containerXMLDescription, $blockName) {
        $blocks = $containerXMLDescription->xpath(
            'descendant-or-self::*[name()="container" or name() = "component"]' .
            '[@name="' . $blockName . '"]'
        );
        if (!empty($blocks)) {
            list($blocks) = $blocks;
        }
        else {
            $blocks = false;
        }

        return $blocks;
    }

    /**
     * Load the component description from the file.
     *
     * @param string $blockDescriptionFileName File name.
     * @return \SimpleXMLElement
     *
     * @throws SystemException ERR_DEV_NO_CONTAINER_FILE
     * @throws SystemException ERR_DEV_BAD_CONTAINER_FILE
     */
    static public function getDescriptionFromFile($blockDescriptionFileName) {
        if (!file_exists($blockDescriptionFileName)) {
            throw new SystemException('ERR_DEV_NO_CONTAINER_FILE', SystemException::ERR_CRITICAL, $blockDescriptionFileName);
        }
        if (!($blockDescription = simplexml_load_file($blockDescriptionFileName))) {
            throw new SystemException('ERR_DEV_BAD_CONTAINER_FILE', SystemException::ERR_CRITICAL, $blockDescriptionFileName);
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
     * @throws SystemException ERR_UNKNOWN_BLOCKTYPE
     */
    static public function createBlockFromDescription(\SimpleXMLElement $blockDescription, $additionalProps = array()) {
        $result = false;
        switch ($blockDescription->getName()) {
            case 'content':
                $props = array_merge(array('tag' => 'content'), $additionalProps);
                $result = ComponentContainer::createFromDescription($blockDescription, $props);
                break;
            case 'page':
                $props = array_merge(array('tag' => 'layout'), $additionalProps);
                $result = ComponentContainer::createFromDescription($blockDescription, $props);
                break;
            case 'container':
                $result = ComponentContainer::createFromDescription($blockDescription);
                break;
            case 'component':
                $result = self::createComponentFromDescription($blockDescription);
                break;
            default:
                throw new SystemException('ERR_UNKNOWN_BLOCKTYPE', SystemException::ERR_CRITICAL);
                break;
        }

        return $result;
    }

    /**
     * Create component by requested parameters.
     *
     * @param string $name Component name.
     * @param string $module Component module name.
     * @param  $class Component class.
     * @param $params Parameters.
     * @return Component
     *
     * @throws SystemException
     */
    static private function _createComponent($name, $module, $class, $params = null) {
        try {
            $module = explode('/', $module);
            $vendorNS = 'Energine';
            if(sizeof($module) > 1){
                $vendorNS = 'EnergineSite';
            }
            $module = array_pop($module);
            $fqClassName = $vendorNS.'\\'.$module.'\\'.'components'.'\\'.$class;
            $result = new $fqClassName($name, $module, $params);
        }
        catch (SystemException $e) {
            throw new SystemException($e->getMessage(), SystemException::ERR_DEVELOPER, array(
                'class' => (($module !==
                        'site') ? str_replace('*', $module, CORE_COMPONENTS_DIR) :
                        SITE_COMPONENTS_DIR . $module) . '/' . $class .
                    '.php',
                'trace' => $e->getTraceAsString()
            ));
        }
        return $result;
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

/**
 * Block interface.
 *
 * @code
interface IBlock;
@endcode
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