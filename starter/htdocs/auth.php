<?php
use Energine\share\gears\UserSession, Energine\share\gears\Object, Energine\share\gears\User, Energine\user\gears\FBOAuth, Energine\user\gears\VKOAuth;
//на всякий пожарный проверяем реферрера
if (!isset($_SERVER['HTTP_REFERER']) && (!isset($_GET['return']))) {
    //не местных  - в сад
    exit;
}
require_once('../../vendor/autoload.php');
//подключаем bootstrap
require_once('bootstrap.php');

$response = E()->getResponse();
$response->disableCache();

if (
    ($login = (isset($_POST['user']['login']) &&
    isset($_POST['user']['username']) &&
    isset($_POST['user']['password'])))
    ||
    ($logout = (isset($_POST['user']['logout']) || isset($_GET['logout'])))
    ||
    ($fbAuth = isset($_GET['fbAuth']))
    ||
    ($vkAuth = isset($_GET['vkAuth']))
) {
    if ($login) {
        if ($UID = Energine\share\gears\AuthUser::authenticate(
            $_POST['user']['username'],
            $_POST['user']['password']
        )
        ) {
            /**
             * $response->addCookie()
             */
            call_user_func_array(
                array($response, 'addCookie'),
                $cookieInfo = UserSession::manuallyCreateSessionInfo($UID)
            );
        } else {
            $response->addCookie(UserSession::FAILED_LOGIN_COOKIE_NAME, 'bad auth data', time() + 60);
        }
        //о том прошла ли аутентификация успешноыны LoginForm узнает из куков
    } elseif ($logout) {
        UserSession::manuallyDeleteSessionInfo();
        //просто удаляем куку
        $response->deleteCookie(UserSession::DEFAULT_SESSION_NAME);
    } elseif (
        $fbAuth
        &&
        ($appID = Object::_getConfigValue('auth.fb.appID'))
        &&
        ($secretKey = Object::_getConfigValue('auth.fb.secretKey'))
    ) {
        try {
            $fb = new FBOAuth(array(
                'appId'     => $appID,
                'secret'    => $secretKey,
            ));
            $userInfo = $fb->api('/me?fields=id,name,email,picture.type(small)');
            if (is_array($userInfo) && !isset($_REQUEST['error'])) {
                //Смотрим есть ли такой юзер в списке зарегистрированных
                if (!($user = User::getFBUser($userInfo['id']))
                        && !($user = User::linkFBUserByEmail($userInfo['email'], $userInfo['id']))) {
                    //Если нет - создаем
                        $userData = array(
                            'u_name'        => (isset($userInfo['email']))
                                                    ? $userInfo['email']
                                                        : $userInfo['id'] . '@facebook.com',
                            'u_fbid'        => $userInfo['id'],
                            'u_password'    => User::generatePassword(),
                            'u_fullname'    => $userInfo['name'],
                            'u_avatar_img'  => $userInfo['picture']['data']['url']
                        );

                        $user = new User();
                        $user->create($userData);
                }
                call_user_func_array(
                    array($response, 'addCookie'),
                    UserSession::manuallyCreateSessionInfo($user->getID())
                );

            }
        }
        catch (\Exception $e) {
            $response->addCookie(UserSession::FAILED_LOGIN_COOKIE_NAME, $e->getMessage(), time() + 60);
            goto escape;
        }
    } elseif (
        $vkAuth
        &&
        ($appID = Object::_getConfigValue('auth.vk.appID'))
        &&
        ($secretKey = Object::_getConfigValue('auth.vk.secretKey'))
    ) {
        try {

            $vk = new VKOAuth(array(
                'appId' => $appID,
                'secret' => $secretKey,
            ),
                $_GET['return']
            );
            $vk->connect();
            if ($vkUID = $vk->getVKId()) {
                //Смотрим есть ли такой юзер в списке зарегистрированных
                if (!($user = User::getVKUser($vkUID))) {
                    //Если нет - создаем
                        $user = new User();
                        $res = $vk->api('users.get',
                            array(
                                'uids'   => $vkUID,
                                'fields' => 'uid,first_name,last_name,photo'
                            )
                        );
                        if(is_array($res['response'])) {
                            $vkUser = $res['response'][0];
                            $userInfo = array(
                                'u_name'        => $vkUser['uid'].'@vk.com',
                                'u_vkid'        => $vkUser['uid'],
                                'u_password'    => User::generatePassword(),
                                'u_fullname'    => $vkUser['first_name'] . ' ' . $vkUser['last_name'],
                                'u_avatar_img'  => $vkUser['photo'],
                            );
                            $user->create($userInfo);
                        }
                        else {
                            throw new SystemException('TXT_CREATE_SOCIAL_USER_ERROR');
                        }

                }
                call_user_func_array(
                    array($response, 'addCookie'),
                    UserSession::manuallyCreateSessionInfo($user->getID())
                );

            }
        }
        catch (\Exception $e) {
            $response->addCookie(UserSession::FAILED_LOGIN_COOKIE_NAME, $e->getMessage(), time() + 60);
            goto escape;
        }
    }
}
escape:
$response->goBack();