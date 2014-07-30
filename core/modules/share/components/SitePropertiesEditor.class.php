<?php
/**
 * @file
 * SitePropertiesEditor
 *
 * It contains the definition to:
 * @code
class SitePropertiesEditor;
@endcode
 *
 * @author Andrii A
 * @copyright Energine 2014
 *
 * @version 1.0.0
 */

/**
 * Site properties editor.
 *
 * @code
class SitePropertiesEditor;
@endcode
 */
class SitePropertiesEditor extends Grid {
    /**
     * @copydoc Grid::__construct
     */
    public function __construct($name, $module, array $params = null) {
        parent::__construct($name, $module, $params);
        $this->setTableName('share_sites_properties');
        $this->setSaver(new SitePropertiesSaver());
    }
}