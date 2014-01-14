<?php
/**
 * Содержит класс ProductPropertiesValuesEditor
 *
 * @package energine
 * @subpackage shop
 * @author Andrii A
 * @copyright Energine 2013
 */

/**
 * Редактор значений свойств товаров
 *
 * @package energine
 * @subpackage shop
 * @author Andrii A
 */
class ProductPropertiesValuesEditor extends Grid {
    /**
     * Конструктор класса
     *
     * @access public
     */
    public function __construct($name, $module, array $params = null) {
        parent::__construct($name, $module, $params);
        $this->setTableName('shop_product_properties_values');

        $linkedId = $this->getParam('linkedId');
        $fk = $this->getParam('fk');

        if ($this->getState() != 'save') {
            if ($linkedId) {
                $this->addFilterCondition(array($fk => $linkedId));
            } else {
                $this->addFilterCondition(array($fk => null, 'session_id' => session_id()));
            }
        }
    }

    protected function add() {
        parent::add();

        for($i=0; $i<$this->getData()->getRowCount(); $i++) {
            $f = $this->getData()->getFieldByName($this->getParam('fk'));
            $f->setRowData($i, $this->getParam('linkedId'));

            $f = $this->getData()->getFieldByName('session_id');
            $f->setRowData($i, session_id());
        }

    }

    protected function edit() {
        parent::edit();

        for($i=0; $i<$this->getData()->getRowCount(); $i++) {
            $f = $this->getData()->getFieldByName($this->getParam('fk'));
            $f->setRowData($i, $this->getParam('linkedId'));

            $f = $this->getData()->getFieldByName('session_id');
            $f->setRowData($i, session_id());
        }
    }

    protected function createDataDescription() {
        $dd = parent::createDataDescription();
        foreach(array($this->getParam('fk'), 'session_id') as $fName) {
            if($fd = $dd->getFieldDescriptionByName($fName)) {
                $fd->setType(FieldDescription::FIELD_TYPE_HIDDEN);
            }
        }
        return $dd;
    }

    protected function loadDataDescription() {
        $result = parent::loadDataDescription();
        $result[$this->getParam('fk')]['key'] = false;
        return $result;
    }

    protected function defineParams() {
        return array_merge(parent::defineParams(),
            array(
                'linkedId' => false,
                'fk' => false
            )
        );
    }
}