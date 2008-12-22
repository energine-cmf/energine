<?php

/**
 * Класс Request.
 *
 * @package energine
 * @subpackage core
 * @author 1m.dm
 * @copyright ColoCall 2006
 * @version $Id$
 */

//require_once('core/framework/URI.class.php');

/**
 * HTTP-запрос.
 *
 * @package energine
 * @subpackage core
 * @author 1m.dm
 * @final
 */
final class Request extends Object {

    /**
     * @access private
     * @var Request единый в системе экземпляр класса Request (паттерн Singleton)
     */
    private static $instance;

    /**
     * @access private
     * @var URI текущий URI запроса
     */
    private $uri;

    /**
     * @access private
     * @var string путь к корню сайта в общем пути URI
     */
    private $rootPath;

    /**
     * @access private
     * @var string язык, указанный в URI
     */
    private $lang;

    /**
     * @access private
     * @var array путь из URI запроса (без пути к корню и языка)
     */
    private $path;

    /**
     * @access private
     * @var int смещение в пути, разделяющее путь шаблона, и путь, относящийся к действию
     */
    private $offset;

    /*
     * Типы пути:
     */

    /**
     * Полный путь
     */
    const PATH_WHOLE = 1;

    /**
     * Путь шаблона
     */
    const PATH_TEMPLATE = 2;

    /**
     * Путь, относящийся к действию
     */
    const PATH_ACTION = 3;

    /**
     * Конструктор класса.
     *
     * @access private
     * @return void
     */
    public function __construct() {
        parent::__construct();

        $uri = (isset($_SERVER['HTTPS']) ? 'https' : 'http').'://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
        $this->uri = new URI($uri);
        $path = $this->uri->getPath();
        $rootPath = $this->getConfigValue('site.root');
        if ($rootPath[strlen($rootPath)-1] != '/') {
            $rootPath .= '/';
        }
        $rootPathLen = strlen($rootPath);
        $this->rootPath = '';
        if (strpos($path, $rootPath) === 0) {
            $this->rootPath = substr($path, 0, $rootPathLen);
        }
        $path = array_values(array_diff(explode('/', substr($path, $rootPathLen)), array('')));
        try {
            $language = Language::getInstance();
            $this->lang = (isset($path[0]) && $language->isValidLangAbbr($path[0])) ? array_shift($path) : '';
        }
        catch (SystemException $e){
            $this->lang = '';
        }
        $this->path = $path;
    }

    /**
     * Возвращает единый для всей системы экземпляр класса Request.
     *
     * @access public
     * @return Request
     * @static
     */
    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new Request;
        }
        return self::$instance;
    }

    /**
     * Возвращает URI запроса.
     *
     * @access public
     * @return URI
     */
    public function getURI() {
        return $this->uri;
    }

    /**
     * Возвращает путь к корню сайта.
     *
     * @access public
     * @return string
     */
    public function getRootPath() {
        return $this->rootPath;
    }

    /**
     * Возвращает URI-адрес корня сайта.
     *
     * @access public
     * @return string
     */
    public function getBasePath() {
        return $this->getURI()->getScheme().'://'.$this->getURI()->getHost().$this->getRootPath();
    }

    /**
     * Возвращает язык, указанный в URI запроса.
     *
     * @access public
     * @return string
     */
    public function getLang() {
        return $this->lang;
    }

    /**
     * Возвращает сегмент(аббревиатуру) языка
     *
     * @access public
     * @return string
     */
    public function getLangSegment() {
        return (empty($this->lang) ? '' : $this->lang.'/');
    }

    /**
     * Возвращает путь из URI запроса.
     *
     * @access public
     * @param int $what тип пути - определяет какую часть пути вернуть
     * @param boolean $asString вернуть путь в виде строки
     * @return array
     */
    public function getPath($what = self::PATH_WHOLE, $asString = false) {
        $path = array();
        switch ($what) {
            case self::PATH_WHOLE:
                $path = $this->path;
                break;
            case self::PATH_TEMPLATE:
                $path = array_slice($this->path, 0, $this->offset);
                break;
            case self::PATH_ACTION:
                $path = array_slice($this->path, $this->offset);
                break;
        }
        if ($asString) {
            $path = (empty($path) ? '' : implode('/', $path).'/');
        }
        return $path;
    }

    /**
     * Устанавливает смещение в пути, разделяющее путь шаблона, и путь, относящийся к действию.
     *
     * @access public
     * @param int $offset
     * @return void
     */
    public function setPathOffset($offset) {
        $this->offset = $offset;
    }

    /**
     * Возвращает смещение в пути.
     *
     * @access public
     * @return int
     */
    public function getPathOffset() {
        return $this->offset;
    }

    /**
     * Возвращает IP-адрес клиента.
     *
     * @access public
     * @return string
     */
    public function getClientIP() {
        $ip = $_SERVER['REMOTE_ADDR'];
        if (isset($_ENV['HTTP_X_FORWARDED_FOR']) && ip2long($_ENV['HTTP_X_FORWARDED_FOR']) != -1) {
            $ip = $_ENV['HTTP_X_FORWARDED_FOR'];
        }
        return $ip;
    }
}
