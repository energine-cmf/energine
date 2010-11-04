<?php
/**
 * Содержит класс FeedbackForm
 *
 * @package energine
 * @subpackage share
 * @author dr.Pavka
 * @copyright Energine 2007
 * @version $Id$
 */

/**
 * Форма обратной связи
 *
 * @package energine
 * @subpackage share
 * @author dr.Pavka
 */
class FeedbackForm extends DBDataSet {

    /**
     * Конструктор класса
     *
     * @param string $name
     * @param string $module

     * @param array $params
     * @access public
     */
    public function __construct($name, $module,   array $params = null) {
        parent::__construct($name, $module,  $params);
        //$tableName = $this->getParam('tableName');
        
        /*if(!($tableName)){
            $this->setTableName('apps_feedback');
        }else {
            $this->setTableName($tableName);
        }*/
        $this->setType(self::COMPONENT_TYPE_FORM_ADD);
        $this->setDataSetAction('send');
        $this->setTitle($this->translate('TXT_FEEDBACK_FORM'));
        $this->addTranslation('TXT_ENTER_CAPTCHA');
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
        'textBlock' => false,
        'tableName' => 'apps_feedback',    
        'recipientEmail' => 'mail.feedback',
        'userSubject' => 'TXT_SUBJ_FEEDBACK_USER',
        'userBody' => 'TXT_BODY_FEEDBACK_USER',
        'adminSubject' => 'TXT_SUBJ_FEEDBACK_ADMIN',
        'adminBody' => 'TXT_BODY_FEEDBACK_ADMIN',
        ));
        return $result;
    }

    /**
     * Сохраняет данные
     *
     * @return mixed
     * @access protected
     */

     protected function saveData($data) {
        $result = false;
        //создаем объект описания данных
        $dataDescriptionObject = new DataDescription();

       //получаем описание полей для метода
        $configDataDescription = $this->config->getMethodConfig($this->getPreviousAction());
        //если в конфиге есть описание полей для метода - загружаем их
        if (isset($configDataDescription->fields)) {
            $dataDescriptionObject->loadXML($configDataDescription->fields);
        }

        //Создаем объект описания данных взятых из БД
        $DBDataDescription = new DataDescription();
        //Загружаем в него инфу о колонках
        $DBDataDescription->load($this->loadDataDescription());
        $this->setDataDescription($dataDescriptionObject->intersect($DBDataDescription));

        $dataObject = new Data();
        $dataObject->load($data);
        $this->setData($dataObject);

        //Создаем сейвер
        $this->saver = new Saver();
        //Устанавливаем его режим
        $this->saver->setMode(self::COMPONENT_TYPE_FORM_ADD);
        $this->saver->setDataDescription($this->getDataDescription());
        $this->saver->setData($this->getData());

        if($this->saver->validate() === true) {
            $this->saver->setFilter($this->getFilter());
            $this->saver->save();
            $result = $this->saver->getResult();

        }
        else {
            //выдвигается пустой exception который перехватывается в методе save
            throw new FormException();
        }

        return $result;

     }

    /**
	 * Записывает обращение в БД, отправляет уведомление пользователю и администратору
	 *
	 * @return void
	 * @access protected
	 */

    protected function send() {
    	try{
			$data[$this->getTableName()] = $_POST[$this->getTableName()];
            $this->checkCaptcha();

	        if ($result = $this->saveData($data)) {
	            $data = $data[$this->getTableName()];
	            $senderEmail = $data['feed_email'];

	            $this->dbh->modify(QAL::UPDATE, $this->getTableName(), array('feed_date'=>date('Y-m-d H:i:s')), array($this->getPK()=>$result));

	            $mailer = new Mail();
	            $mailer->setFrom($this->getConfigValue('mail.from'))->
	                setSubject($this->translate($this->getParam('userSubject')))->
	                setText($this->translate($this->getParam('userBody')), $data)->
	                addTo($senderEmail, $senderEmail)
	                ->send();
	            try {
	            	$mailer = new Mail();
	            	$data['feed_email']  = $senderEmail;
	                $mailer->setFrom($this->getConfigValue('mail.from'))->
	                    setSubject($this->translate($this->getParam('adminSubject')))->
	                    setText($this->translate($this->getParam('adminBody')),$data)->
	                    addTo($this->getRecipientEmail())->send();
	            }
	            catch (Exception $e){
	            }
	        }


	        $this->prepare();
	        
	        if ($this->getParam('textBlock') && ($textBlock = $this->document->componentManager->getBlockByName($this->getParam('textBlock')))) {
	        	$textBlock->disable();
	        }

            $this->response->redirectToCurrentSection('success/');

    	}
    	catch (Exception $e){
            $this->failure($e->getMessage(), $data[$this->getTableName()]);
    	}
   }

/**
     * Визначає адресу отримувача
     *
     * @return string
     * @access private
     */
   protected function getRecipientEmail(){
        return $this->getConfigValue($this->getParam('recipientEmail'));
   }
   /*
    * Викликаємо у випадку помилки з captcha 
    */
   protected function failure($errorMessage, $data){
        $this->config->setCurrentMethod('main');
        $this->prepare();
        $eFD = new FieldDescription('error_message');
        $eFD->setMode(FieldDescription::FIELD_MODE_READ);
        $eFD->setType(FieldDescription::FIELD_TYPE_CUSTOM);
        $this->getDataDescription()->addFieldDescription($eFD);
        $this->getData()->load(array(array_merge(array('error_message' => $errorMessage), $data)));
        $this->getDataDescription()->getFieldDescriptionByName('error_message')->removeProperty('title');
    }

    /*
     * Перевіряє капчу
     */
    protected function checkCaptcha(){
        if(
			 !isset($_SESSION['captchaCode'])
			 ||
			 !isset($_POST['captcha'])
			 ||
			 ($_SESSION['captchaCode'] != sha1(trim($_POST['captcha'])))
			){
			     throw new SystemException('TXT_BAD_CAPTCHA', SystemException::ERR_CRITICAL);
        }
        unset($_SESSION['captchaCode']);
    }

    protected function prepare(){
    	parent::prepare();
    	if(
    	   $this->document->getUser()->isAuthenticated()
    	   &&
    	   ($captcha = $this->getDataDescription()->getFieldDescriptionByName('captcha'))
    	 ){
    	   $this->getDataDescription()->removeFieldDescription($captcha);
    	   unset($_SESSION['captchaCode']);
    	}
    }

    protected function success() {
		$this->setBuilder($this->createBuilder());

        $dataDescription = new DataDescription();
		$ddi = new FieldDescription('result');
		$ddi->setType(FieldDescription::FIELD_TYPE_TEXT);
		$ddi->setMode(FieldDescription::FIELD_MODE_READ);
		$ddi->removeProperty('title');
		$dataDescription->addFieldDescription($ddi);

		$data = new Data();
		$di = new Field('result');
		$di->setData($this->translate('TXT_FEEDBACK_SUCCESS_SEND'));
		$data->addField($di);

		$this->setDataDescription($dataDescription);
		$this->setData($data);

	}

}