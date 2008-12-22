<?php
/**
 * Содержит класс ParamValuesEditor
 *
 * @package energine
 * @subpackage shop
 * @author dr.Pavka
 * @copyright ColoCall 2006
 * @version $Id$
 */

//require_once('core/modules/share/components/Grid.class.php');

/**
 * Редактор значений параметров продукта
 *
 * @package energine
 * @subpackage shop
 * @author dr.Pavka
 */
class ParamValuesEditor extends Grid {
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
        $this->setTableName('shop_product_param_values');
    }

    /**
     * Добавлен параметр идентификатор продукта
     *
     * @return array
     * @access protected
     */

    protected function defineParams() {
        return array_merge(
        parent::defineParams(),
        array(
        'productID' => false
        )
        );
    }

    /**
	 * Всегда выводятся все присвоенные этому типу параметра значения
	 *
	 * @return array
	 * @access protected
	 */

    protected function loadData() {
        $result = false;
        if ($this->getAction() == 'getRawData') {
            $result = $this->dbh->selectRequest(sprintf('
            SELECT ppv.ppv_id, ppvt.lang_id, ppt.pp_name, ppvt.ppv_value FROM shop_product_params pp
            LEFT JOIN shop_product_params_translation ppt ON ppt.pp_id = pp.pp_id
            LEFT JOIN shop_product_param_values ppv ON ppv.pp_id = pp.pp_id AND ppv.product_id = %s
            LEFT JOIN shop_product_param_values_translation ppvt ON ppvt.ppv_id = ppv.ppv_id AND ppvt.lang_id = %s
            WHERE pp.pt_id in(
            SELECT pt_id FROM `shop_products` WHERE product_id = %s)
            AND ppt.lang_id = %s
            ',
            $this->getParam('productID'),
            $this->getDataLanguage(),
            $this->getParam('productID'),
            $this->getDataLanguage()
            ));

        }
        else {
            $result = parent::loadData();
        }
        return $result;
    }

    /**
     * В зависимости от типа продукта выставляется тип поля ppv_value
     *
     * @return DataDescription
     * @access protected
     */

    protected function createDataDescription() {
        $result = parent::createDataDescription();
        if ($this->getType() == self::COMPONENT_TYPE_FORM_ALTER) {
        	$fieldType = simplifyDBResult($this->dbh->selectRequest('SELECT pp_type FROM shop_products p
        	LEFT JOIN shop_product_params pp ON pp.pt_id = p.pt_id
        	LEFT JOIN shop_product_param_values ppv ON ppv.pp_id = pp.pp_id
        	WHERE p.product_id ='.$this->getParam('productID').' AND ppv.ppv_id = '.current($this->getFilter())), 'pp_type', true);
            $result->getFieldDescriptionByName('ppv_value')->setType($fieldType);
        }
        return $result;
    }
}

