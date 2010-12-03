<?php

/**
 * Класс Response.
 *
 * @package energine
 * @subpackage core
 * @author 1m.dm
 * @copyright Energine 2006
 * @version $Id$
 */

/**
 * HTTP-ответ.
 *
 * @package energine
 * @subpackage core
 * @author 1m.dm
 * @final
 */
final class Response extends Singleton {
    /**
     * @access private
     * @var string строка статуса ответа
     */
    private $statusLine;

    /**
     * @access private
     * @var array заголовки ответа
     */
    private $headers;

    /**
     * @access private
     * @var array cookies ответа
     */
    private $cookies;

    /**
     * @access private
     * @var string тело ответа
     */
    private $body;

    /**
     * @access private
     * @var array описание кодов ответа
     */
    private $reasonPhrases = array(
    100 => 'Continue',
    101 => 'Switching Protocols',
    200 => 'OK',
    201 => 'Created',
    202 => 'Accepted',
    203 => 'Non-Authoritative Information',
    204 => 'No Content',
    205 => 'Reset Content',
    206 => 'Partial Content',
    300 => 'Multiple Choices',
    301 => 'Moved Permanently',
    302 => 'Found',
    303 => 'See Other',
    304 => 'Not Modified',
    305 => 'Use Proxy',
    307 => 'Temporary Redirect',
    400 => 'Bad Request',
    401 => 'Unauthorized',
    402 => 'Payment Required',
    403 => 'Forbidden',
    404 => 'Not Found',
    405 => 'Method Not Allowed',
    406 => 'Not Acceptable',
    407 => 'Proxy Authentication Required',
    408 => 'Request Time-out',
    409 => 'Conflict',
    410 => 'Gone',
    411 => 'Length Required',
    412 => 'Precondition Failed',
    413 => 'Request Entity Too Large',
    414 => 'Request-URI Too Large',
    415 => 'Unsupported Media Type',
    416 => 'Requested range not satisfiable',
    417 => 'Expectation Failed',
    500 => 'Internal Server Error',
    501 => 'Not Implemented',
    502 => 'Bad Gateway',
    503 => 'Service Unavailable',
    504 => 'Gateway Time-out',
    505 => 'HTTP Version not supported'
    );

    /**
     * Конструктор класса.
     *
     * @access public
     * @return void
     */
    public function __construct() {
        parent::__construct();

        $this->setStatus(200);
        $this->headers = array();
        $this->cookies = array();
        $this->body = '';
    }

    /**
     * Устанавливает статус ответа.
     *
     * @access public
     * @param int $statusCode
     * @param string $reasonPhrase
     * @return void
     */
    public function setStatus($statusCode, $reasonPhrase = null) {
        if (!isset($reasonPhrase)) {
            $reasonPhrase = (isset($this->reasonPhrases[$statusCode]) ? $this->reasonPhrases[$statusCode] : '');
        }
        $this->statusLine = $_SERVER['SERVER_PROTOCOL']." $statusCode $reasonPhrase";
    }

    /**
     * Устанавливает поле заголовка ответа.
     *
     * @access public
     * @param string $name
     * @param string $value
     * @param boolean $replace
     * @return void
     */
    public function setHeader($name, $value, $replace = true) {
        if ((!$replace) && isset($this->headers[$name])) {
            return;
        }
        $this->headers[$name] = $value;
    }

    /**
     * Устанавливает cookie.
     *
     * @access public
     * @param string $name
     * @param string $value
     * @param int $expire
     * @param string $path
     * @param string $domain
     * @param boolean $secure
     * @return void
     */
    public function setCookie($name, $value = '', $expire = '') {
    	if($this->getConfigValue('site.domain')){
    		$path = '/';
    		$domain = '.'.$this->getConfigValue('site.domain');
    	}
    	else{
        	$path = SiteManager::getInstance()->getCurrentSite()->root;
        	$domain = '';
    	}
    	$secure = false;
        $this->cookies[$name] = compact('value', 'expire', 'path', 'domain', 'secure');
    }

    public function sendCookies(){
        foreach ($this->cookies as $name => $params) {
            setcookie($name, $params['value'], $params['expire'], $params['path'], $params['domain'], $params['secure']);
        }        
    }

    public function sendHeaders(){
        header($this->statusLine);
        foreach ($this->headers as $name => $value) {
            header("$name: $value");
        }
    }

    /**
     * Удаляет cookie.
     *
     * @access public
     * @param string $name
     * @param string $path
     * @param string $domain
     * @param boolean $secure
     * @return void
     */
    public function deleteCookie($name) {
        $this->setCookie($name, '', (time() - 1));
    }

    /**
     * Устанавливает адрес для переадресации.
     *
     * @param string $location
     * @return void
     * @access public
     */
    public function setRedirect($location) {
	    $this->setStatus(302);
        $this->setHeader('Location', $location);
        $this->setHeader('Content-Length', 0);
        $this->commit();
    }

    /**
     * Устанавливает адрес переадресации
     *
     * @param string $action
     * @return void
     * @access public
     */
    public function redirectToCurrentSection($action = '') {
        if ($action && substr($action, -1) !== '/') {
            $action .= '/';
        }
        $request = Request::getInstance();
        $this->setRedirect( 
        SiteManager::getInstance()->getCurrentSite()->base.$request->getLangSegment()
        .$request->getPath(Request::PATH_TEMPLATE, true)
        .$action
        );
    }

    /**
     * Добавляет данные к телу ответа.
     *
     * @access public
     * @param string $data
     * @return void
     */
    public function write($data) {
        $this->body .= $data;
    }

    /**
     * Отправляет ответ клиенту и завершает работу программы.
     *
     * @access public
     * @return void
     */
    public function commit() {
        if (!headers_sent($filename, $linenum)) {
            $this->sendHeaders();
            $this->sendCookies();
        }
        else {
            //throw new SystemException('ERR_HEADERS_SENT', SystemException::ERR_CRITICAL);
        }
        $contents = $this->body;

        /*if ((bool)Object::_getConfigValue('site.compress') && (strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== false)) {
            header("Vary: Accept-Encoding");
            header("Content-Encoding: gzip");
            $contents = gzencode($contents, 6);
        }*/
        echo $contents;
        session_write_close();
        exit;
    }
}
