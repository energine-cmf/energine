<?php
/**
 * Класс для работы с API соц. сети Вконтакте.
 *
 * @package energine
 * @subpackage user
 * @author andrii.a
 * @copyright eggmengroup.com
 */
class VKApi {

    private $m_appId;
    private $m_secret;
    private $m_apiUrl;
    private $m_uid;

    function __construct($appId, $secret, $api_url = 'api.vk.com/api.php') {
        $this->m_appId = $appId;
        $this->m_secret = $secret;
        if (!strstr($api_url, 'http://')) $api_url = 'http://'.$api_url;
        $this->m_apiUrl = $api_url;
    }

    /**
     * Функция для запроса в API
     * Вконтакте.
     *
     * @param $method
     * @param $params
     * @access private
     * @return array
     */
    private function apiRequest($method,$params=false) {
        if (!$params) $params = array();
        $params['api_id'] = $this->m_appId;
        $params['v'] = '3.0';
        $params['method'] = $method;
        $params['timestamp'] = time();
        $params['format'] = 'json';
        $params['random'] = rand(0,10000);
        ksort($params);
        $sig = '';
        foreach($params as $k=>$v) {
            $sig .= $k.'='.$v;
        }
        $sig .= $this->m_secret;
        $params['sig'] = md5($sig);
        $query = $this->m_apiUrl.'?'.$this->params($params);
        $res = file_get_contents($query);
        return json_decode(stripslashes($res), true);
    }


    /**
     * Генерация параметров запроса к
     * API Вконтакте.
     *
     * @param $params
     * @access private
     * @return string
     */
    private function params($params) {
        $pice = array();
        foreach($params as $k=>$v) {
            $pice[] = $k.'='.urlencode($v);
        }
        return implode('&',$pice);
    }

    /**
     * Если пользователь авторизирован -
     * возвращаем его ЮИД, в противном случае
     * возвращаем false.
     *
     * @access public
     * @return bool|int
     */
    public function is_auth() {
        if (!isset($_COOKIE['vk_app_' . $this->m_appId]))
            return false;

        $vk_cookie = $_COOKIE['vk_app_' . $this->m_appId];

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
                    $sign .= ($key . '=' . $value);
                }
            }
            $sign .= $this->m_secret;
            $sign = md5($sign);

            if ($sign == $cookie_data['sig']) {
                $this->m_uid = $cookie_data['mid'];
                // sig не подделан - возвращаем ID пользователя ВКонтакте.
                return $cookie_data['mid'];
            }
        }

        return false;
    }

    /**
     * Получаем данные о пользователе
     *
     * @access public
     * @return array
     */
    public function getUserInfo() {
        $res = $this->apiRequest('getProfiles', array('uids'=>$this->m_uid));
        if(is_array($res['response'])){
            $userInfo = array(
                'u_name' => $res['response'][0]['uid'].'@vk.com',
                'u_vkid' => $res['response'][0]['uid'],
                'u_password' => User::generatePassword(),
                'u_fullname' => $res['response'][0]['first_name'] . ' ' . $res['response'][0]['last_name']
            );
        }
        else{
            $userInfo = false;
        }
        return $userInfo;
    }

}