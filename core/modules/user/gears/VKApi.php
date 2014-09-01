<?php
/**
 * @file
 * VKApi
 *
 * It contains the definition to:
 * @code
class VKApi;
@endcode
 *
 * @version 1.0.0
 */

namespace Energine\user\gears;
/**
 * Class for working with <a href="http://vk.com">Вконтакте</a> API.
 *
 * @code
class VKApi;
@endcode
 */
class VKApi {
    /**
     * Application ID.
     * @var string|int $m_appId
     */
    private $m_appId;
    /**
     * Application secret key
     * @var string $m_secret
     */
    private $m_secret;
    /**
     * API URL.
     * @var string $m_apiUrl
     */
    private $m_apiUrl;
    /**
     * User ID.
     * @var int|string $m_uid
     */
    private $m_uid;

    /**
     * @param string|int $appId Application ID.
     * @param string $secret Application secret key.
     * @param string $api_url API URL.
     */
    function __construct($appId, $secret, $api_url = 'api.vk.com/api.php') {
        $this->m_appId = $appId;
        $this->m_secret = $secret;
        if (!strstr($api_url, 'http://')) $api_url = 'http://'.$api_url;
        $this->m_apiUrl = $api_url;
    }

    /**
     * API request.
     *
     * @param string $method Method name.
     * @param array|bool $params parameters.
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
     * Generate request parameters to <a href="http://vk.com">Вконтакте</a> API.
     *
     * @param mixed $params Parameters.
     * @return string
     */
    private function params($params) {
        $pice = array();
        foreach($params as $k=>$v) {
            $pice[] = $k.'='.urlencode($v);
        }
        return implode('&',$pice);
    }

    // todo VZ: the function name does not reflect his functionality.
    /**
     * Is the user authorized?
     *
     * @return bool|int
     *
     * @note If the user is authorized then his user ID will be returned. If not - @c false.
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
     * Get user information.
     *
     * @return array
     */
    public function getUserInfo() {
        $res = $this->apiRequest('getProfiles', array('uids'=>$this->m_uid,"fields"=>"uid,first_name,last_name,sex,city,country,photo"));
        if(is_array($res['response'])){
            $userInfo = array(
                'u_name' => $res['response'][0]['uid'].'@vk.com',
                'u_vkid' => $res['response'][0]['uid'],
                'u_password' => User::generatePassword(),
                'u_city' => (isset($res['response'][0]['city']))?$res['response'][0]['city']:'',
                'u_country' => (isset($res['response'][0]['country']))?$res['response'][0]['country']:'',
                'u_fullname' => $res['response'][0]['first_name'] . ' ' . $res['response'][0]['last_name'],
            );
            if(isset($res['response'][0]['city'])){
                $userInfo['u_city'] = $res['response'][0]['city'];
                $userInfo['u_country'] = $res['response'][0]['country'];
            }
        }
        else{
            $userInfo = false;
        }
        return $userInfo;
    }

}