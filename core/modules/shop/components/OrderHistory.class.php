<?php
/**
 * Содержит класс OrderHistory
 *
 * @package energine
 * @subpackage shop
 * @author dr.Pavka
 * @copyright ColoCall 2006
 * @version $Id$
 */

//require_once('core/modules/share/components/Grid.class.php');

/**
 * История заказов
 *
 * @package energine
 * @subpackage shop
 * @author dr.Pavka
 */
class OrderHistory extends Grid {
    /**
      * Детали заказа(список продуктов)
      *
      * @var OrderDetails
      * @access private
      */
    private $details;
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
        $this->setTableName('shop_orders');
        $this->setOrder(array('order_created'=>QAL::DESC));
    }

    /**
     * Формат даты создания заказа
     *
     * @return DataDescription
     * @access protected
     */

     protected function createDataDescription() {
         $result = parent::createDataDescription();
         if ($this->getAction() == 'getRawData') {
         	$result->getFieldDescriptionByName('order_created')->setProperty('outputFormat', '%d/%m/%Y %H:%M');
         }
         return $result;
     }
    /**
	 * Выводит детали заказа
	 *
	 * @return void
	 * @access protected
	 */

    protected function showDetails() {
        $orderID = $this->getActionParams();
        $orderID = $orderID[0];
        $this->request->setPathOffset($this->request->getPathOffset() + 2);
        $this->details = $this->document->componentManager->createComponent('orderDetails', 'shop', 'OrderDetails', array('orderID'=>$orderID), false);
        $this->details->getAction();
        $this->details->run();
    }

    /**
     * Для метода вывода информации о заказе доавбляем инфу о данных заказа
     *
     * @return void
     * @access protected
     */

     protected function edit() {
        parent::edit();

        $data = $this->dbh->select($this->getTableName(), array('user_detail', 'order_detail'), $this->getFilter());
        if (is_array($data)) {
            list($data) = $data;
            $userData = unserialize($data['user_detail']);
            $orderData = unserialize($data['order_detail']);
            unset($userData['u_id']);
            foreach ($userData as $fieldName => $value) {
                $field = new FieldDescription($fieldName);
                $field->setType(FieldDescription::FIELD_TYPE_STRING);
                $field->setProperty('customField', true);
                $field->setMode(FieldDescription::FIELD_MODE_READ);
                $this->getDataDescription()->addFieldDescription($field);

                $field = new Field($fieldName);
                $field->setData($value);
                $this->getData()->addField($field);
            }
        }

     }

    /**
	  * Выводим детали заказа
	  *
	  * @return DOMNode
	  * @access public
	  */

    public function build() {
        if ($this->getAction() == 'showDetails') {
            $result = $this->details->build();
        }
        else {
            $result = parent::build();
        }
        return $result;
    }
}
