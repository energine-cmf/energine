<?php
/**
 * Содержит класс GridConfig
 *
 * @package energine
 * @subpackage share
 * @author dr.Pavka
 * @copyright Energine 2011
 */

/**
 * Класс реализующий работу с конфигурационным файлом компонента
 * специфичный для Grid
 *
 *
 * @package energine
 * @subpackage share
 * @author dr.Pavka
 */
class SiteEditorConfig extends GridConfig {
    public function __construct($config, $className, $moduleName){
        parent::__construct($config, $className, $moduleName);
        $this->registerState('domains', array('/domains/[any]/', '/[site_id]/domains/[any]/'));
        $this->registerState('go', array('/goto/[site_id]/'));
    }

}