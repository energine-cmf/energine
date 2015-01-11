<?php
/**
 * @file
 * ProductTypesEditor
 *
 *
 * @code
class ProductTypesEditor;
 * @endcode
 *
 * @author Pavel Dubenko
 * @copyright Energine 2014
 *
 * @version 1.0.0
 */


/**
 * Product Types editor
 *
 * Just a grid for product types CRUD
 */
class ProductTypesEditor extends Grid {
    /**
     * @var PropertiesEditor
     */
    private $propEditor;

    public function __construct($name, $module, array $params = null) {
        parent::__construct($name, $module, $params);
        $this->setTableName('shop_product_types');
    }

    protected function prepare() {
        parent::prepare();
        if (in_array($this->getState(), array('add', 'edit'))) {
            $fd = new FieldDescription('properties');
            $fd->setType(FieldDescription::FIELD_TYPE_TAB);
            $fd->setProperty('title', $this->translate('TAB_TYPE_PROPERTIES'));
            $this->getDataDescription()->addFieldDescription($fd);

            $field = new Field('properties');
            $state = $this->getState();
            $tab_url = (($state != 'add') ? $this->getData()->getFieldByName($this->getPK())->getRowData(0) : '') . '/properties/';

            $field->setData($tab_url, true);
            $this->getData()->addField($field);
        }
    }
    /**
     *
     */
    protected function propertiesEditor() {
        $sp = $this->getStateParams(true);
        /*$params = array('config' => 'core/modules/apps/config/PropertiesEditor.component.xml');*/
        $params = array();

        if (isset($sp['type_id'])) {
            $this->request->shiftPath(2);
            $params['typeID'] = $sp['type_id'];

        } else {
            $this->request->shiftPath(1);
        }
        $this->propEditor = $this->document->componentManager->createComponent('propEditor', 'shop', 'PropertiesEditor', $params);
        $this->propEditor->run();
    }

    /**
     * @copydoc Grid::build
     */
    public function build() {
        if ($this->getState() == 'propertiesEditor') {
            $result = $this->propEditor->build();
        } else {
            $result = parent::build();
        }

        return $result;
    }
}

