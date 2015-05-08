<?php
/**
 * Содержит класс ComponentData
 *
 * @package energine
 * @author dr.Pavka
 * @copyright Energine 2015
 */
namespace Energine\share\gears;

use Energine\share\components\IBuilder;

/**
 * Proxy class for getting component data
 *
 * @package energine
 * @author dr.Pavka
 */
class ComponentProxyBuilder extends Object implements IBuilder {
    private $name = null;
    private $className = null;
    private $params = [];

    public function setComponent($name, $class, $params = []) {

    }

    /**
     * Get build result.
     * @return mixed
     */
    public function getResult() {
        // TODO: Implement getResult() method.
    }

    /**
     * Run building.
     * @return mixed
     */
    public function build() {
        //if()
    }

}