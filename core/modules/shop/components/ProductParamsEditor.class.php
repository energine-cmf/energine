<?php

/**
 * Содержит класс ProductParamsEditor
 *
 * @package energine
 * @subpackage shop
 * @author dr.Pavka
 * @copyright ColoCall 2006
 * @version $Id$
 */

//require_once('core/modules/share/components/Grid.class.php');

/**
 * Редактор параметров типа продукта
 *
 * @package energine
 * @subpackage shop
 * @author dr.Pavka
 */
class ProductParamsEditor extends Grid {
    /**
     * Конструктор класса
     *
     * @param string $name
     * @param string $module
     * @param Document $document
     * @param array $params
     * @access public
     */
    public function __construct($name, $module, Document $document,  array $params = null) {
        parent::__construct($name, $module, $document,  $params);
        $this->setTableName('shop_product_params');
        $this->setFilter(array('pt_id' => $this->getParam('productTypeID')));
    }

    /**
     * Добавлены:
     * Параметр productTypeID
     *
     * @return array
     * @access protected
     */
    protected function defineParams() {
        return array_merge(
        parent::defineParams(),
        array(
        'productTypeID' => false
        )
        );
    }

    /**
     * Поле pp_type сделано selectом
     *
     * @return DataDescription
     * @access protected
     */

    protected function createDataDescription() {
        $result = parent::createDataDescription();
        if ($this->getType() !== self::COMPONENT_TYPE_LIST) {
            $ppType = $result->getFieldDescriptionByName('pp_type');
            $ppType->setType(FieldDescription::FIELD_TYPE_SELECT);
            $ppType->loadAvailableValues(array(
                array(
                    'id' => FieldDescription::FIELD_TYPE_STRING,
                    'value' => $this->translate('TXT_PARAM_TYPE_STRING')
                ),
                array(
                    'id' => FieldDescription::FIELD_TYPE_INT,
                    'value' => $this->translate('TXT_PARAM_TYPE_INT')
                ),
                array(
                    'id' => FieldDescription::FIELD_TYPE_TEXT,
                    'value' => $this->translate('TXT_PARAM_TYPE_TEXT')
                )
            ), 'id', 'value');

            $ptID = $result->getFieldDescriptionByName('pt_id');
            $ptID->setType(FieldDescription::FIELD_TYPE_HIDDEN);
        }
        return $result;
    }

    /**
     * Добавлена инфа о типе продукта для вставки
     *
     * @return mixed
     * @access protected
     */

     protected function saveData() {
        if (!(isset($_POST[$this->getTableName()][$this->getPK()]) && !empty($_POST[$this->getTableName()][$this->getPK()]))) {
            //Для режима вставки добавляем инфу о типе продукта
            $_POST[$this->getTableName()]['pt_id'] = $this->getParam('productTypeID');
        }
        $result = parent::saveData();
        //Для режима вставки в таблицу значений параметров для всех языков добавляем пустые значения
        if ($this->saver->getMode() == QAL::INSERT) {
            $res = $this->dbh->select('shop_products', 'product_id' , array('pt_id' => $this->getParam('productTypeID')));
            if (is_array($res)) {
            	$langAbbr = array_keys(Language::getInstance()->getLanguages());
            	$ppID = $this->saver->getResult();
            	foreach ($res as $productInfo) {
                    	$productID = $productInfo['product_id'];
                    	$ppvID = $this->dbh->modify(QAL::INSERT, 'shop_product_param_values', array('product_id' => $productID, 'pp_id' => $ppID));
                        foreach ($langAbbr as $langID) {
                            $this->dbh->modify(QAL::INSERT, 'shop_product_param_values_translation', array('ppv_id'=>$ppvID, 'lang_id'=>$langID, 'ppv_value'=>QAL::EMPTY_STRING));
                        }
                }
            }
        }
        return $result;
     }
}
