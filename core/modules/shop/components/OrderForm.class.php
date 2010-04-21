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
     * Метод проверки логина
     * Вызывается AJAXом при заполнении формы регистрации
     *
     * @access protected
     * @return void
     */
    protected function checkLogin(){
        $login = trim($_POST['login']);
            $result = !(bool)simplifyDBResult(
                $this->dbh->select(
                    'user_users',
                    array('COUNT(u_id) as number'),
                    array('u_name' => $login)
                ),
                'number', 
            true
            );
            $field = 'login';
            $message = ($result)?$this->translate('TXT_LOGIN_AVAILABLE'):$this->translate('TXT_ORDER_LOGIN_ENGAGED');
            $result = array(
                'result'=> $result,
                'message' => $message,
                'field' => $field,  
        );
        $result = json_encode($result);
        $this->response->setHeader('Content-Type', 'text/javascript; charset=utf-8');
        $this->response->write($result);
        $this->response->commit();
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
        	if (
	        	in_array($fieldName, 
	        	       array('u_id', 'os_id', 'order_comment', 'order_detail', 'user_detail', 'order_created')
	        	)) 
	        {
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
        $this->dbh->beginTransaction();
        try {
            if(
             !isset($_SESSION['captchaCode'])
             ||
             !isset($_POST['captcha'])
             ||
             ($_SESSION['captchaCode'] != sha1($_POST['captcha']))
            ){
                 throw new SystemException('TXT_BAD_CAPTCHA', SystemException::ERR_CRITICAL);   
            }
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
            $this->order->sendNotification(array_merge($userData, $data));
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
}
