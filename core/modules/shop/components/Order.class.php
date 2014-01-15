<?php
/**
 * Содержит класс Order
 *
 * @package energine
 * @subpackage shop
 * @author Andrii A
 * @copyright eggmengroup.com
 */


/**
 * Предназначен для формирования заказа пользователем
 *
 * @package energine
 * @subpackage shop
 * @author Andrii A
 */
class Order extends DBDataSet {
    /**
     * @param string $name
     * @param string $module
     * @param array $params
     */
    public function __construct($name, $module, array $params = null) {
        parent::__construct($name, $module, $params);
        $this->setTableName('shop_orders');
        //@TODO: возможно стоит получать ссылку на компонент корзины
    }
}
