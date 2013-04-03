<?php

//на всякий пожарный проверяем реферрера
if (!isset($_SERVER['HTTP_REFERER'])) {
    //не местных  - в сад
    exit;
}

// подключаем bootstrap
require_once('bootstrap.php');

// подключаем инициализационные функции
require_once(CORE_DIR . '/kernel/ini.func.php');

// подключаем служебные(вспомогательные) функции
require_once(CORE_DIR . '/kernel/utils.func.php');

if (
        ($login = (isset($_POST['user']['login']) &&
        isset($_POST['user']['username']) &&
        isset($_POST['user']['password'])))
        ||
        ($logout = (isset($_POST['user']['logout']) || isset($_GET['logout'])))
        ||
        //($fbLogin = ((isset($_POST['fbData']))?$_POST['fbData']:false))
        ($fbAuth = isset($_GET['fbAuth']))
) {
    $response = E()->getResponse();

    if ($login) {
        if ($UID = AuthUser::authenticate(
            $_POST['user']['username'],
            $_POST['user']['password']
        )) {
            /**
             * $response->addCookie()
             */
            call_user_func_array(
                array($response, 'addCookie'),
                UserSession::manuallyCreateSessionInfo($UID)
            );
        }
        else{
            $response->addCookie(UserSession::FAILED_LOGIN_COOKIE_NAME, 'bad auth data', time()+60);    
        }
        //о том прошла ли аутентификация успешно LoginForm узнает из куков


    }
    elseif ($logout) {
        UserSession::manuallyDeleteSessionInfo();
        //просто удаляем куку
        $response->deleteCookie(UserSession::DEFAULT_SESSION_NAME);
    }
    elseif(
        $fbAuth
        &&
        ($appID = Object::_getConfigValue('auth.facebook.appID'))
        &&
        ($secretKey = Object::_getConfigValue('auth.facebook.secretKey'))
    ){
        require_once(str_replace('*', 'user', CORE_GEARS_DIR).'/facebook.php');
        $FBL = new Facebook(array(
          'appId'  => $appID,
          'secret' => $secretKey,
        ));
        if($fbUID = $FBL->getUser()){
            //Смотрим есть ли такой юзер в списке зарегистрированных
            if(!($user = User::getFBUser($fbUID))){
            //Если нет - создаем
                try {
                    //Обращаемся за данными
                    $FBUserData = $FBL->api('/me');
                    $userData = array(
                        'u_name' =>$FBUserData['email'],
                        'u_fbid' =>$FBUserData['id'],
                        'u_password' => User::generatePassword(),
                        'u_fullname' =>$FBUserData['name']
                    );
                    $user = new User();
                    $user->create($userData);
                }
                catch(FacebookApiException $e){
                    $response->addCookie(UserSession::FAILED_LOGIN_COOKIE_NAME, 'bad auth data', time()+60);
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