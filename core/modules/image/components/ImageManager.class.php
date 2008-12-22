<?php
/**
 * Класс ImageManager.
 *
 * @package energine
 * @subpackage image
 * @author 1m.dm
 * @copyright ColoCall 2006
 * @version $Id$
 */

//require_once('core/modules/share/components/DataSet.class.php');
//require_once('core/modules/image/components/Image.class.php');

/**
 * Менеджер изображений.
 *
 * @package energine
 * @subpackage image
 * @author 1m.dm
 */
class ImageManager extends DataSet {

    public function __construct($name, $module, Document $document,  array $params = null) {
        parent::__construct($name, $module, $document,  $params);

        $this->addTranslation('TXT_OPEN_FIELD');
        $this->addTranslation('TXT_CLOSE_FIELD');
    }

    public function createDataDescription() {
        $result = parent::createDataDescription();
        $fieldDescr = $result->getFieldDescriptionByName('align');
        $fieldDescr->loadAvailableValues(
            array(
                array('id' => 'bottom', 'value' => $this->translate('TXT_ALIGN_BOTTOM')),
                array('id' => 'middle', 'value' => $this->translate('TXT_ALIGN_MIDDLE')),
                array('id' => 'top',    'value' => $this->translate('TXT_ALIGN_TOP')),
                array('id' => 'left',   'value' => $this->translate('TXT_ALIGN_LEFT')),
                array('id' => 'right',  'value' => $this->translate('TXT_ALIGN_RIGHT'))
            ),
            'id', 'value'
        );
        return $result;
    }
}

