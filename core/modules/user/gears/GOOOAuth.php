<?php
/**
 * Created by PhpStorm.
 * User: pavka
 * Date: 9/30/15
 * Time: 2:11 PM
 */

namespace Energine\user\gears;


use Energine\share\gears\Primitive;

/**
 * Class GooOAuth
 * @package Energine\user\gears
 * @property-read $client
 * @property-read $user
 */
class GOOOAuth extends Primitive implements IOAuth {
    private $clientID;
    /**
     * @var Google_Client
     */

    private $secret;
    private $callBackURL;
    private $gClient = NULL;
    private $gPlusUser = NULL;

    function __construct($config, $return = false) {
        $this->clientID = $config['appId'];
        $this->secret = $config['secret'];
        $this->callBackURL = ($base = E()->getSiteManager()->getCurrentSite()->base) . 'auth.php';
    }

    /**
     * is utilized for reading data from inaccessible members.
     *
     * @param $name string
     * @return mixed
     * @link http://php.net/manual/en/language.oop5.overloading.php#language.oop5.overloading.members
     */
    function __get($name) {
        if ($name == 'client') {
            if (is_null($this->gClient)) {
                $this->gClient = new \Google_Client();
                $this->gClient->setClientId($this->clientID);
                $this->gClient->setClientSecret($this->secret);
                $this->gClient->setScopes(['https://www.googleapis.com/auth/userinfo.email', 'https://www.googleapis.com/auth/userinfo.profile']);
                $this->gClient->setState('gooAuth');
                $this->gClient->setRedirectUri($this->callBackURL);
            }
            return $this->gClient;

        }
        elseif($name =='user'){
            if (is_null($this->gPlusUser)) {
                $this->client->authenticate($this->getCode());
                $uInfo = new \Google_Service_Oauth2($this->client);
                $this->gPlusUser = $uInfo->userinfo->get();
            }
            return $this->gPlusUser;
        }
        throw new \InvalidArgumentException($name);
    }


    public function getLoginUrl() {
        return $this->client->createAuthUrl();
    }

    /**
     * Get code.
     *
     * @return string | bool
     */
    private function getCode() {
        $code = false;
        if (isset($_GET['code'])) {
            $code = $_GET['code'];
        }
        return $code;
    }
}