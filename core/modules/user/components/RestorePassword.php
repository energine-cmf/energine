<?php
/**
 * @file
 * RestorePassword
 *
 * It contains the definition to:
 * @code
class RestorePassword;
@endcode
 *
 * @author dr.Pavka
 * @copyright Energine 2006
 *
 * @version 1.0.0
 */
namespace Energine\user\components;
use Energine\share\components\DataSet, Energine\share\gears\User, Energine\share\gears\QAL, Energine\share\gears\Field, Energine\share\gears\Mail;

/**
 * Form to restoring password.
 *
 * @code
class RestorePassword;
@endcode
 */
class RestorePassword extends DataSet {
    /**
     * @copydoc DataSet::__construct
     */
    public function __construct($name, $module,   array $params = null) {
        parent::__construct($name, $module,  $params);
        $this->setAction('send');
    }

    /**
     * @copydoc DataSet::defineParams
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
     * Send new password to defined E-Mail.
     */
    protected function send() {
        if($crumbComponent = $this->document->componentManager->getBlockByName('breadCrumbs')) {
            $crumbComponent->addCrumb();
        }
        if ($component = $this->document->componentManager->getBlockByName('textBlockRestorePassword')) {
         	$component->disable();
         }
        if (!isset($_POST['u_name'])) {
            $message = $this->translate('ERR_NO_U_NAME');
        }
        else {
            $uName = $_POST['u_name'];
            $UID = simplifyDBResult($this->dbh->select('user_users', 'u_id', array('u_name'=>$uName)), 'u_id', true);
            if (!$UID) {
                $message = $this->translate('ERR_NO_U_NAME');
            }
            else {
                $password = User::generatePassword();
                $this->dbh->modify(QAL::UPDATE, 'user_users', array('u_password'=>sha1($password)), array('u_id'=>$UID));
                $mailer = new Mail();
                $mailer->setFrom($this->getConfigValue('mail.from'))->
                    setSubject($this->translate('TXT_SUBJ_RESTORE_PASSWORD'))->
                    setText($this->translate('TXT_BODY_RESTORE_PASSWORD'),compact('password'))->
                    addTo($uName);
                $message = $this->translate('MSG_PASSWORD_SENT');
                try {
                    $mailer->send();
                }
                catch (\Exception $e) {
                    $message = $e->getMessage();
                }
            }
        }
        $this->prepare();
        $messageField = new Field('restore_password_result');
        $messageField->setData($message);
        $this->getData()->addField($messageField);
    }
}
