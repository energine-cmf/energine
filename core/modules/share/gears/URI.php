<?php
/**
 * @file
 * URI.
 *
 * It contains the definition to:
 * @code
final class URI;
@endcode
 *
 * @author 1m
 * @copyright Energine
 *
 * @version 1.0.0
 */
namespace Energine\share\gears;

/**
 * URI (Unified Resource Identifier).
 *
 * @final
 */
final class URI extends Object {
    /**
     * Request scheme (protocol).
     * @var string $scheme
     */
    private $scheme;

    /**
     * Host name.
     * @var string $host
     */
    private $host;

    /**
     * Path.
     * @var array $path
     */
    private $path;

    /**
     * Query of parameters.
     * @var string $query
     */
    private $query;

    /**
     * Document fragment ID.
     * @var string $fragment
     */
    private $fragment;

    /**
     * Trick for imitation of private constructor.
     * @var bool $trick
     */
    private static $trick;

    /**
     * Port number.
     * @var int $port
     */
    private $port;

    /**
     * @param string $uri URI.
     *
     * @throws SystemException 'ERR_PRIVATE_CONSTRUCTOR'
     */
    public function __construct($uri) {
        if(is_null(self::$trick)){
            throw new SystemException('ERR_PRIVATE_CONSTRUCTOR', SystemException::ERR_DEVELOPER);
        }
        $matches = array();

        if($uri && ($matches = self::validate($uri))){
            $this->setScheme($matches[0]);
            $this->setHost($matches[1]);
            $this->setPort($matches[2]);
            $this->setPath($matches[3]);
            $this->setQuery(isset($matches[4]) ? $matches[4] : '');
        }
        else {
            $this->scheme = $this->host = $this->path = $this->query = $this->fragment = '';
        }
    }

    //todo VZ: I think the using of throws is better than return false.
    /**
     * Validate URI.
     * It returns an array of matched strings.
     *
     * @param string $uri URI
     * @return array|bool
     */
    public static function validate($uri){
        $result = false;
        if(preg_match('/^(\w+):\/\/([a-z0-9\.\-]+)\:?([0-9]{2,5})?(\/[^?]*)[\?]?(.*)?$/i', $uri, $matches) && count($matches) >= 5){
            array_shift($matches);
            $result = $matches;
        }
        return $result;
    }

    /**
     * Create URI.
     *
     * @param string $uriString URI string.
     * @return URI
     */
    public static function create($uriString = ''){
        self::$trick = true;
        if(!$uriString){
            $host = explode(':', ((isset($_SERVER['HTTP_HOST']))?$_SERVER['HTTP_HOST']:$_SERVER['SERVER_NAME']));
            $protocol = (isset($_SERVER['HTTPS']) ? 'https' : 'http');

            if(sizeof($host) == 1){
                $port = ($protocol == 'http')?80:443;
                list($host) = $host;
            }
            else{
                list($host, $port) = $host;
            }

            $uriString = $protocol.'://'.$host.':'.$port.$_SERVER['REQUEST_URI'];
        }
        return new URI($uriString);
    }

    /**
     * Set scheme (protocol)
     *
     * @param string $scheme Scheme.
     */
    public function setScheme($scheme) {
        $this->scheme = strtolower($scheme);
    }

    /**
     * Get scheme (protocol).
     *
     * @return string
     */
    public function getScheme() {
        return $this->scheme;
    }

    /**
     * Set host name.
     *
     * @param string $host Host name.
     */
    public function setHost($host) {
        $this->host = strtolower($host);
    }

    /**
     * Get host name.
     * @return string
     */
    public function getHost() {
        return $this->host;
    }

    /**
     * Set port number.
     * If @c $port is not defined, then the port @c 80 will be used.
     *
     * @param int $port Port number.
     */
    public function setPort($port) {
        if(!$port) $port = 80;
        $this->port = $port;
    }

    /**
     * Get port.
     *
     * @return int
     */
    public function getPort() {
        return $this->port;
    }

    /**
     * Set path.
     *
     * @param string $path Path.
     */
    public function setPath($path) {
        if (!is_array($path)) {
            $path = array_values(array_diff(explode('/', $path), array('')));
        }
        $this->path = $path;
    }

    /**
     * Get path.
     *
     * @param boolean $asString Return as string?
     * @return array|string
     */
    public function getPath($asString = true) {
        $path = $this->path;
        if ($asString) {
            if (!empty($path)) {
                $path = '/'.implode('/', $path).'/';
            }
            else {
                $path = '/';
            }
        }
        return $path;
    }

    /**
     * Get path segment with @c $pos index.
     *
     * @param int $pos Position
     * @return string
     */
    public function getPathSegment($pos) {
        if (isset($this->path[$pos])) {
            return $this->path[$pos];
        }
        return '';
    }

    /**
     * Set query.
     *
     * @param string $query Query.
     */
    public function setQuery($query) {
        $this->query = strval($query);
    }

    /**
     * Get query.
     *
     * @return string
     */
    public function getQuery() {
        return $this->query;
    }

    /**
     * Set document fragment.
     *
     * @param string $fragment Document fragment.
     */
    public function setFragment($fragment) {
        $this->fragment = strval($fragment);
    }

    /**
     * Get document fragment.
     *
     * @return string
     */
    public function getFragment() {
        return $this->fragment;
    }

    /**
     * Get URI as string.
     *
     * @return string
     */
    public function __toString() {
        if (!empty($this->scheme) && !empty($this->host)) {
            return $this->scheme.'://'.$this->host.
            (empty($this->path) ? '/' : $this->getPath(true)).
            (empty($this->query) ? '' : '?'.$this->query).
            (empty($this->fragment) ? '' : '#'.$this->fragment);
        }
        else {
            return '';
        }
    }
}
