<?php
/**
 * Содержит класс UserOrderHistory
 *
 * @package energine
 * @subpackage shop
 * @author dr.Pavka
 * @copyright ColoCall 2006
 * @version $Id$
 */

//require_once('core/modules/shop/components/OrderHistory.class.php');
//require_once('core/modules/shop/components/Discounts.class.php');

/**
 * Класс выводит список заказов текущего пользователя
 *
 * @package energine
 * @subpackage shop
 * @author dr.Pavka
 */
class UserOrderHistory extends OrderHistory {
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
        $this->setFilter(array('u_id' => $this->document->getUser()->getID()));
        $this->removeProperty('exttype');
        $this->setOrder(array('order_created'=>QAL::DESC));
    }

    /**
	 * Для вывода списка  - вызываем дедовский метод загрузеи данных, поскольку в родиетльском работает AJAX
	 *
	 * @return mixed
	 * @access protected
	 */

    protected function loadData() {
        if ($this->getAction() == self::DEFAULT_ACTION_NAME) {
            $result = DBDataSet::loadData();
        }
        else {
            $result = parent::loadData();
        }

        return $result;
    }

    /**
	  * Добавлена хлебная крошка
	  * Order_detail сделан пользовательским типом
	  *
	  * @return void
	  * @access protected
	  */

    protected function view() {
        parent::view();
        $discounts = Discounts::getInstance();
        $this->document->componentManager->getComponentByName('breadCrumbs')->addCrumb();

        $fieldOrderDetails = $this->getData()->getFieldByName('order_detail');
        list($details) = $fieldOrderDetails->getData();
        $details = unserialize($details);
        $dom_details = $this->doc->createElement('recordset');
        $dom_details->setAttribute('title', $this->translate('TXT_BASKET_SUMM'));
        $summ = 0;
        $fields = array('product_name', 'product_id', 'product_price', 'basket_count', 'product_summ');
        foreach ($details as $detail) {
            $dom_detail = $this->doc->createElement('record');
            foreach ($fields as $fieldName) {
                $dom_detail_field = $this->doc->createElement('field', $detail[$fieldName]);
                $dom_detail_field->setAttribute('title', $this->translate('FIELD_'.strtoupper($fieldName)));
                $dom_detail_field->setAttribute('name', $fieldName);
                $dom_detail->appendChild($dom_detail_field);
            }
            $summ += $detail['product_summ'];
            $dom_details->appendChild($dom_detail);
        }
        $dom_details->setAttribute('discount', $discounts->getDiscountForGroup());
        $dom_details->setAttribute('summ', $summ);
        $dom_details->setAttribute('summ_with_discount', $discounts->calculateCost($summ));
        $this->addTranslation('TXT_BASKET_SUMM_WITH_DISCOUNT');
        $fieldOrderDetails->setData($dom_details);
        $this->getDataDescription()->getFieldDescriptionByName('order_detail')->setType(FieldDescription::FIELD_TYPE_CUSTOM);

    }

}
