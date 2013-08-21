<?php
/**
 * Класс OKOAuth
 *
 * @package energine
 * @subpackage site/ufo
 * @author Andrii Alieksieienko
 * @copyright 2013 eggmengroup.com
 */
/**
 * Класс для авторизации пользователей
 * через odnoklassniki.ru.
 *
 * @package energine
 * @subpackage user
 * @author Andrii Alieksieienko
 * @copyright 2013 eggmengroup.com
 */
class OKOAuth extends Object {
    const API_URL = 'http://api.odnoklassniki.ru/fb.do';
    const AUTHORIZE_URL = 'http://www.odnoklassniki.ru/oauth/authorize';
    const ACCESS_TOKEN_URL = 'http://api.odnoklassniki.ru/oauth/token.do';
    const SIGN_TOKEN_NAME = 'access_token';

    private $appSecret;
    private $appId;
    private $appPublic;
    private $callbackUrl;
    private $accessToken;

    public function __construct($config, $return = false) {
        $this->appId = $config['appId'];
        $this->appPublic = $config['public'];
        $this->appSecret = $config['secret'];
        $this->callbackUrl = ($base = E()->getSiteManager()->getCurrentSite()->base)
            . 'auth.php?okAuth&return=' . ((!$return) ? $base : $return);
    }

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
            throw new Exception( "Error: " . $response->error);
        }
        $this->accessToken = $response->access_token;
        return $response;
    }

    public function getUser() {
        $user = array();
        $sig = md5('application_key=' . $this->appPublic . 'method=users.getCurrentUser' . md5($this->accessToken . $this->appSecret));
        $response = $this->api( '?application_key=' . $this->appPublic . '&method=users.getCurrentUser&sig=' .$sig);

        $user['id']    = (property_exists($response,'uid'))?$response->uid:"";
        $user['firstName']     = (property_exists($response,'first_name'))?$response->first_name:"";
        $user['lastName']      = (property_exists($response,'last_name'))?$response->last_name:"";
        $user['photoURL']      = (property_exists($response,'pic_1'))?$response->pic_1:"";

        return $user;
    }

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
     * @param array $params
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
     * @param   string $url
     * @param   array $parameters
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