<?php
/**
 * Содержит класс UserProfile
 *
 * @package energine
 * @subpackage user
 * @author dr.Pavka
 * @copyright Energine 2006
 * @version $Id$
 */

//require_once('core/modules/share/components/DBDataSet.class.php');
//require_once('core/framework/AuthUser.class.php');

/**
 * Форма редактирования данных пользователя
 *
 * @package energine
 * @subpackage user
 * @author dr.Pavka
 */
class UserProfile extends DBDataSet {
    /**
     * Конструктор класса
     *
     * @return void
     * @access public
     */
    public function __construct($name, $module, Document $document,  array $params = null) {
        parent::__construct($name, $module, $document,  $params);
        $this->setTableName('user_users');
        $this->setType(self::COMPONENT_TYPE_FORM_ALTER);
    }


    /**
	 * Действие по умолчанию
	 *
	 * @return type
	 * @access protected
	 */

    protected function main() {
    	if (!$this->document->user->isAuthenticated()) {
            throw new SystemException('ERR_DEV_NO_AUTH_USER', SystemException::ERR_DEVELOPER);
        }
        $this->setFilter($this->document->user->getID());
        
        $this->setDataSetAction('save-user');
        $this->setTitle($this->translate('TXT_USER_PROFILE'));
        $this->addTranslation('MSG_PWD_MISMATCH');
        $this->prepare();
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
	 * Метод сохранения
	 * Переписан родительский
	 *
	 * @return void
	 * @access protected
	 */

    protected function save() {
        if($this->document->user->getValue('u_password') != sha1($_POST[$this->getTableName()]['u_password'])) {
            $_SESSION['error'] = true;
            $this->response->redirectToCurrentSection('error/');
        }

        if (!empty($_POST[$this->getTableName()]['u_password'])) {
            if ($_POST[$this->getTableName()]['u_password'] != $_POST['u_password2']) {
            	$this->generateError(SystemException::ERR_WARNING, 'ERR_PWD_MISMATCH');
            }
            unset($_POST['u_password2']);
            $_POST[$this->getTableName()]['u_password'] = sha1($_POST[$this->getTableName()]['u_password']);
        }
        $_POST[$this->getTableName()]['u_id'] = $this->document->getUser()->getID();

        $this->prepare();
        $fields = $this->getDataDescription()->getFieldDescriptionList();
        if (array_diff($fields, array_keys($_POST[$this->getTableName()])) != array()) {
            throw new SystemException('ERR_BAD_DATA', SystemException::ERR_CRITICAL);
        }

        $data = $_POST[$this->getTableName()];

        try {
            $this->document->user->update($data);
            $_SESSION['saved'] = true;

            //переадресация
            $this->response->redirectToCurrentSection('success/');
        }
        //Отлавливаем все ошибки которые могли произойти при сохранении в БД, чтобы вывести нужную информацию об ошибке на уровне компонента
        catch (FormException $formError) {
            $errors = $this->saver->getErrors();
            foreach ($errors as $errorFieldName) {
                $message = $this->saver->getDataDescription()->getFieldDescriptionByName($errorFieldName)->getPropertyValue('message');
                $this->generateError(SystemException::ERR_NOTICE, $message);
            }
            //переадресация
            $this->response->redirectToCurrentSection();
        }
        catch (SystemException $e){
            $this->generateError(SystemException::ERR_NOTICE, $e->getMessage(), $e->getCustomMessage());
            //переадресация
            $this->response->redirectToCurrentSection();
        }
    }

    /**
	 * Метод, выводящий сообщение об успешном сохранении данных
	 *
	 * @return void
	 * @access protected
	 */

    protected function success() {
        //если в сессии нет переменной saved, значит этот метод пытаются дернуть напрямую. Не выйдет!
        if (!isset($_SESSION['saved'])) {
            throw new SystemException('ERR_404', SystemException::ERR_404);
        }
        //Мавр сделал свое дело...
        unset($_SESSION['saved']);

        $this->setBuilder($this->createBuilder());

        $dd = new DataDescription();
        $this->setDataDescription($dd);

        $ddi = new FieldDescription('success_message');
        $ddi->setType(FieldDescription::FIELD_TYPE_TEXT);
        $ddi->setMode(FieldDescription::FIELD_MODE_READ);
        $ddi->removeProperty('title');
        $dd->addFieldDescription($ddi);

        $d = new Data();
        $this->setData($d);

        $di = new Field('success_message');
        $di->setData($this->translate('TXT_USER_PROFILE_SAVED'));
        $d->addField($di);

        $this->document->componentManager->getBlockByName('breadCrumbs')->addCrumb();
    }


    /**
	 * Метод, выводящий сообщение о неверно введенном пароле
	 *
	 * @return void
	 * @access protected
	 */

    protected function error() {
        //если в сессии нет переменной error, значит этот метод пытаются дернуть напрямую. Не выйдет!
        if (!isset($_SESSION['error'])) {
            throw new SystemException('ERR_404', SystemException::ERR_404);
        }
        //Мавр сделал свое дело...
        unset($_SESSION['error']);

        $this->setBuilder($this->createBuilder());

        $dd = new DataDescription();
        $this->setDataDescription($dd);

        $ddi = new FieldDescription('error_message');
        $ddi->setType(FieldDescription::FIELD_TYPE_TEXT);
        $ddi->setMode(FieldDescription::FIELD_MODE_READ);
        $ddi->removeProperty('title');
        $dd->addFieldDescription($ddi);

        $d = new Data();
        $this->setData($d);

        $di = new Field('error_message');
        $di->setData($this->translate('TXT_USER_PROFILE_WRONG_PWD'));
        $d->addField($di);

        $this->document->componentManager->getBlockByName('breadCrumbs')->addCrumb();
    }

    /**
     * Для метода success переопределен метод создания объекта метаданных
     *
     * @return DataDescription
     * @access protected
     */

    protected function createDataDescription() {
        $result = parent::createdataDescription();
        if ($field = $result->getFieldDescriptionByName('u_is_active')) {
        	$result->removeFieldDescription($field);
        }

        $field = $result->getFieldDescriptionByName('u_password');
        $field->setProperty('message2', $this->translate('ERR_PWD_MISMATCH'));
        $result->removeFieldDescription($field);
        $result->addFieldDescription($field);

        if ($this->getAction() !== 'save') {
        	$field = new FieldDescription('u_password2');
            $field->setProperty('message2', $this->translate('ERR_PWD_MISMATCH'));
            $field->setType(FieldDescription::FIELD_TYPE_PWD);
            $field->setProperty('customField', true);
            $field->setProperty('title', $this->translate('FIELD_U_PASSWORD2'));
            $result->addFieldDescription($field);
        }


        return $result;
    }

    /**
     * Для метода success создаем свой объект данных
     *
     * @return Data
     * @access protected
     */

    protected function createData() {
        $result = parent::createData();
        $result->getFieldByName('u_password')->setData('');
        return $result;
    }
}

