<?php
/**
 * Содержит класс OrderForm
 *
 * @package energine
 * @subpackage shop
 * @author dr.Pavka
 * @copyright ColoCall 2006
 * @version $Id$
 */

//require_once('core/modules/share/components/DBDataSet.class.php');
//require_once('core/modules/shop/components/Order.class.php');
//require_once('core/modules/shop/components/CurrencyConverter.class.php');
//require_once('core/framework/Mail.class.php');

/**
 * Предназначен для формирования заказа пользователем
 *
 * @package energine
 * @subpackage shop
 * @author dr.Pavka
 */
class OrderForm extends DBDataSet {
    /**
     * Корзина
     *
     * @var Order
     * @access protected
     */
    protected $order;
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
        $this->setType(self::COMPONENT_TYPE_FORM_ADD);
        $this->order = new Order();
        $this->setTableName(Order::ORDER_TABLE_NAME);
        $this->setDataSetAction('save-order');
        $this->setTitle($this->translate('TXT_ORDER_FORM'));
    }
    /**
	 * Переопределен параметр active
	 *
	 * @return int
	 * @access protected
	 */

    protected function defineParams() {
        $result = array_merge(parent::defineParams(),
        array(
        'active'=>true,
        ));
        return $result;
    }

    /**
     * Если корзина - пуста то что заказывать?
     *
     * @return void
     * @access protected
     */

     protected function main() {
        if (!Basket::getInstance()->getContents()) {
            throw new SystemException('ERR_BASKET_IS_EMPTY', SystemException::ERR_CRITICAL);
        }
        parent::main();
     }

    /**
     * Подтягиваем перечень полей из таблицы пользователей
     *
     * @return mixed
     * @access protected
     */

    protected function loadDataDescription() {
        $result = parent::loadDataDescription();
        foreach (array_keys($result) as $fieldName) {
        	if (!in_array($fieldName, array('order_id', 'order_delivery_comment'))) {
        		unset($result[$fieldName]);
        	}
        }
        $result = array_merge($result, $this->dbh->getColumnsInfo('user_users'));
        unset($result['u_is_active'], $result['u_password'], $result['u_avatar_prfile']);
        $commentField = $result['order_delivery_comment'];
        unset($result['order_delivery_comment']);
        $result['order_delivery_comment'] = $commentField;
        return $result;
    }

    /**
	 * Если пользователь аутентифицирован загружаем его данные из объекта AuthUser
	 *
	 * @return Data
	 * @access protected
	 */

    protected function createData() {
        $result = false;
        $user = $this->document->getUser();
        $result = new Data();
        if ($user->isAuthenticated()) {
            foreach (array_keys($user->getFields()) as $fieldName) {
            	$data[$fieldName] = $user->getValue($fieldName);
            }
            $result->load(array($data));

        }
        return $result;
    }

    /**
     * Сохраняет данные о заказе
     *
     * @return void
     * @access protected
     */

    protected function save() {
        try {
            $this->dbh->beginTransaction();

            if (!isset($_POST[$this->getTableName()])) {
                throw new SystemException('ERR_DEV_NO_DATA', SystemException::ERR_WARNING);
            }
            $request = Request::getInstance();
            $data = $_POST[$this->getTableName()];
            $userData = $_POST['user_users'];
            if (!$this->document->getUser()->isAuthenticated()) {
                $newUser = new User();
                $newUser->create(array_merge($userData, array('u_password'=>User::generatePassword())));
                $this->order->setUser($newUser);
            }
            else {
                $this->order->setUser($this->document->getUser());
            }


            $data['order_id'] = $this->order->create(array_merge($userData, array('order_delivery_comment'=>$data['order_delivery_comment'])));
            $this->sendNotification(array_merge($userData, $data));
            $_SESSION['order_saved'] = true;
            $this->order->getBasket()->purify();

            $this->dbh->commit();
            $this->response->redirectToCurrentSection('success/');
        }
        catch (Exception $error) {
            $this->dbh->rollback();
            $this->failure($error->getMessage());
        }

    }

    /**
     * Метод отрабатывающий если что то не так пошло
     *
     * @return void
     * @access protected
     */

    protected function failure($errors) {
        $this->setBuilder($this->createBuilder());

        $dataDescription = new DataDescription();
        $ddi = new FieldDescription('message');
        $ddi->setType(FieldDescription::FIELD_TYPE_TEXT);
        $ddi->setMode(FieldDescription::FIELD_MODE_READ);
        $ddi->removeProperty('title');
        $dataDescription->addFieldDescription($ddi);

        $data = new Data();
        $di = new Field('message');
        $di->setData($this->translate('MSG_ORDER_FAILED').$errors);
        $data->addField($di);

        $this->setDataDescription($dataDescription);
        $this->setData($data);

        if ($component = $this->document->componentManager->getComponentByName('textBlock_order')) {
            $component->disable();
        }

        if ($component = $this->document->componentManager->getComponentByName('basket')) {
            $component->disable();
        }
    }

    /**
	 * Метод выводящий сообщение об успешном сохранении данных
	 *
	 * @return void
	 * @access protected
	 */

    protected function success() {
        //если в сессии нет переменной saved значит этот метод пытаются дернуть напрямую. Не выйдет!
        if (!isset($_SESSION['order_saved'])) {
            throw new SystemException('ERR_404', SystemException::ERR_404);
        }
        //Мавр сделал свое дело...
        unset($_SESSION['order_saved']);
        $this->setBuilder($this->createBuilder());

        $dataDescription = new DataDescription();
        $ddi = new FieldDescription('success_message');
        $ddi->setType(FieldDescription::FIELD_TYPE_TEXT);
        $ddi->setMode(FieldDescription::FIELD_MODE_READ);
        $ddi->removeProperty('title');
        $dataDescription->addFieldDescription($ddi);

        $data = new Data();
        $di = new Field('success_message');
        $di->setData($this->translate('TXT_ORDER_SEND'));
        $data->addField($di);

        $this->setDataDescription($dataDescription);
        $this->setData($data);

        if ($component = $this->document->componentManager->getComponentByName('textBlock_order')) {
            $component->disable();
        }
        if ($component = $this->document->componentManager->getComponentByName('basket')) {
            $component->disable();
        }
    }

    /**
     * Отправка уведомления о заказе
     *
     * @param array
     * @return bool
     * @access protected
     */

    protected function sendNotification($data) {
        $this->sendClientMail($data);

        if ($managerEmail = $this->getConfigValue('mail.manager')) {
            $this->sendManagerMail($data);
        
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
    	$mail = new Mail();
        $mail->setFrom($this->getConfigValue('mail.from'));
        $mail->addTo($data['u_name']);
        $mail->setSubject($this->translate('TXT_ORDER_CLIENT_SUBJECT'));
        $result = '';
        if ($this->order->getUser()->getValue('u_password') === true) {
        	$mail->setText($body);
            $result = sprintf($this->translate('TXT_ORDER_CLIENT_MAIL_BODY'), $data['order_id'], $data['u_name'], $data['u_fullname'], $data['order_delivery_comment'], $basketHTML);
        }
        else {
        	$mail->setText($body);
            $result = 
                sprintf($this->translate('TXT_ORDER_NEW_CLIENT_MAIL_BODY'), $data['u_name'], $this->order->getUser()->getValue('u_password'), $data['order_id'], $data['u_name'], $data['u_fullname'], $data['order_delivery_comment'], $basketHTML);
        }
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
    protected function sendManagerMail($data) {
    	$mail = new Mail();
        $mail->setFrom($this->getConfigValue('mail.from'));
        $managerEmails = explode(' ', $managerEmail);

        foreach ($managerEmails as $email) {
            $mail->addTo($email);
        }
        $mail->setSubject($this->translate('TXT_ORDER_MANAGER_SUBJECT'));
        $body = $this->buildManagerMailBody($data);
        $mail->setText($body);
        $mail->send();
    	
        $result = '';
        $basketHTML = $this->buildBasketHTML();
        $result = sprintf($this->translate('TXT_ORDER_MANAGER_MAIL_BODY'), $data['order_id'], $data['u_name'], $data['u_fullname'], $data['order_delivery_comment'], $basketHTML);
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
        $contents = $this->order->getBasket()->getFormattedContents();
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
