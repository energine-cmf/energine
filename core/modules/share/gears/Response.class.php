<?php

/**
 * Класс Response.
 *
 * @package energine
 * @subpackage kernel
 * @author 1m.dm
 * @copyright Energine 2006
 */

/**
 * HTTP-ответ.
 *
 * @package energine
 * @subpackage kernel
 * @author 1m.dm
 * @final
 */
final class Response extends Object {

    private $reasonPhrases;
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
     * Конструктор класса.
     *
     * @access public
     * @return void
     */
    public function __construct() {
        $this->reasonPhrases = include_once('reasonPhrases.inc.php');
        $this->setStatus(200);
        $this->headers = array();
        $this->cookies = array();
        $this->body = '';
    }


    /**
     * Метод вызываемый при переадресации
     * заменяет паттерны lang и site на соответствующие значения
     * @static
     * @param $redirectURL string
     * @return string
     */
    public static function prepareRedirectURL($redirectURL) {
        if (empty($redirectURL)) return $redirectURL;
        $lang = E()->getLanguage();

        return str_replace(
            array(
                '%lang%',
                '%site%'
            ),
            array(
                $lang->getAbbrByID($lang->getCurrent()),
                E()->getSiteManager()->getCurrentSite()->base
            ),
            $redirectURL);
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
        if (is_null($reasonPhrase)) {
            $reasonPhrase =
                (isset($this->reasonPhrases[$statusCode]) ? $this->reasonPhrases[$statusCode] : '');
        }
        $this->statusLine =
            ((isset($_SERVER['SERVER_PROTOCOL'])) ? $_SERVER['SERVER_PROTOCOL'] : 'UNKNOWN') . " $statusCode $reasonPhrase";
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
     * @return void
     */
    public function addCookie($name = UserSession::DEFAULT_SESSION_NAME, $value = '', $expire = 0, $domain = false, $path = '/') {
        if (!$domain) {
            if ($domain = $this->getConfigValue('site.domain')) {
                $domain = '.' . $domain;
                $path = '/';
            } else {
                $path = E()->getSiteManager()->getCurrentSite()->root;
                $domain = E()->getSiteManager()->getCurrentSite()->domain;
            }
        }
        /*if ($this->getConfigValue('site.domain')) {
            $path = '/';
            $domain = '.' . $this->getConfigValue('site.domain');
        }
        else {
            $path = E()->getSiteManager()->getCurrentSite()->root;
            $domain = '';
        }*/
        $secure = false;
        $_COOKIE[$name] = $value;
        $this->cookies[$name] =
            compact('value', 'expire', 'path', 'domain', 'secure');
    }

    /**
     * Отправляет куки добавленные в список
     * используется только в commit
     * сделан публичным для того чтобы можно было вызвать из капчи
     * но это исключение
     *
     * @return void
     * @access private
     */
    public function sendCookies() {
        foreach ($this->cookies as $name => $params) {
            setcookie($name, $params['value'], $params['expire'], $params['path'], $params['domain'], $params['secure']);
        }
    }

    public function sendHeaders() {
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
     * @param string $domain
     * @param string $path
     * @param boolean $secure
     * @return void
     */
    public function deleteCookie($name) {
        $this->addCookie($name, '', (time() - 1));
    }

    /**
     * Устанавливает адрес для переадресации.
     * и собственно переадресовывает
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
        $request = E()->getRequest();
        $this->setRedirect(
            E()->getSiteManager()->getCurrentSite()->base .
                $request->getLangSegment()
                . $request->getPath(Request::PATH_TEMPLATE, true)
                . $action
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
     * Возвращаемся туда откуда пришли
     * @see auth.php
     * @return void
     */
    public function goBack() {
        if (isset($_GET['return'])) {
            $url = $_GET['return'];
        }
        else if (isset($_SERVER['HTTP_REFERER'])) {
            $url = $_SERVER['HTTP_REFERER'];
        }
        else {
            $url = E()->getSiteManager()->getCurrentSite()->root;
        }
        $this->setHeader('Location', $url);
        $this->commit();
    }

    public function disableCache() {
        $this->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate');
        $this->setHeader('Pragma', 'no-cache');
        $this->setHeader('X-Accel-Expires', 0);
    }

    /**
     * Отправляет ответ клиенту и завершает работу программы.
     * это - точка выхода
     *
     * @access public
     * @return void
     */
    public function commit() {
        if (!headers_sent()) {
            $this->sendHeaders();
            $this->sendCookies();
        } else {
            //throw new SystemException('ERR_HEADERS_SENT', SystemException::ERR_CRITICAL);
        }
        $contents = $this->body;

        if (
            (bool)Object::_getConfigValue('site.compress')
            &&
            isset($_SERVER['HTTP_ACCEPT_ENCODING'])
            &&
            (strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== false)
            &&
            !(bool)Object::_getConfigValue('site.debug')
        ) {
            header("Vary: Accept-Encoding");
            header("Content-Encoding: gzip");
            $contents = gzencode($contents, 6);
        }
        echo $contents;
        session_write_close();
        exit;
    }
}
