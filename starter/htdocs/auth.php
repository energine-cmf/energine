<?php
//на всякий пожарный проверяем реферрера
if (!isset($_SERVER['HTTP_REFERER'])) {
    //не местных  - в сад
    exit;
}
//подключаем инициализационные функции
require_once('core/kernel/ini.func.php');

//подключаем служебные(вспомогательные) функции
require_once('core/kernel/utils.func.php');


if (
        ($login = (isset($_POST['user']['login']) &&
        isset($_POST['user']['username']) &&
        isset($_POST['user']['password'])))
        ||
        ($logout = (isset($_POST['user']['logout']) || isset($_GET['logout'])))
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
            $response->addCookie('login_attempt_failed', 'bad auth data', time()+60);    
        }
        //о том прошла ли аутентификация успешно LoginForm узнает из куков


    }
    elseif ($logout) {
        UserSession::manuallyDeleteSessionInfo();
        //просто удаляем куку
        $response->deleteCookie(UserSession::DEFAULT_SESSION_NAME);
    }
}

$response->goBack();