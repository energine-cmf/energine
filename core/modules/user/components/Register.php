<?php
/**
 * @file
 * Register
 *
 * It contains the definition to:
 * @code
class Register;
 * @endcode
 *
 * @author 1m.dm
 * @copyright Energine 2006
 *
 * @version 1.0.0
 */
namespace Energine\user\components;
use Energine\share\components\DBDataSet, Energine\share\gears\User, Energine\share\gears\JSONCustomBuilder, Energine\share\gears\SystemException, Energine\share\gears\FieldDescription, Energine\share\gears\Data, Energine\share\gears\DataDescription, Energine\share\gears\Field, Energine\share\gears\Mail;
/**
 * Registration form.
 *
 * @code
class Register;
 * @endcode
 */
class Register extends DBDataSet {
    /**
     * Exemplar of User class.
     * @var User $user
     */
    protected $user;

    /**
     * @copydoc DBDataSet::__construct
     */
    public function __construct($name, $module, array $params = null) {
        parent::__construct($name, $module, $params);
        $this->setAction('save-new-user');
        $this->setType(self::COMPONENT_TYPE_FORM_ADD);
        $this->user = new User();
        $this->setTableName(User::USER_TABLE_NAME);
        $this->addTranslation('TXT_ENTER_CAPTCHA');
        if (!$this->getTitle())
            $this->setTitle($this->translate(
                'TXT_' . strtoupper($this->getName())));
    }

    /**
     * @copydoc DBDataSet::defineParams
     */
    // Переопределен параметр active
    protected function defineParams() {
        $result = array_merge(parent::defineParams(),
            array(
                'active' => true,
            ));
        return $result;
    }

    /**
     * Check login.
     * It is called by AJAX by filling registration form.
     */
    protected function checkLogin() {
        $login = trim($_POST['login']);
        $result = !(bool)simplifyDBResult(
            $this->dbh->select(
                $this->getTableName(),
                array('COUNT(u_id) as number'),
                array('u_name' => $login)
            ),
            'number',
            true
        );
        $field = 'login';
        $message = ($result) ? $this->translate('TXT_LOGIN_AVAILABLE') : $this->translate('TXT_LOGIN_ENGAGED');
        $result = array(
            'result' => $result,
            'message' => $message,
            'field' => $field,
        );
        $builder = new JSONCustomBuilder();
        $this->setBuilder($builder);
        $builder->setProperties($result);
    }


    /**
     * Save.
     * It process all possible errors and redirect to the result page.
     */
    protected function save() {
        //inspect($_SESSION);
        try {
            $this->checkCaptcha();
            $this->saveData();
            $_SESSION['saved'] = true;
            $this->response->redirectToCurrentSection('success/');
        } catch (SystemException $e) {
            $this->failure($e->getMessage(), $_POST[$this->getTableName()]);
        }
    }

    /**
     * Check captcha.
     *
     * @throws SystemException
     */
    protected function checkCaptcha() {
        require_once('core/modules/share/gears/recaptchalib.php');
        $privatekey = $this->getConfigValue('recaptcha.private');
        $resp = recaptcha_check_answer($privatekey,
            $_SERVER["REMOTE_ADDR"],
            $_POST["recaptcha_challenge_field"],
            $_POST["recaptcha_response_field"]);

        if (!$resp->is_valid) {
            throw new SystemException($this->translate('TXT_BAD_CAPTCHA'), SystemException::ERR_CRITICAL);
        }
    }

    /**
     * Failure.
     *
     * @param string $errorMessage Error message.
     * @param mixed $data Data.
     */
    protected function failure($errorMessage, $data) {
        $this->getConfig()->setCurrentState('main');
        $this->prepare();
        $eFD = new FieldDescription('error_message');
        $eFD->setMode(FieldDescription::FIELD_MODE_READ);
        $eFD->setType(FieldDescription::FIELD_TYPE_STRING);
        $this->getDataDescription()->addFieldDescription($eFD);
        $this->getData()->load(array(array_merge(array('error_message' => $errorMessage), $data)));
        $this->getDataDescription()->getFieldDescriptionByName('error_message')->removeProperty('title');
    }


    /**
     * Save data.
     *
     * @throws SystemException
     */
    protected function saveData() {
        $password = $_POST[$this->getTableName()]['u_password'] = User::generatePassword();
        try {
            $result = $this->user->create($_POST[$this->getTableName()]);

            $mailer = new Mail();
            $mailer->setFrom($this->getConfigValue('mail.from'));
            $mailer->setSubject($this->translate('TXT_SUBJ_REGISTER'));
            $mailer->setText(
                $this->translate('TXT_BODY_REGISTER'),
                array(
                    'login' => $this->user->getValue('u_name'),
                    'name' => $this->user->getValue('u_fullname'),
                    'password' => $password
                )
            );
            $mailer->addTo($this->user->getValue('u_name'));
            $mailer->send();

        } catch (\Exception $error) {
            throw new SystemException($error->getMessage(), SystemException::ERR_WARNING);
        }
    }

    /**
     * @copydoc DBDataSet::prepare
     */
    // Получает список доступных полей из таблицы пользователей и генерит форму
    protected function prepare() {
        parent::prepare();
        //u_id и u_is_active нам не нужны ни при каких раскладах
        if ($this->getDataDescription()->getFieldDescriptionByName('u_id')) {
            $this->getDataDescription()->removeFieldDescription($this->getDataDescription()->getFieldDescriptionByName('u_id'));
        }

        if ($this->getDataDescription()->getFieldDescriptionByName('u_is_active')) {
            $this->getDataDescription()->removeFieldDescription($this->getDataDescription()->getFieldDescriptionByName('u_is_active'));
        }
        //Тут таки нужно вернуться к параметру confirmationNeeded
        if ($this->getDataDescription()->getFieldDescriptionByName('u_password')) {
            $this->getDataDescription()->removeFieldDescription($this->getDataDescription()->getFieldDescriptionByName('u_password'));
        }
        $this->getDataDescription()->getFieldDescriptionByName('u_name')->setType(FieldDescription::FIELD_TYPE_EMAIL);
    }

    /**
     * Show registration result.
     */
    protected function success() {
        //если в сессии нет переменной saved значит этот метод пытаются вызвать напрямую. Не выйдет!
        /*if (!isset($_SESSION['saved'])) {
            throw new SystemException('ERR_404', SystemException::ERR_404);
        }*/
        //unset($_SESSION['saved']);
        if ($textBlock = $this->document->componentManager->getBlockByName('RegTextBlock')) {
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
        $di->setData($this->translate('TXT_USER_REGISTRED'));
        $data->addField($di);

        $this->setDataDescription($dataDescription);
        $this->setData($data);
    }


}
