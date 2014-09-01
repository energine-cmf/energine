<?php
/**
 * @file
 * SiteEditorConfig.
 *
 * It contains the definition to:
 * @code
class SiteEditorConfig;
@endcode
 *
 * @author dr.Pavka
 * @copyright Energine 2011
 *
 * @version 1.0.0
 */
namespace Energine\share\gears;

/**
 * Configuration of site editor.
 *
 * @code
class SiteEditorConfig;
@endcode
 */
class SiteEditorConfig extends GridConfig {
    /**
     * @copydoc GridConfig::__construct
     */
    public function __construct($config, $className, $moduleName){
        parent::__construct($config, $className, $moduleName);
        $this->registerState('domains', array('/domains/[any]/', '/[site_id]/domains/[any]/'));
        $this->registerState('go', array('/goto/[site_id]/'));
    }

}