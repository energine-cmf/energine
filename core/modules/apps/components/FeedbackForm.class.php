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
        $tableName = $this->getParam('tableName');
        if(!($tableName)){
            $this->setTableName('apps_feedback');
        }else {
            $this->setTableName($tableName);
        }
        $this->setType(self::COMPONENT_TYPE_FORM_ADD);
        $this->setDataSetAction('send');
        $this->setTitle($this->translate('TXT_FEEDBACK_FORM'));
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

	        if ($result = $this->saveData($data)) {
	            $data = $data[$this->getTableName()];
	            $senderEmail = $data['feed_email'];

	            $this->dbh->modify(QAL::UPDATE, $this->getTableName(), array('feed_date'=>date('Y-m-d H:i:s')), array($this->getPK()=>$result));

                $userSubject = $this->getParam('userSubject');
                $userBody = $this->getParam('userBody');
                $adminSubject = $this->getParam('adminSubject');
                $adminBody = $this->getParam('adminBody');
                $recipientEmail = $this->getRecipientEmail();

	            $mailer = new Mail();
	            $mailer->setFrom($this->getConfigValue('mail.from'))->
	                setSubject($this->translate($userSubject))->
	                setText($this->translate($userBody), $data)->
	                addTo($senderEmail, $senderEmail)
	                ->send();
	            try {
	            	$mailer = new Mail();
	            	$data['feed_email']  = $senderEmail;
	                $mailer->setFrom($this->getConfigValue('mail.from'))->
	                    setSubject($this->translate($adminSubject))->
	                    setText($this->translate($adminBody),$data)->
	                    addTo($recipientEmail)->send();
	            }
	            catch (Exception $e){
	            }
	        }


	        $this->prepare();
	        
	        if ($this->getParam('textBlock') && ($textBlock = $this->document->componentManager->getBlockByName($this->getParam('textBlock')))) {
	        	$textBlock->disable();
	        }

	        $field = new Field('result');
	        $field->setData($this->translate('TXT_FEEDBACK_SUCCESS_SEND'));
	        $this->getData()->addField($field);

    	}
    	catch (Exception $e){
   			$this->response->redirectToCurrentSection();
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

}