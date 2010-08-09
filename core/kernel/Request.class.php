<?php

/**
 * Класс Request.
 *
 * @package energine
 * @subpackage core
 * @author 1m.dm
 * @copyright Energine 2006
 */


/**
 * HTTP-запрос.
 *
 * @package energine
 * @subpackage core
 * @author 1m.dm
 * @final
 */
final class Request extends Singleton {
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

        $this->uri = URI::create();
        $path = $this->uri->getPath();
        
        if(strpos($path, SiteManager::getInstance()->getCurrentSite()->root) !== false) {
	        $path = array_values(
	            array_diff(
	                explode(
	                    '/', 
	                    substr(
	                        $path, 
	                        strlen(
	                            SiteManager::getInstance()->getCurrentSite()->root
	                        )
	                    )
	                 ), 
	                 array('')
	              )
	        );
        }
        else {
        	$path = array();
        }
        
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
     * Возвращает URI запроса.
     *
     * @access public
     * @return URI
     */
    public function getURI() {
        return $this->uri;
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
