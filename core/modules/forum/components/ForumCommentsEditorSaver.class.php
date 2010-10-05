<?php 
/**
 * Содержит класс ForumCommentsEditorSaver
 *
 * @package energine
 * @subpackage stb
 * @author d.pavka
 * @copyright d.pavka@gmail.com
 */

/**
 *
 * @package energine
 * @subpackage stb
 * @author d.pavka@gmail.com
 */
class ForumCommentsEditorSaver extends Saver {

    public function setDataDescription(DataDescription $dataDescription){
        $dataDescription->removeFieldDescription($dataDescription->getFieldDescriptionByName('u_id'));
        parent::setDataDescription($dataDescription);
    }

}