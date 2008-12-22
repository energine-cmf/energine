<?php
/**
 * Содержит класс OrderDetails
 *
 * @package energine
 * @subpackage shop
 * @author dr.Pavka
 * @copyright ColoCall 2006
 * @version $Id$
 */

//require_once('core/modules/share/components/Grid.class.php');

/**
 * Выводит детали заказа
 * вызывается из OrderHistory
 *
 * @package energine
 * @subpackage shop
 * @author dr.Pavka
 */
class OrderDetails extends Grid {
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
        $this->setTableName('shop_basket');
	}

    /**
     * Добавляет параметр идентификатор заказа
     *
     * @access protected
     * @return array
     */
    protected function defineParams() {
        return array_merge(
            parent::defineParams(),
            array(
                'orderID' => false
            )
        );
    }

    /**
     * Загружает данные
     *
     * @return array
     * @access protected
     */

     protected function loadData() {
        if ($this->getAction() == 'getRawData') {
        	$result = simplifyDBResult($this->dbh->select('shop_orders', 'order_detail', array('order_id'=>$this->getParam('orderID'))), 'order_detail', true);
        	$result = unserialize($result);
        }
        else {
        	$result = parent::loadData();
        }
        return $result;
     }
}
