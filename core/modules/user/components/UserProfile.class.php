<?php
/**
 * @file
 * UserProfile
 *
 * It contains the definition to:
 * @code
class UserProfile;
@endcode
 *
 * @author dr.Pavka
 * @copyright Energine 2006
 *
 * @version 1.0.0
 */
namespace Energine\user\components;
use Energine\share\components\DBDataSet, Energine\share\gears\SystemException, Energine\share\gears\DataDescription, Energine\share\gears\Data, Energine\share\gears\FieldDescription, Energine\share\gears\Field;

/**
 * form to edit user profile.
 *
 * @code
class UserProfile;
@endcode
 */
class UserProfile extends DBDataSet {
    /**
     * @copydoc DBDataSet::__construct
     */
    public function __construct($name, $module,   array $params = null) {
        parent::__construct($name, $module,  $params);
        $this->setTableName('user_users');
        $this->setType(self::COMPONENT_TYPE_FORM_ALTER);
    }


    /**
     * @copydoc DBDataSet::main
     *
     * @throws SystemException 'ERR_DEV_NO_AUTH_USER'
     */
    protected function main() {
    	if (!$this->document->user->isAuthenticated()) {
            throw new SystemException('ERR_DEV_NO_AUTH_USER', SystemException::ERR_DEVELOPER);
        }
        $this->setFilter($this->document->user->getID());
        
        $this->setAction('save-user');
        $this->setTitle($this->translate('TXT_USER_PROFILE'));
        $this->prepare();

    }

    /**
     * @copydoc DBDataSet::defineParams
     */
    // Переопределен параметр active
    protected function defineParams() {
        $result = array_merge(parent::defineParams(),
        array(
        'active'=>true,
        ));
        return $result;
    }


    /**
     * Save.
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
        /*
        if (array_diff($fields, array_keys($_POST[$this->getTableName()])) != array()) {
            throw new SystemException('ERR_BAD_DATA', SystemException::ERR_CRITICAL);
        }
        */
        $data = $_POST[$this->getTableName()];

        try {
            $this->document->user->update($data);
            $_SESSION['saved'] = true;

            //переадресация
            $this->response->redirectToCurrentSection('success/');
        }
        //Отлавливаем все ошибки которые могли произойти при сохранении в БД, чтобы вывести нужную информацию об ошибке на уровне компонента
        /*catch (FormException $formError) {
            $errors = $this->saver->getErrors();
            foreach ($errors as $errorFieldName) {
                $message = $this->saver->getDataDescription()->getFieldDescriptionByName($errorFieldName)->getPropertyValue('message');
                $this->generateError(SystemException::ERR_NOTICE, $message);
            }
            //переадресация
            //$this->response->redirectToCurrentSection();
        }*/
        catch (SystemException $e){
            stop($e);
            $this->generateError(SystemException::ERR_NOTICE, $e->getMessage(), $e->getCustomMessage());
            //переадресация
            //$this->response->redirectToCurrentSection();
        }
    }

    /**
     * Show message about successful saving data.
     *
     * @throws SystemException 'ERR_404'
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
     * Show message about incorrect password.
     *
     * @throws SystemException 'ERR_404'
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
     * @copydoc DBDataSet::createDataDescription
     */
    // Для метода success переопределен метод создания объекта метаданных
    protected function createDataDescription() {
        $result = parent::createdataDescription();
        if ($field = $result->getFieldDescriptionByName('u_is_active')) {
        	$result->removeFieldDescription($field);
        }

        $field = $result->getFieldDescriptionByName('u_password');
        $field->setProperty('message2', $this->translate('ERR_PWD_MISMATCH'));
        $result->removeFieldDescription($field);
        $result->addFieldDescription($field);

        if ($this->getState() !== 'save') {
        	$field = new FieldDescription('u_password2');
            $field->setProperty('message2', $this->translate('ERR_PWD_MISMATCH'));
            $field->setType(FieldDescription::FIELD_TYPE_PWD);
            $field->setProperty('customField', true);
            //$field->setProperty('title', $this->translate('FIELD_U_PASSWORD2'));
            $field->setProperty('title', 'FIELD_U_PASSWORD2');
            $result->addFieldDescription($field);
        }


        return $result;
    }

    /**
     * @copydoc DBDataSet::createData
     */
    // Для метода success создаем свой объект данных
    protected function createData() {
        $result = parent::createData();
        $result->getFieldByName('u_password')->setData('');
        return $result;
    }
}

