<?php
/**
 * Содержит класс ProductEditor
 *
 * @package energine
 * @subpackage shop
 * @author Andrii A
 * @copyright Energine 2013
 */

/**
 * Редактор товаров для админ части
 * системы
 *
 * @package energine
 * @subpackage shop
 * @author Andrii A
 */
class ProductEditor extends Grid {
    /**
     * @var PropertiesValuesEditor
     */
    private $propEditor;
    /**
     * @var DivisionEditor
     */
    private $divisionEditor;

    /**
     * Конструктор класса
     *
     * @access public
     */
    public function __construct($name, $module, array $params = null) {
        parent::__construct($name, $module, $params);
        $this->setTableName('shop_product');
        $this->setSaver(new ProductSaver());
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
            for ($i = 0; $i < sizeof(E()->getLanguage()->getLanguages()); $i++) {
                $field->setRowProperty($i, 'segment', '/properties/');
            }


            $this->getData()->addField($field);
        }
    }

    protected function createDataDescription() {
        $dd = parent::createDataDescription();
        if (in_array($this->getState(), array('add', 'edit'))) {
            $dd->getFieldDescriptionByName('smap_id')->setType(FieldDescription::FIELD_TYPE_SMAP_SELECTOR);
            $dd->getFieldDescriptionByName('pt_id')->setProperty('editor', 'ProductTypesEditor');
            $dd->getFieldDescriptionByName('product_add_date')->setType(FieldDescription::FIELD_TYPE_HIDDEN);
            $dd->getFieldDescriptionByName('product_mod_date')->setType(FieldDescription::FIELD_TYPE_HIDDEN);
        }
        return $dd;
    }

    /**
     * Вывод дерева разделов для форм добавления/редактирования
     */
    protected function showSmapSelector() {
        $this->request->shiftPath(1);
        $this->divisionEditor = ComponentManager::createBlockFromDescription(
            ComponentManager::getDescriptionFromFile('core/modules/apps/templates/content/site_div_selector.container.xml')
        );
        $this->divisionEditor->run();
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
        $this->propEditor = $this->document->componentManager->createComponent('propEditor', 'shop', 'PropertiesValuesEditor', $params);
        $this->propEditor->run();
    }

    public function build() {
        if ($this->getState() == 'showSmapSelector') {
            $result = $this->divisionEditor->build();
        } elseif ($this->getState() == 'propertiesEditor') {
            $result = $this->propEditor->build();
        } else {
            $result = Grid::build();
        }
        return $result;
    }
}