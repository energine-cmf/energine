<?php
/**
 * @file
 * Response.
 *
 * It contains the definition to:
 * @code
final class Response;
@endcode
 *
 * @author 1m.dm
 * @copyright Energine 2006
 *
 * @version 1.0.0
 */
namespace Energine\share\gears;
/**
 * HTTP-response.
 *
 * @code
final class Response;
@endcode
 *
 * @final
 */
final class Response extends Object {
    /**
     * Reason phrases.
     * @var mixed $reasonPhrases
     */
    private $reasonPhrases;

    /**
     * Line of the response status.
     * @var string $statusLine
     */
    private $statusLine;

    /**
     * Response header.
     * @var array $headers
     */
    private $headers;

    /**
     * Response cookies.
     * @var array $cookies
     */
    private $cookies;

    /**
     * Response body.
     * @var string $body
     */
    private $body;

    public function __construct() {
        $this->reasonPhrases = include_once('reasonPhrases.inc.php');
        $this->setStatus(200);
        $this->headers = array();
        $this->cookies = array();
        $this->body = '';
    }

    /**
     * Prepare redirection URL.
     * It replaces @c lang and @c site to the correspond values.
     *
     * @param string $redirectURL Redirection URL.
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
     * Set response status.
     *
     * @param int $statusCode Status code.
     * @param string $reasonPhrase Reason phrase.
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
     * Set response header.
     *
     * @param string $name Header name.
     * @param string $value Header value.
     * @param boolean $replace Defines whether the header value should be replaced.
     */
    public function setHeader($name, $value, $replace = true) {
        if ((!$replace) && isset($this->headers[$name])) {
            return;
        }
        $this->headers[$name] = $value;
    }

    /**
     * Set cookies.
     *
     * @param string $name Session name.
     * @param string $value Value.
     * @param int $expire Expire time.
     * @param bool $domain Domain.
     * @param string $path Path.
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
        //todo VZ: remove this?
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
     * Send cookies to the list.
     *
     * This is used only in @c commit. It is made public for possibility to call this from capcha (but this is exception).
     */
    public function sendCookies() {
        foreach ($this->cookies as $name => $params) {
            setcookie($name, $params['value'], $params['expire'], $params['path'], $params['domain'], $params['secure']);
        }
    }

    /**
     * Send headers.
     */
    public function sendHeaders() {
        header($this->statusLine);
        foreach ($this->headers as $name => $value) {
            header("$name: $value");
        }
    }

    /**
     * Remove cookie by name.
     *
     * @param string $name
     */
    public function deleteCookie($name) {
        $this->addCookie($name, '', (time() - 1));
    }

    /**
     * Set redirection URL and redirect.
     *
     * @param string $location Redirection URL.
     * @param int $status
     * @throws InvalidArgumentException
     */
    public function setRedirect($location, $status = 302) {
        if(!in_array($status, array(301, 302))) throw new InvalidArgumentException();

        $this->setStatus($status);
        $this->setHeader('Location', $location);
        $this->setHeader('Content-Length', 0);
        $this->commit();
    }

    /**
     * Redirect to current section.
     *
     * @param string $action Action name.
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
     * Add data to the response body.
     *
     * @param string $data New data.
     */
    public function write($data) {
        $this->body .= $data;
    }

    /**
     * Go back.
     *
     * @see auth.php
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

    /**
     * Disable cache.
     */
    public function disableCache() {
        $this->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate');
        $this->setHeader('Pragma', 'no-cache');
        $this->setHeader('X-Accel-Expires', 0);
    }

    /**
     * Send response to the client.
     * @note This the last step.
     */
    public function commit() {
        if (!headers_sent()) {
            $this->sendHeaders();
            $this->sendCookies();
        } else {
            //throw new SystemException('ERR_HEADERS_SENT', SystemException::ERR_CRITICAL);
        }
        $contents = $this->body;

        if ((bool)Object::_getConfigValue('site.compress')
            && isset($_SERVER['HTTP_ACCEPT_ENCODING'])
            && (strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== false)
            && !(bool)Object::_getConfigValue('site.debug')
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
