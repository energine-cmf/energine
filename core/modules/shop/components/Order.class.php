<?php
/**
 * Содержит класс Order
 *
 * @package energine
 * @subpackage shop
 * @author dr.Pavka
 * @copyright ColoCall 2006
 * @version $Id$
 */

//require_once('core/framework/DBWorker.class.php');
//require_once('core/framework/User.class.php');
//require_once('core/modules/shop/components/Basket.class.php');
//require_once('core/modules/shop/components/OrderStatus.class.php');

/**
 * Предназначен для формирования заказа пользователем
 *
 * @package energine
 * @subpackage shop
 * @author dr.Pavka
 */
class Order extends DBWorker {
    /**
     * Имя таблицы
     *
     */
    const ORDER_TABLE_NAME = 'shop_orders';
    /**
     * Корзина
     *
     * @var Basket
     * @access private
     */
    private $basket;
    /**
     * Пользователь
     *
     * @var User
     * @access private
     */
    private $user;

    /**
     * Конструктор класса
     *
     * @access public
     */
    public function __construct() {
        parent::__construct();
        $this->basket = Basket::getInstance();
    }
    /**
     * Устанавливает пользователя
     *
     * @return User
     * @access public
     */

    public function setUser(User $user) {
        $this->user = $user;
    }

    /**
     * Возвращает корзину
     *
     * @return Basket
     * @access public
     */

    public function getBasket() {
        return $this->basket;
    }

    /**
     * Возвращает пользователя
     *
     * @return User
     * @access public
     */

    public function getUser() {
        return $this->user;
    }
    /**
     * Создание заказа
     *
     * @param array
     * @return boolean
     * @access public
     */

    public function create(array $userData) {
        $data['u_id'] = $this->user->getID();
        $data['os_id'] = OrderStatus::getInstance()->getInitial();
        $data['order_created'] = date('Y-m-d H:i:s');
        $data['order_detail'] = serialize($this->basket->getFormattedContents());
        $data['user_detail'] = serialize($userData);
        $data['order_delivery_comment'] = $userData['order_delivery_comment'];
        $res = $this->dbh->modify(QAL::INSERT, self::ORDER_TABLE_NAME, $data);
        return $res;
    }
}
