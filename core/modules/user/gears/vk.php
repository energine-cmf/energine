<?php
/**
 * Класс для авторизации через соц. сеть Вконтакте.
 *
 * @package energine
 * @subpackage user
 * @author andrii.a
 * @copyright eggmengroup.com
 */
class VKApi {

    private $m_appId;
    private $m_secret;

    function __construct($appId,$secret) {
        $this->m_appId = $appId;
        $this->m_secret = $secret;
    }

    public function is_auth() {
        if (!isset($_COOKIE['vk_app_'.$this->m_appId]))
            return false;

        $vk_cookie = $_COOKIE['vk_app_'.$this->m_appId];

        if (!empty($vk_cookie)) {
            $cookie_data = array();

            foreach (explode('&', $vk_cookie) as $item) {
                $item_data = explode('=', $item);
                $cookie_data[$item_data[0]] = $item_data[1];
            }

            // Проверяем sig
            $sign = '';
            foreach ($cookie_data as $key => $value) {
                if ($key != 'sig') {
                    $sign .= ($key.'='.$value);
                }
            }
            $sign .= $this->m_secret;
            $sign = md5($sign);

            if ($sign == $cookie_data['sig']) {
                // sig не подделан - возвращаем ID пользователя ВКонтакте.
                return $cookie_data['mid'];
            }
        }

        return false;
    }

    /**
     * При авторизации данные о пользователе приходят в гет запросе.
     * (см. VKAuth.js)
     *
     * @access public
     * @return void
     */
    public function getUserInfo() {
        return array(
            'u_name' => $_GET['first_name'],
            'u_vkid' => $_GET['id'],
            'u_password' => User::generatePassword(),
            'u_fullname' => $_GET['first_name'].' '.$_GET['last_name']
        );
    }
}