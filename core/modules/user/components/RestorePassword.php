<?php
/**
 * @file
 * RestorePassword
 *
 * It contains the definition to:
 * @code
class RestorePassword;
 * @endcode
 *
 * @author dr.Pavka
 * @copyright Energine 2006
 *
 * @version 1.0.0
 */
namespace Energine\user\components;

use Energine\share\components\DataSet,
    Energine\share\gears\User,
    Energine\share\gears\QAL,
    Energine\share\gears\Field,
    Energine\mail\gears\MailTemplate,
    Energine\mail\gears\Mail;

/**
 * Form to restoring password.
 *
 * @code
class RestorePassword;
 * @endcode
 */
class RestorePassword extends DataSet {
    /**
     * @copydoc DataSet::__construct
     */
    public function __construct($name, array $params = null) {
        parent::__construct($name, $params);
        $this->setAction('send');
    }

    /**
     * @copydoc DataSet::defineParams
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
     * Send new password to defined E-Mail.
     */
    protected function send() {
        if ($crumbComponent = $this->document->componentManager->getBlockByName('breadCrumbs')) {
            $crumbComponent->addCrumb();
        }
        if ($component = $this->document->componentManager->getBlockByName('textBlockRestorePassword')) {
            $component->disable();
        }
        if (!isset($_POST['u_name'])) {
            $message = $this->translate('ERR_NO_U_NAME');
        } else {
            $uName = $_POST['u_name'];

            if (!($UID = $this->dbh->getScalar('user_users', 'u_id', array('u_name'=>$uName)))) {
                $message = $this->translate('ERR_NO_U_NAME');
            } else {
                $password = User::generatePassword();
                $this->dbh->modify(QAL::UPDATE, 'user_users', array('u_password' => password_hash($password, PASSWORD_DEFAULT)), array('u_id' => $UID));

                $template = new MailTemplate('user_restore_password', [
                    'user_login' => $uName,
                    'user_password' => $password,
                    'site_url' => E()->SiteManager->getCurrentSite()->base,
                    'site_name' => $this->translate('TXT_SITE_NAME')
                ]);

                $mailer = new Mail();
                $mailer
                    ->setFrom($this->getConfigValue('mail.from'))
                    ->setSubject($template->getSubject())
                    ->setText($template->getBody())
                    ->setHtmlText($template->getHTMLBody())
                    ->addTo($uName);
                $message = $this->translate('MSG_PASSWORD_SENT');
                try {
                    $mailer->send();
                } catch (\Exception $e) {
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
