<?php
/**
 * @file
 * Lookup
 *
 * Contains the definition to:
 * @code
class Lookup;
 * @endcode
 *
 * @author dr.Pavka
 * @copyright Energine 2015
 *
 * @version 1.0.0
 */
namespace Energine\share\components;

use Energine\share\gears\LookupConfig;

/**
 * Lookup.php
 *
 * @code
class Lookup;
 * @endcode
 */
class Lookup extends Grid
{
    /**
     * @copydoc Grid::__construct
     */
    public function __construct($name, $module, array $params = null)
    {
        parent::__construct($name, $module, $params);
    }
    /**
     * @copydoc DBDataSet::getConfig
     */
    protected function getConfig() {
        if (!$this->config) {
            $this->config = new LookupConfig(
                $this->getParam('config'),
                get_class($this),
                $this->module
            );
        }
        return $this->config;
    }

    protected function autocomplete(){

    }
}