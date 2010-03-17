<?php
/**
 * Содержит класс OrderStatuses
 *
 * @package energine
 * @subpackage shop
 * @author dr.Pavka
 * @copyright ColoCall 2007
 * @version $Id$
 */

//require_once('core/modules/share/components/Grid.class.php');

/**
 * Редактор статусов заказов
 *
 * @package energine
 * @subpackage shop
 * @author dr.Pavka
 */
class OrderStatuses extends Grid {
    /**
     * Конструктор класса
     *
     * @param string $name
     * @param string $module
     * @param Document $document
     * @param array $params
     * @access public
     */
	public function __construct($name, $module, Document $document, array $params = null) {
        parent::__construct($name, $module, $document, $params);
        $this->setTableName('shop_order_statuses');
        $this->setOrderColumn('os_priority');
        //$this->setOrder(array('os_priority'=>QAL::ASC));
	}
}