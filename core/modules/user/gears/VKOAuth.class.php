<?php

/**
 * Класс VKOAuth
 *
 * @package energine
 * @subpackage site/ufo
 * @author Andrii Alieksieienko
 * @copyright 2013 eggmengroup.com
 */

/**
 * Класс для авторизации пользователей
 * через Вконтакте.
 *
 * @package energine
 * @subpackage site/ufo
 * @author Andrii Alieksieienko
 * @copyright 2013 eggmengroup.com
 */
class VKOAuth extends Object{
    /**
     * VK application id.
     * @var string
     */
    private $appId;

    /**
     * VK application secret key.
     * @var string
     */
    private $apiSecret;

    /**
     * VK access token.
     * @var string
     */
    private $accessToken = null;

    /**
     * Instance curl.
     * @var resource
     */
    private $ch;

    /**
     * ИД пользователя вконтакте.
     * @var integer
     */
    private $VKId = false;

    /**
     * Url для получения access token.
     * @var string
     */
    private $callbackUrl;

    const AUTHORIZE_URL    = 'https://oauth.vk.com/authorize';
    const ACCESS_TOKEN_URL = 'https://oauth.vk.com/access_token';

    /**
     * @param array $config
     * @throws SystemException
     */
    public function __construct($config) {
        $this->ch          = curl_init();
        $this->callbackUrl = ($base = E()->getSiteManager()->getCurrentSite()->base)
                                    . 'auth.php?vkAuth&return=' . $base;
        $this->appId       = $config['appId'];
        $this->apiSecret   = $config['secret'];
    }

    /**
     * @return  void
     */
    public function __destruct() {
        curl_close($this->ch);
    }

    /**
     * @param   string  $method
     * @param   string  $responseFormat
     * @return  string
     */
    public function getApiUrl($method, $responseFormat = 'json') {
        return 'https://api.vk.com/method/' . $method . '.' . $responseFormat;
    }

    /**
     * @return mixed
     * @throws SystemException
     */
    public function connect() {
        $parameters = array(
            'client_id'     => $this->appId,
            'client_secret' => $this->apiSecret,
            'code'          => $this->getCode(),
            'redirect_uri'  => $this->callbackUrl
        );

        $rs = json_decode($this->request(
            $this->createUrl(self::ACCESS_TOKEN_URL, $parameters)), true);
        if (isset($rs['error'])) {
            throw new SystemException($rs['error'] .
            (!isset($rs['error_description']) ?: ': ' . $rs['error_description']));
        } else {
            $this->accessToken = $rs['access_token'];
            $this->VKId = $rs['user_id'];
            return $rs;
        }
    }

    /**
     * @param array $params
     * @return string
     */
    public function getLoginUrl($params)
    {
        $parameters = array(
            'client_id'     => $this->appId,
            'scope'         => $params['scope'],
            'redirect_uri'  => $params['redirect_uri'],
            'response_type' => 'code'
        );

        return $this->createUrl(self::AUTHORIZE_URL, $parameters);
    }

    /**
     * @return string
     */
    private function getCode() {
        $code = false;
        if(isset($_GET['code']) && ctype_alnum($_GET['code'])) {
            $code = $_GET['code'];
        }
        return $code;
    }

    /**
     * @return int
     */
    public function getVKId() {
        return $this->VKId;
    }

    /**
     * @param   string  $method
     * @param   array   $parameters
     * @param   string  $format
     * @return  mixed
     */
    public function api($method, $parameters = array(), $format = 'array') {
        $parameters['timestamp']    = time();
        $parameters['api_id']       = $this->appId;
        $parameters['random']       = rand(0, 10000);
        if (!is_null($this->accessToken)) {
            $parameters['access_token'] = $this->accessToken;
        }
        ksort($parameters);

        $sig = '';
        foreach ($parameters as $key => $value) {
            $sig .= $key . '=' . $value;
        }
        $sig .= $this->apiSecret;

        $parameters['sig'] = md5($sig);

        $rs = $this->request($this->createUrl(
            $this->getApiUrl($method, $format == 'array' ? 'json' : $format), $parameters));
        return $format == 'array' ? json_decode($rs, true) : $rs;
    }

    /**
     * @param   string  $url
     * @param   array   $parameters
     * @return  string
     */
    private function createUrl($url, $parameters) {
        $piece = array();
        foreach ($parameters as $key => $value) {
            $piece[] = $key . '=' . rawurlencode($value);
        }
        $url .= '?' . implode('&', $piece);
        return $url;
    }

    /**
     * @param $url
     * @param string $method
     * @param array $postFields
     * @return mixed
     */
    private function request($url, $method = 'GET', $postFields = array()) {
        curl_setopt_array($this->ch, array(
            CURLOPT_USERAGENT       => 'VK/1.0',
            CURLOPT_RETURNTRANSFER  => true,
            CURLOPT_SSL_VERIFYPEER  => false,
            CURLOPT_POST            => ($method == 'POST'),
            CURLOPT_POSTFIELDS      => $postFields,
            CURLOPT_URL             => $url
        ));

        return curl_exec($this->ch);
    }

};