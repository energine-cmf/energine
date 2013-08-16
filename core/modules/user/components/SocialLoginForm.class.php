<?php
/**
 * Содержит класс SocialLoginForm
 *
 * @package energine
 * @subpackage user
 * @author Andrii A
 * @copyright Energine 2013
 * @version $Id$
 */


/**
 * Вывод формы авторизации с возможностью авторизации
 * через соц. сети
 *
 * @package energine
 * @subpackage user
 * @author Andrii A
 */
class SocialLoginForm extends LoginForm implements SampleLoginForm {
    /**
     * Вывод формы авторизации
     *
     * @return void
     * @access public
     */
    public function showLoginForm() {
        $this->prepare();
        if (isset($_COOKIE[UserSession::FAILED_LOGIN_COOKIE_NAME])) {
            $messageField = new FieldDescription('message');
            $messageField->setType(FieldDescription::FIELD_TYPE_STRING);
            $this->getDataDescription()->addFieldDescription($messageField);
            $messageField->setRights(FieldDescription::FIELD_MODE_READ);

            $messageField = new Field('message');
            $messageField->addRowData($this->translate('ERR_BAD_LOGIN'));
            $this->getData()->addField($messageField);
            E()->getResponse()->deleteCookie(UserSession::FAILED_LOGIN_COOKIE_NAME);
        }

        //Во избежание появления empty рекордсета
        $f = new Field('username');
        $f->setData('');
        $this->getData()->addField($f);
        //Если есть информация о авторизации через соц. сети
        foreach (array('fb', 'vk') as $socialType) {
            list($tbr) = array_values($this->getToolbar());
            if ($ctrl = $tbr->getControlByID('auth.' . $socialType)) {
                $ctrl->disable();
            }
            if ($ctrl && $this->getConfigValue('auth.' . $socialType)) {
                if (($appID = $this->getConfigValue('auth.' . $socialType . '.appID'))
                    && ($secretKey = $this->getConfigValue('auth.' . $socialType . '.appID'))
                ) {
                    $authClassName = strtoupper($socialType) . 'OAuth';
                    $auth = new $authClassName(array(
                        'appId' => $appID,
                        'secret' => $secretKey,
                    ));
                    $ctrl->setAttribute('loginUrl', $auth->getLoginUrl(
                        array(
                            'redirect_uri' => ($base = E()->getSiteManager()->getCurrentSite()->base)
                            . 'auth.php?' . $socialType . 'Auth&return='.(string)E()->getRequest()->getURI(),//((isset($_SERVER['HTTP_REFERER']))?$_SERVER['HTTP_REFERER']:$base),
                            'scope' => $ctrl->getAttribute('permissions')
                        )
                    ));
                    $ctrl->setAttribute('appID', $appID);
                    $ctrl->enable();
                }

            }
        }
    }
}