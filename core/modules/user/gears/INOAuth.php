<?php
/**
 * @file
 * IOAuth
 *
 * It contains the definition to:
 * @code
class IOAuth;
@endcode
 *
 * @author Oleg Marichev
 * based on OKOAuth
 * @copyright 2016
 *
 * @version 1.0.0
 */
//namespace Energine\share\gears;
namespace Energine\user\gears;
use Energine\share\gears\Primitive;
/**
 * Class for user authorisation over <a href="http://www.instagram.com">Instagram</a>.
 *
 * @code
class IOAuth;
@endcode
 */
class INOAuth extends Primitive {
    /**
     * API URL.
     * @var string API_URL
     */
    const API_URL = 'http://api.odnoklassniki.ru/fb.do';
    /**
     * Authorize URL.
     * @var string AUTHORIZE_URL
     */
    //const AUTHORIZE_URL = 'http://www.odnoklassniki.ru/oauth/authorize';   
    //https://api.instagram.com/oauth/authorize/?client_id=CLIENT-ID&redirect_uri=REDIRECT-URI&response_type=code
    const AUTHORIZE_URL = 'https://api.instagram.com/oauth/authorize';
    /**
     * Access token URL.
     * @var string ACCESS_TOKEN_URL
     */
    //const ACCESS_TOKEN_URL = 'http://api.odnoklassniki.ru/oauth/token.do';
    // https://api.instagram.com/oauth/access_token
    const ACCESS_TOKEN_URL = 'https://api.instagram.com/oauth/access_token';
    /**
     * Sign token name.
     * @var string SIGN_TOKEN_NAME
     */
    const SIGN_TOKEN_NAME = 'access_token';

    /**
     * Application secret key.
     * @var int|string $appSecret
     */
    private $appSecret;
    /**
     * Application ID.
     * @var int $appId
     */
    private $appId;
    /**
     * Application public key.
     * @var string $appPublic
     */
    private $appPublic;
    /**
     * Callback URL.
     * @var string $callbackUrl
     */
    private $callbackUrl;
    /**
     * Access token.
     * @var $accessToken
     */
    private $accessToken;
    /**
     * userClass.
     * @var $user
     */
    public $user;

    /**
     * @param array $config Configurations.
     * @param bool $return
     */
    public function __construct($config, $return = false) {    
        $this->appId = $config['appId'];
        //$this->appPublic = $config['public'];
        $this->appSecret = $config['secret'];
        $this->callbackUrl = ($base = E()->getSiteManager()->getCurrentSite()->base)
            . 'auth.php?inAuth=1&return=' . ((!$return) ? $base : $return);
    }

    /**
     * Send request.
     *
     * @param string $url URL.
     * @param array $params Parameters.
     * @param string $method Method name.
     * @return mixed
     */
    private function request($url, $params = array(), $method = 'GET') {
        $ch = curl_init();
        curl_setopt_array($ch, array(
            CURLOPT_USERAGENT => 'OK/1.0',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_POST => ($method == 'POST'),
            CURLOPT_POSTFIELDS => $params,
            CURLOPT_URL => $url
        ));
        $response = curl_exec($ch);
        curl_close ($ch);
        return $response;
    }

    /**
     * Parse request result.
     *
     * @param mixed $result Request result.
     * @return mixed|StdClass
     */
    private function parseRequestResult($result) {
        if(json_decode($result)) {
            return json_decode($result);
        }
        parse_str($result, $output);
        $result = new StdClass();
        foreach($output as $k => $v) {
            $result->$k = $v;
        }
        return $result;
    }

    /**
     * Connect.
     *
     * @return mixed
     *
     * @throws \Exception
     */
    public function connect() {
        $parameters = array(
            "client_id"     => $this->appId,
            "client_secret" => $this->appSecret,
            "grant_type"    => "authorization_code",
            "redirect_uri"  => $this->callbackUrl,
            "code"          => $this->getCode()
        );        
        $response = $this->request($this->createUrl(self::ACCESS_TOKEN_URL, $parameters), $parameters);
        $response = $this->parseRequestResult($response);

        if(!$response || !isset($response->access_token)){
            throw new \Exception( "Error: " . $response->error);
        }
        $this->accessToken = $response->access_token;
        $this->user=$response->user;
        return $response;
    }

    /**
     * API.
     *
     * @param string $url URL.
     * @param string $method Method name.
     * @param array $parameters Parameters.
     * @return mixed|null
     */
    private function api( $url, $method = "GET", $parameters = array() ) {
        if ( strrpos($url, 'http://') !== 0 && strrpos($url, 'https://') !== 0 ) {
            $url = self::API_URL . $url;
        }
        $parameters['access_token'] = $this->accessToken;
        $response = null;
        switch( $method ) {
            case 'GET'  : $response = $this->request( $url, $parameters, "GET"  ); break;
            case 'POST' : $response = $this->request( $url, $parameters, "POST" ); break;
        }
        if($response){
            $response = json_decode( $response );
        }
        return $response;
    }

    /**
     * Get login URL.
     *
     * @param array $params Parameters.
     * @return string
     */
    public function getLoginUrl($params) {
        $scope=(empty($params['scope']))? 'basic':$params['scope'];//+public_content
        $parameters = array(
            'client_id' => $this->appId,
            'scope' => $scope,
            'redirect_uri' => $params['redirect_uri'],
            'response_type' => 'code'
        );

        return $this->createUrl(self::AUTHORIZE_URL, $parameters);
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
     * Get code.
     *
     * @return string
     */
    private function getCode() {
        $code = false;
        if (isset($_GET['code'])) {
            $code = $_GET['code'];
        }
        return $code;
    }
}