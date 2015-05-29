<?php
/**
 * Содержит класс ComponentData
 *
 * @package energine
 * @author dr.Pavka
 * @copyright Energine 2015
 */
namespace Energine\share\gears;

use Energine\share\components\Component;
use Energine\share\components\IBuilder;

/**
 * Proxy class for getting component data
 *
 * @package energine
 * @author dr.Pavka
 */
class ComponentProxyBuilder extends XMLBuilder implements IBuilder {
    /**
     * @var Component component which recordset we want to get
     */
    private $component;
    /**
     * @var string
     */
    private $filter = NULL;
    private $componentDocument;

    /**
     * @param string $name proxy object name
     * @param string $class fully qualified class name
     * @param array $params component params
     * @param string $filter xpath filter
     */
    public function setComponent($name, $class, $params = [], $filter = NULL) {
        $this->filter = $filter;
        $this->component = E()->getDocument()->componentManager->createComponent($name, $class, $params);
    }

    /**
     * Get build result.
     * @return \DOMElement
     */
    public function getResult() {
        if (!$this->filter)
            $result = $this->component->getBuilder()->getResult();
        else {
            $result = $this->componentDocument;
            $xpath = new \DOMXPath($result);
            $result = $xpath->query($this->filter);
            if (!$result->length) {
                $result = false;
            }
        }

        return $result;
    }

    /**
     * Run building.
     */
    public function run() {
        $this->component->run();
        $this->componentDocument = $this->component->build();
    }

}