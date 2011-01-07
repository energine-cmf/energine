<?php
//на всякий пожарный проверяем реферрера
if (!isset($_SERVER['HTTP_REFERER'])) {
    //не местных  - в сад
    exit;
}
/**
 * Отправка куки полученных при авторизации/отлогинивании
 * @param  $cookiedata
 * @return void
 */
function sendCookies($cookiedata) {
    if (is_array($cookiedata))
        foreach ($cookiedata as $cookieProps) {
            if ($cookieProps['sticky'] == 1) {
                $cookieProps['expires'] = time() + 60 * 60 * 24 * 365;
            }

            $cookieProps['path'] =
                    $cookieProps['path'] ? $cookieProps['path'] : '/';
            setcookie($cookieProps['name'], $cookieProps['value'], $cookieProps['expires'], $cookieProps['path'], $cookieProps['domain']);
        }
}

if (
        ($login = (isset($_POST['user']['login']) &&
        isset($_POST['user']['username']) &&
        isset($_POST['user']['password'])))
        ||
        ($logout = (isset($_POST['user']['logout']) || isset($_GET['logout'])))
) {
    require('site/kernel/IPBAuthUser.class.php');

    if ($login) {
        //если аутентифициорванный пользователь
        if ($cookiedata =
                IPBAuthUser::authenticate($_POST['user']['username'], $_POST['user']['password'])) {
            //бросаем ему куки
            sendCookies($cookiedata);
        }
    }
    elseif ($logout) {
        $user = new IPBAuthUser();
        if ($cookiedata = $user->logout()) {
            //бросаем ему куки
            sendCookies($cookiedata);
        }
    }
}
header('Location: ' . $_SERVER['HTTP_REFERER']);
exit;

