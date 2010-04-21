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
    /**
     * Отправка уведомления о заказе
     *
     * @param array
     * @return bool
     * @access public
     */

    public function sendNotification($data) {
        $this->sendClientMail($data);

        if ($managerEmail = $this->getConfigValue('mail.manager')) {
            $this->sendManagerMail($data, explode(' ', $managerEmail));
        
        }

        return true;
    }

    /**
      * Возвращает текст письма отправляемого пользователю при отправке заказа
      * Вынесено в отдельный метод для облегчения переписывания с потомках
      *
      * @return string
      * @access protected
      */

    protected function sendClientMail($data) {
        //$data['order_id'], $data['u_name'], $data['u_fullname'], $data['order_delivery_comment'], $basketHTML
    	$mail = new Mail();
        $mail->setFrom($this->getConfigValue('mail.from'));
        $mail->addTo($data['u_name']);
        $mail->setSubject($this->translate('TXT_ORDER_CLIENT_SUBJECT'));
        $data['basket'] = $this->buildBasketHTML(); 
        if ($this->getUser()->getValue('u_password') === true) {
            $body = $this->translate('TXT_ORDER_CLIENT_MAIL_BODY');
        }
        else {
        	$data['u_password'] = $this->getUser()->getValue('u_password');
            $body = $this->translate('TXT_ORDER_NEW_CLIENT_MAIL_BODY');
        }
        $mail->setText($body, $data);
        $mail->send();
    }


    /**
     * Возвращает текст письма администратору
     *
     *
     * @param array $data
     * @return string
     * @access protected
     */
    protected function sendManagerMail($data, $managerEmails) {
    	$mail = new Mail();
        $mail->setFrom($this->getConfigValue('mail.from'));
        foreach ($managerEmails as $email) {
            $mail->addTo($email);
        }
        $mail->setSubject($this->translate('TXT_ORDER_MANAGER_SUBJECT'));
        $basketHTML = $this->buildBasketHTML();
        $data['basket'] = $basketHTML;
         
        $mail->setText(
            $this->translate('TXT_ORDER_MANAGER_MAIL_BODY'),
            $data
        );
        $mail->send();
    }

    /**
     * Возвращает содержимое корзины в HTML
     *
     * @return string
     * @access protected
     */
    protected function buildBasketHTML() {
        $converter = CurrencyConverter::getInstance();
        //$discounts = Discounts::getInstance();
        $contents = $this->getBasket()->getFormattedContents();
        $basketHTML = '<table border="1">';
        $basketHTML .= '<thead><tr>';
        $basketHTML .= '<td>'.$this->translate('FIELD_PRODUCT_NAME')."</td>\t<td>".$this->translate('FIELD_BASKET_COUNT')."</td>\t<td>".$this->translate('FIELD_PRODUCT_PRICE')."</td>\t<td>".$this->translate('FIELD_PRODUCT_SUMM')."</td>\n";
        $basketHTML .= '</tr></thead><tbody>';
        $summ = 0;
        foreach ($contents as $key => $productInfo) {
            $basketHTML .= '<tr>';
            $basketHTML .= '<td>'.$productInfo['product_name'] .' '.$productInfo['product_code'] ."</td>\t";
            $basketHTML .= '<td>'.$productInfo['basket_count'] ."</td>\t";
            $basketHTML .= '<td>'.$productInfo['product_price'] ."</td>\t";
            $basketHTML .= '<th>'.$productInfo['product_summ'] ."</th>\t";
            $basketHTML .= "</tr>\n";
            $summ += $productInfo['product_summ'];
        }
        $basketHTML .= '</tbody>';
        $basketHTML .= '<tfoot>';
        $basketHTML .= '<tr><td colspan="3">'.$this->translate('TXT_BASKET_SUMM')."</td>\t<td>".$converter->format($summ, $converter->getIDByAbbr('HRN')).'</td></tr>';
        /*if ($discounts->getDiscountForGroup() > 0) {
            $basketHTML .= '<tr><td colspan="3">'.$this->translate('TXT_BASKET_SUMM_WITH_DISCOUNT').' '.$discounts->getDiscountForGroup().'%</td><td>'.number_format($discounts->calculateCost($summ), 2, '.', ' ').'</td></tr>';
        }*/
        $basketHTML .= '</tfoot>';
        $basketHTML .= '</table>';
        return $basketHTML;
    }    
}
