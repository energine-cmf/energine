<?php
/**
 * @file
 * VKOAuth
 *
 * It contains the definition to:
 * @code
class VKOAuth;
@endcode
 *
 * @author Andrii Alieksieienko
 * @copyright 2013 eggmengroup.com
 *
 * @version 1.0.0
 */
namespace Energine\user\gears;
use Energine\share\gears\Object, Energine\share\gears\SystemException;
/**
 * Class for user authorization over <a href="http://vk.com">Вконтакте (VK)</a>.
 *
 * @code
class VKOAuth;
@endcode
 */
class VKOAuth extends Object {
    /**
     * VK application ID.
     * @var string $appId
     */
    private $appId;

    /**
     * VK application secret key.
     * @var string $apiSecret
     */
    private $apiSecret;

    /**
     * VK access token.
     * @var string $accessToken
     */
    private $accessToken = null;

    /**
     * Instance curl.
     * @var resource $ch
     */
    private $ch;

    /**
     * User ID.
     * @var integer $VKId
     */
    private $VKId = false;

    /**
     * Callback URL.
     * URL for getting access token.
     * @var string $callbackUrl
     */
    private $callbackUrl;

    /**
     * Authorization URL.
     * @var string AUTHORIZE_URL
     */
    const AUTHORIZE_URL = 'https://oauth.vk.com/authorize';

    /**
     * Access token URL.
     * @var string ACCESS_TOKEN_URL
     */
    const ACCESS_TOKEN_URL = 'https://oauth.vk.com/access_token';

    //todo VZ: What is return?
    /**
     * @param array $config Configurations
     * @param string|bool $return
     */
    public function __construct($config, $return = false) {
        $this->callbackUrl = ($base = E()->getSiteManager()->getCurrentSite()->base)
            . 'auth.php?vkAuth&return=' . ((!$return) ? $base : $return);
        $this->appId = $config['appId'];
        $this->apiSecret = $config['secret'];
    }

    public function __destruct() {
        if(is_resource($this->ch))
            curl_close($this->ch);
    }

    /**
     * Get API URL.
     *
     * @param string $method Method name.
     * @param string $responseFormat Response format.
     * @return string
     */
    public function getApiUrl($method, $responseFormat = 'json') {
        return 'https://api.vk.com/method/' . $method . '.' . $responseFormat;
    }

    /**
     * Connect.
     *
     * @return mixed
     *
     * @throws SystemException
     */
    public function connect() {
        $this->ch = curl_init();
        $parameters = array(
            'client_id' => $this->appId,
            'client_secret' => $this->apiSecret,
            'code' => $this->getCode(),
            'redirect_uri' => $this->callbackUrl
        );

        $rs = json_decode($this->request(
            $this->createUrl(self::ACCESS_TOKEN_URL, $parameters)), true);
        if (isset($rs['error'])) {
            throw new SystemException($rs['error'] .
            (!isset($rs['error_description']) ? : ': ' . $rs['error_description']));
        } else {
            $this->accessToken = $rs['access_token'];
            $this->VKId = $rs['user_id'];
            return $rs;
        }
    }

    /**
     * Get login URL.
     *
     * @param array $params Parameters.
     * @return string
     */
    public function getLoginUrl($params) {
        $parameters = array(
            'client_id' => $this->appId,
            'scope' => $params['scope'],
            'redirect_uri' => $params['redirect_uri'],
            'response_type' => 'code'
        );

        return $this->createUrl(self::AUTHORIZE_URL, $parameters);
    }

    /**
     * Get code.
     *
     * @return string | bool
     */
    private function getCode() {
        $code = false;
        if (isset($_GET['code']) && ctype_alnum($_GET['code'])) {
            $code = $_GET['code'];
        }
        return $code;
    }

    /**
     * Get VK ID.
     *
     * @return int
     */
    public function getVKId() {
        return $this->VKId;
    }

    /**
     * API.
     *
     * @param string $method Method name.
     * @param array $parameters Parameters.
     * @param string $format Format.
     * @return mixed
     */
    public function api($method, $parameters = array(), $format = 'array') {
        $parameters['timestamp'] = time();
        $parameters['api_id'] = $this->appId;
        $parameters['random'] = rand(0, 10000);
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
     * Create URL.
     *
     * @param string $url URL.
     * @param array $parameters Parameters.
     * @return string
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
     * Send request.
     *
     * @param string $url URL.
     * @param string $method Method name.
     * @param array $postFields Post fields.
     * @return mixed
     */
    private function request($url, $method = 'GET', $postFields = array()) {
        curl_setopt_array($this->ch, array(
            CURLOPT_USERAGENT => 'VK/1.0',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_POST => ($method == 'POST'),
            CURLOPT_POSTFIELDS => $postFields,
            CURLOPT_URL => $url
        ));

        return curl_exec($this->ch);
    }

}

;