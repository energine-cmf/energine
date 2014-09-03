<?php
/**
 * @file
 * SocialLoginForm
 *
 * It contains the definition to:
 * @code
class SocialLoginForm;
@endcode
 *
 * @author Andrii A
 * @copyright Energine 2013
 *
 * @version 1.0.0
 */
namespace Energine\user\components;
use Energine\share\gears\Field;

/**
 * Show authorization form with possibility to authorize over social networks.
 *
 * @code
class SocialLoginForm;
@endcode
 */
class SocialLoginForm extends LoginForm implements SampleLoginForm {
    /**
     * @copydoc LoginForm::showLoginForm
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
        foreach (array('fb', 'vk', 'ok') as $socialType) {
            list($tbr) = array_values($this->getToolbar());
            if ($ctrl = $tbr->getControlByID('auth.' . $socialType)) {
                $ctrl->disable();
            }
            if ($ctrl && $this->getConfigValue('auth.' . $socialType)) {
                if (($appID = $this->getConfigValue('auth.' . $socialType . '.appID'))
                    && ($secretKey = $this->getConfigValue('auth.' . $socialType . '.appID'))
                ) {
                    $authClassName = 'Energine\\share\\gears\\'.strtoupper($socialType) . 'OAuth';
                    $auth = new $authClassName(array(
                        'appId' => $appID,
                        'secret' => $secretKey,
                        'public' => $this->getConfigValue('auth.' . $socialType . '.publicKey'),
                    ));
                    $ctrl->setAttribute('loginUrl', $auth->getLoginUrl(
                        array(
                            'redirect_uri' => ($base = E()->getSiteManager()->getCurrentSite()->base)
                            . 'auth.php?' . $socialType . 'Auth&return=' . $this->getReturnUrl(),
                            'scope' => $ctrl->getAttribute('permissions')
                        )
                    ));
                    $ctrl->setAttribute('appID', $appID);
                    $ctrl->enable();
                }

            }
        }
    }

    /**
     * Get return URL.
     *
     * @return mixed|string
     */
    private function getReturnUrl() {
        if(!$returnUrl = $this->getParam('successAction')) {
            $returnUrl = (string)E()->getRequest()->getURI();
        }
        return $returnUrl;
    }
}