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

    protected function createDataDescription() {
        $result = parent::createDataDescription();
        $extraFields = [];
        foreach ($result as $name => $fd) {
            if (
            !(($name == $this->getPK())
                ||
                ($name == 'lang_id')
                ||
                ($name == str_replace('_id', '_name', $this->getPK())))
            ) {
                array_push($extraFields, $fd);
            }
        }

        foreach ($extraFields as $fd) {
            $result->removeFieldDescription($fd);
        }

        return $result;
    }

}