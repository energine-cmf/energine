<?php
/**
 * Содержит класс ProductPropertiesEditor
 *
 * @package energine
 * @subpackage shop
 * @author Andrii A
 * @copyright Energine 2013
 */

/**
 * Редактор свойств товаров
 *
 * @package energine
 * @subpackage shop
 * @author Andrii A
 */
class ProductPropertiesEditor extends Grid {
    /**
     * @var ProductPropertiesValuesEditor
     */
    private $valuesEditor;
    /**
     * Конструктор класса
     *
     * @access public
     */
    public function __construct($name, $module, array $params = null) {
        parent::__construct($name, $module, $params);
        $this->setTableName('shop_product_properties');
    }

    protected function saveData() {
        $propId = parent::saveData();
        $this->dbh->modify('UPDATE shop_product_properties_values SET prop_id = %s WHERE session_id = %s and prop_id IS NULL', $propId, session_id());
        return $propId;
    }

    protected function linkExtraManagers($tableName, $data = false) {
        parent::linkExtraManagers($this->getTableName());
        $fd = new FieldDescription('prop_values');
        $fd->setType(FieldDescription::FIELD_TYPE_TAB);
        $fd->setProperty('title', $this->translate('TAB_VALUES'));
        $fd->setProperty('tableName', 'shop_product_properties_values');
        $this->getDataDescription()->addFieldDescription($fd);

        $field = new Field('prop_values');
        $tab_url = (($this->getState() != 'add') ? $this->getData()->getFieldByName($this->getPK())->getRowData(0) : '') . '/values/';

        $field->setData($tab_url, true);
        $this->getData()->addField($field);
    }

    /**
     * Вывод редактора значений
     */
    protected function showValuesEditor() {
        $sp = $this->getStateParams(true);
        $params = array(
            'fk' => $this->getPK()
        );
        if (isset($sp['propId'])) {
            $this->request->shiftPath(2);
            $params['linkedId'] = $sp['propId'];
        } else {
            $this->request->shiftPath(1);
        }
        $this->valuesEditor = $this->document->componentManager->createComponent(
            'valuesEditor',
            'shop',
            'ProductPropertiesValuesEditor',
            $params
        );
        $this->valuesEditor->run();
    }

    public function build() {
        if ($this->getState() == 'showValuesEditor') {
            $result = $this->valuesEditor->build();
        } else {
            $result = Grid::build();
        }
        return $result;
    }
}