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

use Energine\share\components\DBDataSet,
    Energine\share\gears\User,
    Energine\share\gears\JSONCustomBuilder,
    Energine\share\gears\SystemException,
    Energine\share\gears\FieldDescription,
    Energine\share\gears\Data,
    Energine\share\gears\DataDescription,
    Energine\share\gears\Field,
    Energine\mail\gears\MailTemplate,
    Energine\mail\gears\Mail;

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
    public function __construct($name, array $params = NULL) {
        parent::__construct($name, $params);
        $this->setAction((string)$this->config->getStateConfig('save')->uri_patterns->pattern, true);
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
            [
                'active' => true,
                'noCaptcha' => false
            ]);
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
                ['COUNT(u_id) as number'],
                ['u_name' => $login]
            ),
            'number',
            true
        );
        $field = 'login';
        $message = ($result) ? $this->translate('TXT_LOGIN_AVAILABLE') : $this->translate('TXT_LOGIN_ENGAGED');
        $result = [
            'result' => $result,
            'message' => $message,
            'field' => $field,
        ];
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
            if (!$this->document->getUser()->isAuthenticated() && !$this->getParam('noCaptcha')) {
                $this->checkCaptcha();
            }
            $this->saveData();

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
        $gRecaptchaResponse = (isset($_POST['g-recaptcha-response'])) ? $_POST['g-recaptcha-response'] : false;

        $recaptcha = new \ReCaptcha\ReCaptcha($this->getConfigValue('recaptcha.private'));
        $resp = $recaptcha->verify($gRecaptchaResponse, $_SERVER["REMOTE_ADDR"]);
        if (!$resp->isSuccess()) {
            throw new SystemException($this->translate('TXT_BAD_CAPTCHA'), SystemException::ERR_CRITICAL, $resp->getErrorCodes());
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
        $this->getData()->load([array_merge(['error_message' => $errorMessage], $data)]);
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

            $template = new MailTemplate('user_registration', [
                    'user_login' => $this->user->getValue('u_name'),
                    'user_name' => $this->user->getValue('u_fullname'),
                    'user_password' => $password,
                    'site_url' => E()->getSiteManager()->getCurrentSite()->base,
                    'site_name' => $this->translate('TXT_SITE_NAME')
                ]
            );

            $mailer = new Mail();
            $mailer->setFrom($this->getConfigValue('mail.from'));
            $mailer->setSubject($template->getSubject());
            $mailer->setText($template->getBody());
            $mailer->setHtmlText($template->getHTMLBody());
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

        if ($this->getDataDescription()->getFieldDescriptionByName('u_name')) {
            $this->getDataDescription()->getFieldDescriptionByName('u_name')->setType(FieldDescription::FIELD_TYPE_EMAIL);
        }
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
