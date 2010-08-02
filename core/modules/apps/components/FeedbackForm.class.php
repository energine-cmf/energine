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
     * @param Document $document
     * @param array $params
     * @access public
     */
    public function __construct($name, $module, Document $document,  array $params = null) {
        parent::__construct($name, $module, $document,  $params);

        $this->setTableName('apps_feedback');
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
        'textBlock' => false
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

	            $mailer = new Mail();
	            $mailer->setFrom($this->getConfigValue('mail.from'))->
	                setSubject($this->translate('TXT_SUBJ_FEEDBACK_USER'))->
	                setText($this->translate('TXT_BODY_FEEDBACK_USER'), $data)->
	                addTo($senderEmail, $senderEmail)
	                ->send();
	            try {
	            	$mailer = new Mail();
	            	$data['feed_email']  = $senderEmail;
	                $mailer->setFrom($this->getConfigValue('mail.from'))->
	                    setSubject($this->translate('TXT_SUBJ_FEEDBACK_ADMIN'))->
	                    setText($this->translate('TXT_BODY_FEEDBACK_ADMIN'),$data)->
	                    addTo($this->getConfigValue('mail.feedback'))->send();
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
}