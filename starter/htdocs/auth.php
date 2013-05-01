<?php
//на всякий пожарный проверяем реферрера
if (!isset($_SERVER['HTTP_REFERER']) && (!isset($_GET['return']))) {
    //не местных  - в сад
    exit;
}

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
        if ($UID = AuthUser::authenticate(
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
        ($appID = Object::_getConfigValue('auth.facebook.appID'))
        &&
        ($secretKey = Object::_getConfigValue('auth.facebook.secretKey'))
    ) {

        require_once(str_replace('*', 'user', CORE_GEARS_DIR) . '/facebook.php');
        $FBL = new Facebook(array(
            'appId' => $appID,
            'secret' => $secretKey,
        ));

        if ($fbUID = $FBL->getUser()) {

            //Смотрим есть ли такой юзер в списке зарегистрированных
            if (!($user = User::getFBUser($fbUID))) {
                //Если нет - создаем
                try {
                    //Обращаемся за данными
                    $FBUserData = $FBL->api('/me?fields=id,name,email');

                    $userData = array(
                        'u_name' => (isset($FBUserData['email'])) ? $FBUserData['email'] : $FBUserData['id'] . '@facebook.com',
                        'u_fbid' => $FBUserData['id'],
                        'u_password' => User::generatePassword(),
                        'u_fullname' => $FBUserData['name']
                    );

                    $user = new User();
                    $user->create($userData);

                } catch (FacebookApiException $e) {
                    $response->addCookie(UserSession::FAILED_LOGIN_COOKIE_NAME, 'bad auth data', time() + 60);
                    goto escape;
                }
                catch (SystemException $e) {
                    $response->addCookie(UserSession::FAILED_LOGIN_COOKIE_NAME, 'social_error', time() + 60);
                    goto escape;
                }
            }

            call_user_func_array(
                array($response, 'addCookie'),
                UserSession::manuallyCreateSessionInfo($user->getID())
            );

        }
    } elseif (
        $vkAuth
        &&
        ($appID = Object::_getConfigValue('auth.vk.appID'))
        &&
        ($secretKey = Object::_getConfigValue('auth.vk.secretKey'))
    ) {
        require_once(str_replace('*', 'user', CORE_GEARS_DIR) . '/VKApi.php');
        $VK = new VKApi($appID, $secretKey);
        if ($vkUID = $VK->is_auth()) {
            //Смотрим есть ли такой юзер в списке зарегистрированных
            if (!($user = User::getVKUser($vkUID))) {
                //Если нет - создаем
                try {
                    $user = new User();
                    $user->create($VK->getUserInfo());
                } catch (Exception $e) {
                    $response->addCookie(UserSession::FAILED_LOGIN_COOKIE_NAME, 'bad auth data', time() + 60);
                    goto escape;
                }
            }
            call_user_func_array(
                array($response, 'addCookie'),
                UserSession::manuallyCreateSessionInfo($user->getID())
            );

        }
    }
}
escape:
$response->goBack();