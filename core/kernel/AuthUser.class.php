<?php

/**
 * Класс AuthUser.
 *
 * @package energine
 * @subpackage core
 * @author 1m.dm
 * @copyright Energine 2006
 */


/**
 * Аутентифицированный пользователь.
 *
 * @package energine
 * @subpackage core
 * @author 1m.dm
 */
class AuthUser extends User {
    /**
     * Путь к корню сайта, чтоб по 10 раз за ним не обращаться через сайт менеджер
     *
     * @access private
     * @var string
     */
    private $siteRoot;
    /**
     * Флаг показывающий залогинился ли пользователь только что
     *
     * @var bool
     * @access private
     */
    private $isJustNowAuthenticated = false;


    /**
     * Конструктор класса.
     * параметр введен только для избежания strict ошибки
     *
     * @param false
     * @access private
     * @return void
     * @todo избавиться от hardcoded имен полей формы?
     */
    public function __construct($id = false) {
        parent::__construct(false);
        $this->siteRoot = SiteManager::getInstance()->getCurrentSite()->root;
        //Если пришел флаг  - отлогиниться
        if (isset($_POST['user']['logout']) || isset($_GET['logout'])) {
            //Очищаем информацию о пользователе
            $this->clearInfo();
            return;
        }
        elseif (isset($_SESSION['userID'])) {
            $id = $_SESSION['userID'];
        }

        elseif (isset($_COOKIE['user'])) {
            $response = E()->getResponse();
            try {
                $user = unserialize($_COOKIE['user']);
                if (isset($user[0], $user[1]) &&
                        !$id = $this->authenticate($user[0], $user[1], true)) {
                    $response->deleteCookie('user', $this->siteRoot);
                }
            }
            catch (Exception $e) {
                $response->deleteCookie('user', $this->siteRoot);
            }
        }
        elseif (isset($_POST['user']['login']) &&
                isset($_POST['user']['username']) &&
                isset($_POST['user']['password'])) {
            $id = $this->authenticate(
                $_POST['user']['username'],
                sha1($_POST['user']['password']),
                (empty($_POST['user']['remember']) ? false : true)
            );
            //stop($_POST['user']['remember']);
            $this->isJustNowAuthenticated = true;
        }

        if ($id)
            $this->loadInfo($id);
    }

    /**
     * Возвращает значение isJustNowAuthenticated
     *
     * @return bool
     * @access public
     */

    /*public function isNowAuthenticated() {
        return $this->isJustNowAuthenticated;
    }*/

    /**
     * Возвращает флаг успеха аутентификации:
     *     true - пользователь успешно аутентифицирован;
     *     false - пользователь является гостем.
     *
     * @access public
     * @return boolean
     */
    public function isAuthenticated() {
        return ($this->getID() === false) ? false : true;
    }

    /**
     * Аутентифицирует пользователя по его имени и SHA-1 хэшу пароля.
     * Если флаг $remember установлен в true, при успешной аутентификации
     * клиенту устанавливаются cookie с информацией о его аккаунте на 30 дней,
     * для автоматизации процедуры входа при последующих посещениях сайта.
     * Возвращает флаг успеха аутентификации.
     *
     * @access public
     * @param string $username имя пользовате
     * @param string $password SHA-1 хэш пароля
     * @param boolean $remember
     * @return mixed
     */
    public function authenticate($username, $password, $remember = false) {
        $username = trim($username);
        //Проверяем совпадает ли имя/пароль в SHA1 с данными в таблице
        $result = convertDBResult($this->dbh->select(
            'user_users', array('u_id', 'u_is_active'),
            array(
                'u_name' => $username,
                'u_password' => $password
            )
        ), 'u_id', true);
        if (!is_array($result)) {
            //Нет, не совпадает - дальше нет смысла проверять
            return false;
        }
        else {
            $id = key($result);
            //может пользователь с таким IP находится в таблице забаненых по IP?
            if ($this->isBannedIP()) {
                //Да. он там
                return false;
            }
            //А пользователь активирован?

            if (!($isActive = $result[$id]['u_is_active'])) {
                //Нет, пользователь не активен, смотрим дальше

                //Может он в таблице забаненых пользователей?
                if ($isBanned = $this->isBannedUser($id)) {
                    return false;
                }
                else {
                    $this->dbh->modify(QAL::UPDATE, 'user_users', array('u_is_active' => 1), array('u_id' => $id));
                }

            }

            //Да, активирован - значит все ок
            if ($remember) {
                $response = E()->getResponse();
                $response->setCookie(
                    'user',
                    serialize(array($username, $password)),
                        time() + (3600 * 24 * 30)
                );
            }

            $_SESSION['userID'] = $id;
            return $id;
        }


        //Да, он там

        //А не истекло ли время бана

    }

    /**
     * Проверка забанен ли IP адрес пользователя
     *
     * @param bool | IP string $ip
     * @return bool
     */
    private function isBannedIP($ip = false) {
        if (!$ip) {
            $ip = E()->getRequest()->getClientIP();
        }
        $result =
                $this->dbh->selectRequest('SELECT ban_ip_id as ban_id, ban_ip_end_date as ban_date FROM user_ban_ips WHERE ban_ip=INET_ATON(%s)', $ip);
        //А есть ли он в списке
        if (is_array($result)) {
            //Таки есть
            list($result) = $result;
            //Может время бана истекло?
            if ($result['ban_date'] <= time()) {
                //Да, таки истекло
                //Удаляем из списка забаненых
                $this->dbh->modify(QAL::DELETE, 'user_ban_ips', null, array('ban_ip_id' => $result['ban_id']));
                return false;
            }
            else {
                //Забанен
                return true;
            }
        }
        return false;
    }

    private function isBannedUser($UID) {
        $result =
                simplifyDBResult($this->dbh->select('user_users_ban', 'ban_date', array('u_id' => $UID)), 'ban_date', true);

        if (!$result) {
            //Не, он не в забаненых
            return null;
        }
        //Да, он там
        //А не истекло ли время бана
        if ($result <= time()) {
            //Истекло
            $this->dbh->modify(QAL::DELETE, 'user_users_ban', null, array('u_id' => $UID));
            $this->dbh->modify(QAL::UPDATE, 'user_users', array('u_is_active' => 1), array('u_id' => $UID));
            return false;
        }
        return true;
    }

    /**
     * Очищает всю информацию о пользователе из сессии, cookie.
     *
     * @access public
     * @return boolean
     */
    public function clearInfo() {
        $response = E()->getResponse();
        $UID = false;
        if (isset($_SESSION['userID'])) {
            $UID = $_SESSION['userID'];
            unset($_SESSION['userID']);
        }

        if (isset($_COOKIE[UserSession::DEFAULT_SESSION_NAME])) {
            $response->deleteCookie(UserSession::DEFAULT_SESSION_NAME, $this->siteRoot);
        }

        if (isset($_COOKIE['user'])) {
            $response->deleteCookie('user', $this->siteRoot);
        }
        if ($UID)
            $this->dbh->modify(QAL::DELETE, 'share_session', null, array('u_id' => $UID));
        if (isset($_SESSION))
            session_destroy();
    }
}
