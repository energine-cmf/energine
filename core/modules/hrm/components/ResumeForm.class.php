<?php
/**
 * Содержит класс ResumeForm.
 *
 * @package energine
 * @subpackage misc
 * @author d.pavka
 * @copyright d.pavka@gmal.com
 * @version $Id$
 */

/**
 * Класс для отправки и хранения резюме, а также просмотра отправленных резюме
 *
 * @package energine
 * @subpackage misc
 * @author d.pavka
 * @final
 */
final class ResumeForm extends DataSet {
	const RESUME_TABLE_NAME = 'hrm_resumes';
	private $jevix;

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
		$params['active'] = true;
		parent::__construct($name, $module, $document,  $params);
		$this->setDataSetAction('send-resume');
		$this->setTitle($this->translate('TXT_'.strtoupper($this->getName())));
	}
	/**
	 * Добавляем параметр vacanciesFeed - имя компонента списка вакансий
	 * Позволяет связать форму со списком вакансий
	 * В режиме отображения полного текста вакансии в форме резюме автоматически устанавливается
	 * соответствующая вакансия 
	 *
	 * @access protected
	 * @return array
	 */
	protected function defineParams(){
		return array_merge(
		parent::defineParams(),
		array(
                'vacancies' => false
		)
		);
	}

	/**
	 * Подхватываем перечень полей из таблицы
	 *
	 * @access protected
	 * @return DataDescription
	 */
	protected function loadDataDescription() {
		$result = $this->dbh->getColumnsInfo(self::RESUME_TABLE_NAME);
		unset($result['resume_date']);
		return $result;
	}

	/**
	 * добавлена обработка ключей
	 *
	 * @return DataDescription
	 * @access protected
	 *
	 * @todo дублирование ф-ности доступной в DBDataSet
	 */

	protected function createDataDescription() {
		$result = parent::createDataDescription();

		foreach ($result->getFieldDescriptions() as $fieldName => $fieldMetaData) {
			$keyInfo = $fieldMetaData->getPropertyValue('key');
			//Если это внешний ключ
			if (is_array($keyInfo) && in_array($fieldMetaData->getType(), array(FieldDescription::FIELD_TYPE_SELECT, FieldDescription::FIELD_TYPE_MULTI))) {
				$fkTableName = $keyInfo['tableName'];
				$fkKeyName = $keyInfo['fieldName'];
				//загружаем информацию о возможных значениях
				call_user_func_array(
				array($fieldMetaData, 'loadAvailableValues'),
				$this->dbh->getForeignKeyData(
				$fkTableName,
				$fkKeyName,
				$this->document->getLang(),
				array('vacancy_is_active' => 1)
				)
				);
			}
		}
		if($this->document->getUser()->isAuthenticated() && ($captcha = $result->getFieldDescriptionByName('captcha')) ){
            $result->removeFieldDescription($captcha);		    	
		}
		
		return $result;
	}

	protected function createData(){
		$result = parent::createData();
		if(
		$this->getParam('vacancies')
		&&
		($component = $this->document->componentManager->getComponentByName($this->getParam('vacancies')))
		&& ($component->getAction() == 'view')
		){
			if(!$result){
				$result = new Data();
				$field = new Field('vacancy_id');
				//Определяем идентфикатор вакансии 
				//по сегменту вакансии
				$vacancyID = simplifyDBResult($this->dbh->select(
                    'hrm_vacancies',
                    'vacancy_id',
				$component->getFilter()
				), 'vacancy_id', true);
				$field->setData($vacancyID);
					
				$result->addField($field);
			}

		}
		return $result;
	}
	

	/**
	 * Отправка резюме
	 *
	 * @access protected
	 * @return мщшв
	 */
	protected function send() {
		try{
			$this->checkCaptcha();
			
			if(!isset($_POST[self::RESUME_TABLE_NAME ])){
				throw new SystemException('MSG_NO_RESUME_SEND', SystemException::ERR_CRITICAL);
			}
			$data = $_POST[self::RESUME_TABLE_NAME ];
			$data['resume_date'] = date('r');
	
			$this->jevix = new Jevix();
			$this->jevix->cfgSetAutoBrMode(false);
			$this->jevix->cfgSetAutoLinkMode(false);
			$this->jevix->cfgSetXHTMLMode(true);
			$this->jevix->cfgSetTagCutWithContent(array('script', 'iframe'));
		  
			$data = array_map(array($this, 'cleanInputData'), $data);
	
			if(isset($_FILES['file'])){
				try {
					$uploader = new FileUploader();
					$uploader->setFile($_FILES['file']);
					$uploader->upload('uploads/private/');
					$data['resume_main_pfile'] = $uploader->getFileObjectName();
				}
				catch (Exception $e){
					//Если с файлом что то не так
					//и хрен с ни
				}
			}
			$data['resume_date'] = date('Y-m-d');
			$this->dbh->modify(QAL::INSERT, self::RESUME_TABLE_NAME, $data);
	
			$mail = new Mail();
			$mail->setFrom($this->getConfigValue('mail.from'));
			$mail->addTo($this->getConfigValue('mail.vacancy_email'));
			$mail->setSubject($this->translate('TXT_SUBJ_NEW_RESUME'));
			$mail->addReplyTo($data['resume_candidate_email'], $data['resume_candidate_name']);
			$mail->setText($this->translate('TXT_BODY_NEW_RESUME'), $data);
			if(isset($_FILES['file'])){
				$mail->addAttachment($_FILES['file']['tmp_name'], $_FILES['file']['name']);
			}
	
			$mail->send();
			$_SESSION['saved'] = true;
	        $this->response->redirectToCurrentSection('success/');
		}
		catch(SystemException $e){
			$this->failure($e->getMessage(), (isset($data))?$data:array());
		}
		
	}
	
	protected function failure($errorMessage, $data){
        $this->config->setCurrentMethod('main');
        $this->prepare();
        $eFD = new FieldDescription('error_message');
        $eFD->setMode(FieldDescription::FIELD_MODE_READ);
        $eFD->setType(FieldDescription::FIELD_TYPE_CUSTOM);
        $this->getDataDescription()->addFieldDescription($eFD);
        $this->getData()->load(array(array_merge(array('error_message' => $errorMessage), $data)));
            
    }
 /**
     * Выводит результат отправки сообщения
     *
     * @return void
     * @access protected
     */
    protected function success() {
        //если в сессии нет переменной saved значит этот метод пытаются вызвать напрямую. Не выйдет!
        if (!isset($_SESSION['saved'])) {
            throw new SystemException('ERR_404', SystemException::ERR_404);
        }
        //unset($_SESSION['saved']);
        if ($this->getParam('textBlock') && ($textBlock = $this->document->componentManager->getComponentByName($this->getParam('textBlock')))) {
                $textBlock->disable();
        }
        $this->setBuilder($this->createBuilder());

        $dataDescription = new DataDescription();
        $ddi = new FieldDescription('success_message');
        $ddi->setType(FieldDescription::FIELD_TYPE_TEXT);
        $ddi->setMode(FieldDescription::FIELD_MODE_READ);
        $ddi->removeProperty('title');
        $dataDescription->addFieldDescription($ddi);

        $data = new Data();
        $di = new Field('success_message');
        $di->setData($this->translate('MSG_RESUME_SENT'));
        $data->addField($di);

        $this->setDataDescription($dataDescription);
        $this->setData($data);
    }   

	private function cleanInputData($value){
		$errors = false;
		return $this->jevix->parse($value, $errors);
	}

}