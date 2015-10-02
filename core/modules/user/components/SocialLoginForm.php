<?php
/**
 * @file
 * SocialLoginForm
 *
 * It contains the definition to:
 * @code
class SocialLoginForm;
 * @endcode
 *
 * @author Andrii A
 * @copyright Energine 2013
 *
 * @version 1.0.0
 */
namespace Energine\user\components;

use Energine\share\gears\Field;
use Energine\share\gears\FieldDescription;
use Energine\share\gears\UserSession;
use Energine\user\gears\IOAuth;

/**
 * Show authorization form with possibility to authorize over social networks.
 *
 * @code
class SocialLoginForm;
 * @endcode
 */
class SocialLoginForm extends LoginForm {
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
            $messageField->addRowData($_COOKIE[UserSession::FAILED_LOGIN_COOKIE_NAME]);
            $this->getData()->addField($messageField);
            E()->getResponse()->deleteCookie(UserSession::FAILED_LOGIN_COOKIE_NAME);
        }

        //Во избежание появления empty рекордсета
        $f = new Field('username');
        $f->setData('');
        $this->getData()->addField($f);
        //Если есть информация о авторизации через соц. сети
        foreach (['fb', 'vk', 'ok', 'goo'] as $socialType) {
            foreach (array_values($this->getToolbar()) as $tbr) {
                if ($ctrl = $tbr->getControlByID('auth.' . $socialType)) {
                    $ctrl->disable();
                }
                if ($ctrl && $this->getConfigValue('auth.' . $socialType)) {
                    if (($appID = $this->getConfigValue('auth.' . $socialType . '.appID'))
                        && ($secretKey = $this->getConfigValue('auth.' . $socialType . '.appID'))
                    ) {
                        $authClassName = 'Energine\\user\\gears\\' . strtoupper($socialType) . 'OAuth';
                        /**
                         * @var $auth IOAuth
                         */
                        $auth = new $authClassName([
                            'appId' => $appID,
                            'secret' => $secretKey,
                            'public' => $this->getConfigValue('auth.' . $socialType . '.publicKey'),
                        ]);
                        $ctrl->setAttribute('loginUrl', $auth->getLoginUrl(
                            [
                                'redirect_uri' => ($base = E()->getSiteManager()->getCurrentSite()->base)
                                    . 'auth.php?' . $socialType . 'Auth&return=' . $this->getReturnUrl(),
                                'scope' => $ctrl->getAttribute('permissions')
                            ]
                        ));
                        $ctrl->setAttribute('appID', $appID);
                        $ctrl->enable();
                    }
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
        if (!$returnUrl = $this->getParam('successAction')) {
            $returnUrl = (string)E()->getRequest()->getURI();
        }
        return $returnUrl;
    }
}