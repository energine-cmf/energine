<?php
/**
 * @file
 * GridConfig.
 *
 * It contains the definition to:
 * @code
class GridConfig;
@endcode
 *
 * @author dr.Pavka
 * @copyright Energine 2011
 *
 * @version 1.0.0
 */
namespace Energine\share\gears;
/**
 * Grid configuration.
 *
 * @code
class GridConfig;
@endcode
 */
class GridConfig extends DataSetConfig {
    /**
     * @copydoc ComponentConfig::__construct
     */
    public function __construct($config, $className, $moduleName){
        parent::__construct($config, $className, $moduleName);
        $this->registerState('fkEditor', array('/[field]-[class]/crud/[any]/'));
        $this->registerState('fkValues', array('/[field]/fk-values/[any]/'));
        $this->registerState('put', array('/put/'));
        $this->registerState('upload', array('/upload/'));
        $this->registerState('cleanup', array('/cleanup/'));
        $this->registerState('attachments', array('/attachments/[any]/', '/[id]/attachments/[any]/'));
        $this->registerState('tags', array('/tags/[any]/', '/[id]/tags/[any]/'));
        $this->registerState('autoCompleteTags', array('/tag-autocomplete/'));
    }
}