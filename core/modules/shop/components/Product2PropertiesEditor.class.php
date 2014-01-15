<?php
/**
 * Содержит класс Product2PropertiesEditor
 *
 * @package energine
 * @subpackage shop
 * @author Andrii A
 * @copyright Energine 2013
 */

/**
 * Выбор свойств для товаров
 *
 * @package energine
 * @subpackage shop
 * @author Andrii A
 */
class Product2PropertiesEditor extends Grid {
    /**
     * Конструктор класса
     *
     * @access public
     */
    public function __construct($name, $module, array $params = null) {
        parent::__construct($name, $module, $params);
        $this->setTableName('shop_product2property');
    }

    protected function createDataDescription() {
        $dd = parent::createDataDescription();
        foreach(array('product_id', 'prop_id', 'pval_id') as $fName) {
            $dd->getFieldDescriptionByName($fName)->setType(FieldDescription::FIELD_TYPE_SELECT);
        }
        return $dd;
    }
}