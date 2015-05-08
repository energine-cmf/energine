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
     * @param string $name proxy object name
     * @param string $class fully qualified class name
     * @param array $params component params
     */
    public function setComponent($name, $class, $params = []) {
        $this->component = E()->getDocument()->componentManager->createComponent($name, $class, $params);
    }

    /**
     * Get build result.
     * @return \DOMElement
     */
    public function getResult() {
        return $this->component->getBuilder()->getResult();
    }

    /**
     * Run building.
     */
    public function run() {
        $this->component->run();
        $this->component->build();
    }

}