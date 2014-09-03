<?php
/**
 * @file
 * FeedbackForm
 *
 * It contains the definition to:
 * @code
class FeedbackForm;
@endcode
 *
 * @author dr.Pavka
 * @copyright Energine 2007
 *
 * @version 1.0.0
 */
namespace Energine\apps\components;
use Energine\share\components\DBDataSet, Energine\share\gears\DataDescription, Energine\share\gears\Data, Energine\share\gears\Saver, Energine\share\gears\SystemException, Energine\share\gears\QAL, Energine\share\gears\FieldDescription, Energine\share\gears\Field, Energine\share\gears\Mail;
/**
 * Form for feedback.
 *
 * @code
class FeedbackForm;
@endcode
 */
class FeedbackForm extends DBDataSet {
    /**
     * @copydoc DBDataSet::__construct
     */
    public function __construct($name, $module, array $params = null) {
        parent::__construct($name, $module, $params);
        //$tableName = $this->getParam('tableName');

        /*if(!($tableName)){
            $this->setTableName('apps_feedback');
        }else {
            $this->setTableName($tableName);
        }*/
        $this->setType(self::COMPONENT_TYPE_FORM_ADD);
        $this->setAction('send');
        $this->setTitle($this->translate('TXT_FEEDBACK_FORM'));
        $this->addTranslation('TXT_ENTER_CAPTCHA');
    }

    /**
     * @copydoc DBDataSet::defineParams
     */
    // Переопределен параметр active
    protected function defineParams() {
        $result = array_merge(parent::defineParams(),
            array(
                'active' => true,
                'textBlock' => false,
                'tableName' => 'apps_feedback',
                'recipientEmail' => false,
                'userSubject' => 'TXT_SUBJ_FEEDBACK_USER',
                'userBody' => 'TXT_BODY_FEEDBACK_USER',
                'adminSubject' => 'TXT_SUBJ_FEEDBACK_ADMIN',
                'adminBody' => 'TXT_BODY_FEEDBACK_ADMIN',
                'noCaptcha' => false
            ));
        return $result;
    }

    /**
     * Save data.
     *
     * @param array $data Data.
     * @return bool|mixed
     *
     * @throws SystemException 'ERR_VALIDATE_FORM'
     */
    protected function saveData($data) {
        $result = false;
        //создаем объект описания данных
        $dataDescriptionObject = new DataDescription();

        //получаем описание полей для метода
        $configDataDescription =
                $this->getConfig()->getStateConfig($this->getPreviousState());
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

        if ($this->saver->validate() === true) {
            $this->saver->setFilter($this->getFilter());
            $this->saver->save();
            $result = $this->saver->getResult();

        }
        else {
            //выдвигается пустой exception который перехватывается в методе save
            throw new SystemException('ERR_VALIDATE_FORM', SystemException::ERR_WARNING, $this->saver->getErrors());
        }

        return $result;

    }

    /**
     * Send feedback.
     * It stores the access to database, sends message to the user and administrator.
     */
    protected function send() {

        if(!isset($_POST[$this->getTableName()])){
            E()->getResponse()->redirectToCurrentSection();
        }
        try {
            $data[$this->getTableName()] = $_POST[$this->getTableName()];
            
            if (!$this->document->getUser()->isAuthenticated() && !$this->getParam('noCaptcha')) {
                $this->checkCaptcha();
            }
            if ($result = $this->saveData($data)) {
                $data = $data[$this->getTableName()];
                $senderEmail = '';
                if (isset($data['feed_email'])) {
                    $senderEmail = $data['feed_email'];
                } else {
                    $data['feed_email'] =
                            $this->translate('TXT_NO_EMAIL_ENTERED');
                }

                $this->dbh->modify(QAL::UPDATE, $this->getTableName(), array('feed_date' => date('Y-m-d H:i:s')), array($this->getPK() => $result));
                if ($senderEmail) {
                    $mailer = new Mail();
                    $mailer->setFrom($this->getConfigValue('mail.from'))->
                            setSubject($this->translate($this->getParam('userSubject')))->
                            setText($this->translate($this->getParam('userBody')), $data)->
                            addTo($senderEmail, $senderEmail)
                            ->send();
                }
                try {
                    $mailer = new Mail();
                    $recipientID = false;
                    if (isset($data['feed_type']) &&
                            intval($data['feed_type'])) {
                        $recipientID = $data['feed_type'];
                    }
                    $mailer->setFrom($this->getConfigValue('mail.from'))->
                            setSubject($this->translate($this->getParam('adminSubject')))->
                            setText($this->translate($this->getParam('adminBody')), $data)->
                            addTo($this->getRecipientEmail($recipientID))->send();
                }
                catch (\Exception $e) {
                }
            }


            $this->prepare();

            if ($this->getParam('textBlock') && ($textBlock =
                    $this->document->componentManager->getBlockByName($this->getParam('textBlock')))) {
                $textBlock->disable();
            }

            $this->response->redirectToCurrentSection('success/');

        }
        catch (Exception $e) {
            $this->failure($e->getMessage(), $data[$this->getTableName()]);
        }
    }

    //todo VZ: input argument is not used.
    /**
     * Get recipient E-Mail.
     *
     * @param bool $options Options.
     * @return string
     */
    protected function getRecipientEmail($options = false) {
        return $this->getParam('recipientEmail');
    }

    /**
     * Failure.
     *
     * @param string $errorMessage Error message.
     * @param mixed $data Data.
     */
    // Викликаємо у випадку помилки з captcha
    protected function failure($errorMessage, $data) {
        $this->getConfig()->setCurrentState('main');
        $this->prepare();
        $eFD = new FieldDescription('error_message');
        $eFD->setMode(FieldDescription::FIELD_MODE_READ);
        $eFD->setType(FieldDescription::FIELD_TYPE_CUSTOM);
        $this->getDataDescription()->addFieldDescription($eFD);
        $this->getData()->load(array(array_merge(array('error_message' => $errorMessage), $data)));
        $this->getDataDescription()->getFieldDescriptionByName('error_message')->removeProperty('title');
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
     * @copydoc DBDataSet::prepare
     */
    protected function prepare() {
        parent::prepare();
        if ($this->document->getUser()->isAuthenticated()
            && ($captcha =
                    $this->getDataDescription()->getFieldDescriptionByName('captcha'))
        ) {
            $this->getDataDescription()->removeFieldDescription($captcha);
        }
    }

    /**
     * Success.
     */
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