<?php
/**
 * Класс URI.
 *
 * @package energine
 * @subpackage kernel
 * @author 1m
 * @copyright Energine 
 */


/**
 * URI (Unified Resource Identifier).
 *
 * @package energine
 * @subpackage kernel
 * @author 1m
 * @final
 */
final class URI extends Object {

    /**
     * @access private
     * @var string схема (протокол) запроса
     */
    private $scheme;

    /**
     * @access private
     * @var string сервер (имя хоста)
     */
    private $host;

    /**
     * @access private
     * @var array путь
     */
    private $path;

    /**
     * @access private
     * @var string строка параметров
     */
    private $query;

    /**
     * @access private
     * @var string идентификатор фрагмента документа
     */
    private $fragment;
    
    /**
     * Trick для имитации приватного конструктора
     * 
     * @access private
     * @var bool
     * @static 
     */
     private static $trick;
    
    /**
     * Порт
     * 
     * @access private
     * @var int 
     */
     private $port;
    
    

    /**
     * Конструктор класса.
     *
     * @access private
     * @param string $uri
     * @return void
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
    /**
     * Проверяет правильность URL. ВОзвращает массив, полученный в результате разбора строки
     * 
     * @return array
     * @access public
     * @static
     */
    public static function validate($uri){
    	$result = false;
        if(preg_match('/^(\w+):\/\/([a-z0-9\.\-]+)\:([0-9]{2,5})?(\/[^?]*)[\?]?(.*)?$/i', $uri, $matches) && count($matches) >= 5){
            array_shift($matches);
            $result = $matches;
        }
        
        return $result;    
    }
    /**
      * Создает объект URI
      * 
      * @return URI
      * @access public
      * @static
      */
    public static function create($uriString = ''){
    	self::$trick = true;
        if(!$uriString){
            $host = explode(':', ((isset($_SERVER['HTTP_HOST']))?$_SERVER['HTTP_HOST']:$_SERVER['SERVER_NAME']));
            $protocol = (isset($_SERVER['HTTPS']) ? 'https' : 'http');

            if(sizeof($host) == 1){
                $port = ($protocol == 'http')?80:443;
            }
            else{
                list($host, $port) = $host;
            }

            $uriString = $protocol.'://'.$host.':'.$port.$_SERVER['REQUEST_URI'];
        }
        return new URI($uriString);
    }

    /**
     * Устанавливает схему (протокол) URI.
     *
     * @access public
     * @param string $scheme
     * @return void
     */
    public function setScheme($scheme) {
        $this->scheme = strtolower($scheme);
    }

    /**
     * Возвращает схему (протокол) URI.
     *
     * @access public
     * @return string
     */
    public function getScheme() {
        return $this->scheme;
    }

    /**
     * Устанавливает имя хоста.
     *
     * @access public
     * @param string $host
     * @return void
     */
    public function setHost($host) {
        $this->host = strtolower($host);
    }

    /**
     * Возвращает имя хоста.
     *
     * @access public
     * @return string
     */
    public function getHost() {
        return $this->host;
    }
    
    /**
     * Устанавливает порт
     *
     * @access public
     * @return void
     */
    public function setPort($port) {
        $this->port = $port;
    }
    /**
     * Возвращает идентификатор порта
     *
     * @access public
     * @return int
     */
    public function getPort() {
        return $this->port;
    }    

    /**
     * Устанавливает путь.
     *
     * @access public
     * @param $path
     * @return void
     */
    public function setPath($path) {
        if (!is_array($path)) {
            $path = array_values(array_diff(explode('/', $path), array('')));
        }
        $this->path = $path;
    }

    /**
     * Возвращает путь в виде массива сегментов или в виде строки,
     * если установлен флаг $asString.
     *
     * @access public
     * @param boolean $asString
     * @return string
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
     * Возвращает сегмент пути с индексом $pos.
     *
     * @access public
     * @param int $pos
     * @return string
     */
    public function getPathSegment($pos) {
        if (isset($this->path[$pos])) {
            return $this->path[$pos];
        }
        return '';
    }

    /**
     * Устанавливает строку параметров.
     *
     * @access public
     * @param string $query
     * @return void
     */
    public function setQuery($query) {
        $this->query = strval($query);
    }

    /**
     * Возвращает строку параметров.
     *
     * @access public
     * @return string
     */
    public function getQuery() {
        return $this->query;
    }

    /**
     * Устанавливает идентификатор фрагмента документа.
     *
     * @access public
     * @param string $fragment
     * @return void
     */
    public function setFragment($fragment) {
        $this->fragment = strval($fragment);
    }

    /**
     * Возвращает идентификатор фрагмента документа.
     *
     * @return string
     * @access public
     */
    public function getFragment() {
        return $this->fragment;
    }

    /**
     * Возвращает строковое представление URI.
     *
     * @access public
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
