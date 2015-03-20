<?php
/**
 * @file
 * GridConfig.
 *
 * It contains the definition to:
 * @code
class GridConfig;
 * @endcode
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
 * @endcode
 */
class LookupConfig extends ComponentConfig
{
    /**
     * @copydoc ComponentConfig::__construct
     */
    public function __construct($config, $className, $moduleName)
    {
        parent::__construct($config, $className, $moduleName);
        $this->registerState('source', array('/source/'));
        $this->registerState('imageManager', array('/imagemanager/'));
        $this->registerState('fileLibrary', array('/file-library/', '/file-library/[any]/'));
        $this->registerState('upload', array('/upload/'));
        $this->registerState('cleanup', array('/cleanup/'));
        $this->registerState('getRawData',
            [
                '/get-data/',
                '/get-data/page-[pageNumber]/'
            ]
        );
    }
}