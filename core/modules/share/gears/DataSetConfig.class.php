<?php
/**
 * @file
 * DataSetConfig.
 *
 * It contains the definition to:
 * @code
class DataSetConfig;
@endcode
 *
 * @author Andrii A
 * @copyright Energine 2014
 *
 * @version 1.0.0
 */
namespace Energine\share\gears;
/**
 * DataSet configuration.
 *
 * @code
class GridConfig;
@endcode
 */
class DataSetConfig extends ComponentConfig {
    /**
     * @copydoc ComponentConfig::__construct
     */
    public function __construct($config, $className, $moduleName){
        parent::__construct($config, $className, $moduleName);
        $this->registerState('source', array('/source/'));
        $this->registerState('imageManager', array('/imagemanager/'));
        $this->registerState('fileLibrary', array('/file-library/', '/file-library/[any]/'));
        $this->registerState('embedPlayer', array('/embed-player/[uplId]/'));
    }
}