<?php
/**
 * @file
 * Lookup
 * Contains the definition to:
 * @code
class Lookup;
 * @endcode
 * @author dr.Pavka
 * @copyright Energine 2015
 * @version 1.0.0
 */
namespace Energine\share\components;

use Energine\share\gears\LookupConfig;

/**
 * Lookup.php
 * @code
class Lookup;
 * @endcode
 */
class Lookup extends Grid {
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

}