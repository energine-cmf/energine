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
class GridConfig extends ComponentConfig {
    public function __construct($config, $className, $moduleName){
        parent::__construct($config, $className, $moduleName);
        $this->registerState('source', array('/source/'));
        $this->registerState('put', array('/put/'));
        $this->registerState('upload', array('/upload/'));
        $this->registerState('cleanup', array('/cleanup/'));
        $this->registerState('imageManager', array('/imagemanager/'));
        $this->registerState('fileLibrary', array('/file-library/', '/file-library/[any]/'));
        $this->registerState('attachments', array('/attachments/[any]/', '/[id]/attachments/[any]/'));
        $this->registerState('tags', array('/tags/[any]/', '/[id]/tags/[any]/'));
        $this->registerState('autoCompleteTags', array('/tag-autocomplete/'));
    }

}