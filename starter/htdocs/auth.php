<?php
use
    Energine\share\gears\UserSession,
    Energine\share\gears\Primitive,
    Energine\share\gears\User,
    Energine\user\gears\FBOAuth,
    Energine\user\gears\VKOAuth;

//на всякий пожарный проверяем реферрера
if (!isset($_SERVER['HTTP_REFERER']) /*&& (!isset($_GET['return']))*/) {
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
    ||
    ($gooAuth = ((isset($_GET['state'])) && ($_GET['state'] == 'gooAuth')))
) {
    if ($login) {
        if ($UID = Energine\share\gears\AuthUser::authenticate(
            $_POST['user']['username'],
            $_POST['user']['password']
        )
        ) {
            E()->UserSession->start($UID);
        } else {
            $response->addCookie(UserSession::FAILED_LOGIN_COOKIE_NAME, E()->Utils->translate('ERR_BAD_AUTH'), time() + 60);
        }
        //о том прошла ли аутентификация успешноыны LoginForm узнает из куков
    } elseif ($logout) {
        $_GET['return'] = '/';

        E()->UserSession->kill();
    } elseif (
        $fbAuth
        &&
        ($appID = Primitive::getConfigValue('auth.fb.appID'))
        &&
        ($secretKey = Primitive::getConfigValue('auth.fb.secretKey'))
    ) {
        try {
            $fb = new FBOAuth([
                'appId' => $appID,
                'secret' => $secretKey,
            ]);
            $userInfo = $fb->api('/me?fields=id,name,email,picture.type(small)');
            if (is_array($userInfo) && !isset($_REQUEST['error'])) {
                //Смотрим есть ли такой юзер в списке зарегистрированных
                if (!($user = User::getFBUser($userInfo['id']))
                    /*&& !($user = User::linkFBUserByEmail($userInfo['email'], $userInfo['id']))*/
                ) {
                    //Если нет - создаем
                    $userData = [
                        'u_name' => (isset($userInfo['email']))
                            ? $userInfo['email']
                            : $userInfo['id'] . '@facebook.com',
                        'u_fbid' => $userInfo['id'],
                        'u_password' => User::generatePassword(),
                        'u_fullname' => $userInfo['name'],
                        'u_avatar_img' => $userInfo['picture']['data']['url']
                    ];

                    $user = new User();
                    $user->create($userData);
                }
                E()->UserSession->start($UID);
            }
        } catch (\Exception $e) {
            $response->addCookie(UserSession::FAILED_LOGIN_COOKIE_NAME, $e->getMessage(), time() + 60);
            goto escape;
        }
    } elseif (
        $vkAuth
        &&
        ($appID = Primitive::getConfigValue('auth.vk.appID'))
        &&
        ($secretKey = Primitive::getConfigValue('auth.vk.secretKey'))
    ) {
        try {

            $vk = new VKOAuth([
                'appId' => $appID,
                'secret' => $secretKey,
            ],
                $_GET['return']
            );
            $vk->connect();
            if ($vkUID = $vk->getVKId()) {
                //Смотрим есть ли такой юзер в списке зарегистрированных
                if (!($user = User::getVKUser($vkUID))) {
                    //Если нет - создаем
                    $user = new User();
                    $res = $vk->api('users.get',
                        [
                            'uids' => $vkUID,
                            'fields' => 'uid,first_name,last_name,photo'
                        ]
                    );
                    if (is_array($res['response'])) {
                        $vkUser = $res['response'][0];
                        $userInfo = [
                            'u_name' => $vkUser['uid'] . '@vk.com',
                            'u_vkid' => $vkUser['uid'],
                            'u_password' => User::generatePassword(),
                            'u_fullname' => $vkUser['first_name'] . ' ' . $vkUser['last_name'],
                            'u_avatar_img' => $vkUser['photo'],
                        ];
                        $user->create($userInfo);
                    } else {
                        throw new SystemException('TXT_CREATE_SOCIAL_USER_ERROR');
                    }

                }
                E()->UserSession->start($user->getID());
            }
        } catch (\Exception $e) {
            $response->addCookie(UserSession::FAILED_LOGIN_COOKIE_NAME, $e->getMessage(), time() + 60);
            goto escape;
        }
    } elseif (
        $gooAuth
        &&
        ($appID = Primitive::getConfigValue('auth.goo.appID'))
        &&
        ($secretKey = Primitive::getConfigValue('auth.goo.secretKey'))
    ) {
        $goo = new \Energine\user\gears\GOOOAuth([
            'appId' => $appID,
            'secret' => $secretKey
        ]);
        try {
            if (!($user = User::getGOOUser($goo->user->id))) {
                //Если нет - создаем
                $user = new User();

                $userInfo = [
                    'u_name' => $goo->user->email,
                    'u_gooid' => $goo->user->id,
                    'u_password' => User::generatePassword(),
                    'u_fullname' => $goo->user->name,
                    'u_avatar_img' => $goo->user->picture,
                ];

                $user->create($userInfo);
            }
            E()->UserSession->start($user->getID());
        } catch
        (Exception $e) {
            $response->addCookie(UserSession::FAILED_LOGIN_COOKIE_NAME, $e->getMessage(), time() + 60);
            goto escape;
        }


    }
}
escape:
$response->goBack();