<?php
/**
 * Содержит класс Container
 *
 * @package energine
 * @subpackage core
 * @author dr.Pavka
 * @copyright Energine 2010
 */

/**
 * Контейнер компонентов
 *
 * @package energine
 * @subpackage core
 * @author dr.Pavka
 */
class ComponentContainer extends Object implements Block, Iterator{
    /**
     * Свойства контейнера
     *
     * @var array
     */
    private $properties = array();
    /**
     * @var string
     */
    private $name;
    /**
     * @var Block[]
     */
    private $blocks = array();
    /**
     * @var int
     */
    private $iteratorIndex = 0;
    /**
     * @var array
     */
    private $childNames = array();
    /**
     * @var Document
     */
    private $document;
    /**
     * @param  $name string
     * @param Document $document
     * @param  $properties array
     * @return void
     */
    public function __construct($name, Document $document, array $properties = array()){
        parent::__construct();
        $this->name = $name;
        $this->document = $document;

        $this->properties = $properties;
        if(!isset($this->properties['tag'])) {
            $this->properties['tag'] = 'container';
        }
        $this->document->componentManager->register($this);
    }

    public function add(Block $block) {
        $this->blocks[$block->getName()] = $block;
    }
    /**
     * @static
     * @throws SystemException
     * @param SimpleXMLElement $containerDescription
     * @param Document $document
     * @return Container
     */
    static public function createFromDescription(SimpleXMLElement $containerDescription,Document $document, array $additionalAttributes = array()){
        $attributes = $containerDescription->attributes();
        if(in_array($containerDescription->getName(), array('page', 'content'))){
            $properties['name'] = $containerDescription->getName();
        }
        elseif(!isset($attributes['name'])){
            throw new SystemException('ERR_NO_CONTAINER_NAME', SystemException::ERR_DEVELOPER);
        }
        foreach($attributes as $propertyName => $propertyValue) {
            $properties[(string)$propertyName] = (string)$propertyValue;
        }
        $name = $properties['name'];
        unset($properties['name']);
        $properties = array_merge($properties, $additionalAttributes);

        $result = new ComponentContainer($name, $document, $properties);

        foreach($containerDescription->children() as $blockDescription) {
            $result->add(ComponentManager::createBlockFromDescription($blockDescription));
        }

        return $result;
    }

    public function isEmpty(){
        return (boolean)sizeof($this->childs);
    }
    /**
     * @return string
     */
    public function getName() {
        return $this->name;
    }

    public function setProperty($propertyName, $propertyValue) {
        $this->properties[(string)$propertyName] = (string)$propertyValue;
    }

    public function getProperty($propertyName) {
        $result = null;
        if(isset($this->properties[$propertyName])) {
            $result = $this->properties[$propertyName];
        }
        return $result;
    }

    public function removeProperty($propertyName) {
        unset($this->properties[$propertyName]);
    }

    /**
     * @return DOMElement | DOMElement[]
     */
    public function build() {
        $doc = new DOMDocument('1.0', 'UTF-8');
        $containerDOM = $doc->createElement($this->properties['tag']);
        $containerDOM->setAttribute('name', $this->getName());
        $doc->appendChild($containerDOM);
        foreach($this->properties as $propertyName => $propertyValue) {
            if($propertyName != 'tag') {
                $containerDOM->setAttribute($propertyName, $propertyValue);
            }
        }
        foreach($this->blocks as $block) {
            if(
                $block instanceof ComponentContainer
                ||
                (
                    $block instanceof Component
                    &&
                    $block->enabled()
                    &&
                    ($this->document->getRights()>= $block->getMethodRights())
                )
            ) {
                $blockDOM = $block->build();
                if($blockDOM instanceof DOMDocument) {
                    $blockDOM = $doc->importNode($blockDOM->documentElement, true);
                    $containerDOM->appendChild($blockDOM);
                }
            }
        }

        return $doc;
    }
    /**
     * @return void
     */
    public function run() {
        foreach($this->blocks as $block) {
            $block->run();
        }
    }

    public function rewind() {
        $this->childNames = array_keys($this->blocks);
        $this->iteratorIndex = 0;
    }

    public function valid() {
        return isset($this->childNames[$this->iteratorIndex]);
    }

    public function key() {
        return $this->childNames[$this->iteratorIndex];
    }

    public function next() {
        $this->iteratorIndex ++;
    }

    public function current() {
        return $this->blocks[$this->childNames[$this->iteratorIndex]];
    }

}
