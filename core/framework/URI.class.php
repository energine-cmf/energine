<?php

/**
 * Класс URI.
 *
 * @package energine
 * @subpackage core
 * @author 1m
 * @copyright ColoCall 2006
 * @version $Id$
 */

//require_once('core/framework/Object.class.php');

/**
 * URI (Unified Resource Identifier).
 *
 * @package energine
 * @subpackage core
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
     * Конструктор класса.
     *
     * @access public
     * @param string $uri
     * @return void
     */
    public function __construct($uri) {
        parent::__construct();

        if (preg_match('/^(\w+):\/\/([a-z0-9\.\-]+)(\/[^?]*)(\?(.*))?$/i', $uri, $matches) && count($matches) >= 4) {
            $this->setScheme($matches[1]);
            $this->setHost($matches[2]);
            $this->setPath($matches[3]);
            $this->setQuery(isset($matches[5]) ? $matches[5] : '');
        }
        else {
            $this->scheme = $this->host = $this->path = $this->query = $this->fragment = '';
        }
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
        if (empty($port)) {
            $port = 80;
        }
        
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
