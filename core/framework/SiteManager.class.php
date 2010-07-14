<?php
/**
 * Содержит класс SiteManager
 *
 * @package energine
 * @subpackage core
 * @author d.pavka
 * @copyright d.pavka@gmail.com
 */

/**
 * Работа с сайтам
 *
 * @package energine
 * @subpackage core
 * @author d.pavka@gmail.com
 * @final
 */
final class SiteManager extends DBWorker implements Iterator {
	/**
	 * Инстанс текущего класса
	 *
	 * @access private
	 * @var SiteManager
	 * @static
	 */
	private static $instance;
		
	/**
	 * Данные о всех зарегистрированных сайтах 
	 *
	 * @access private
	 * @var array
	 */
	private $data;
	/**
	 * Индекс используемый при итерации
	 * Заодно используется как флаг для паттерна синглтон 
	 * 
	 * @access private
	 * @var int 
	 * @static
	 */
	 private static $index = null;
	
	/**
	 * Идентификатор текущего сайта
	 * 
	 * @access private
	 * @var int 
	 */
	 private $currentSiteID = null;
		
	/**
	 * Конструктор класса
	 *
	 * @access private
	 */
	public function __construct(URI $uri) {
		if(is_null(self::$index)){
			throw new Exception('ERR_PRIVATE_CONSTRUCTOR', SystemException::ERR_DEVELOPER);
		}
		parent::__construct();
		$tmpData = $this->dbh->select('share_sites');
		foreach($tmpData as $siteData){
			$site = $this->data[$siteData['site_id']] = new Site($siteData);
			if(
			($site->protocol == $uri->getScheme()) &&
			($site->host == $uri->getHost()) &&
			($site->port == $uri->getPort())
			){
				$realPathSegments = array_values(array_filter(explode('/',$site->root)));
				$pathSegments = array_slice($uri->getPath(false), 0, sizeof($realPathSegments));
				
				if(($realPathSegments == $pathSegments) && $site->isActive) {
					$this->currentSiteID = $site->id;
				}
			}
		}
		
		if(is_null($this->currentSiteID)){
			foreach($this->data as $siteID => $site){
				if($site->isDefault == 1){
					$this->currentSiteID = $siteID;
				}
			}
		}
		
	}
	/**
	 * Возвращает синглтон
	 *
	 * @return SiteManager
	 * @access public
	 * @static
	 */
	public static function getInstance() {
		self::$index = 0;
		if (!isset(self::$instance)) {
			self::$instance = new SiteManager(URI::create());
		}
		return self::$instance;
	}
	/**
	 * Возвращает екземпляр объекта Site по идентификатору
	 * 
	 * @return Site
	 * @access public
	 */
	public function getSiteByID($siteID){
	    if(!isset($this->data[$siteID])){
	       throw new SystemException('ERR_NO_SITE', SystemException::ERR_DEVELOPER, $siteID);	
	    }
	    return $this->data[$siteID];
	}
	
	/**
	 * Возвращает экземпляр объекта сайт по идентфикатору страницы
	 * 
	 * @param int идентфикатор страницы
	 * @return Site
	 * @access public
	 */
	public function getSiteByPage($pageID){
	    return $this->getSiteByID(
	       simplifyDBResult(
            $this->dbh->select('share_sitemap', 'site_id', array('smap_id' => $pageID)),
            'site_id',
            true
            )
	    );
	}
	
	/**
	  * Returns current's site
	  * 
	  * @return string
	  * @access public
	  */
	public function getCurrentSite(){
        return $this->data[$this->currentSiteID];	    
		
	}
	/**
	 * Возвращает сайт по умолчанию
	 * 
	 * @return Site
	 * @access public
	 */
	public function getDefaultSite(){
	    foreach ($this->data as $site){
	    	if($site->isDefault){
	    		return $site;
	    	}
	    }
	    throw new SystemException('ERR_NO_DEFAULT_SITE', SystemException::ERR_DEVELOPER);
	}
	/**
	 * Возвращает текущий элемент при итерации  
	 * 
	 * @return Site
	 * @access public
	 * @see Iterator
	 */
	public function current(){
	   $siteIDs = array_keys($this->data);
	   
	   return $this->data[$siteIDs[self::$index]];       
	}
	/**
	 * Возвращает идентификатор текущего сайта(при итерации только)
	 * 
	 * @return int
	 * @access public
	 * @see Iterator
	 */
	public function key(){
		$siteIDs = array_keys($this->data);
	    return $siteIDs[self::$index];
	}
	
	/**
	 * Передвигает счетчик на следующий елемент
	 * 
	 * @return void
	 * @access public
	 * @see Iterator
	 */
	public function next(){
	   self::$index ++;    
	}
	
	/**
	 * Сбрасывает счетчик текущих елементов на начало 
	 * 
	 * @return void
	 * @access public
	 * @see Iterator
	 */
	public function rewind(){
	    self::$index = 0;    
	}
	
	/**
	 * Возвращает флаг указывающий существует ли елемент
	 * 
	 * @return boolean
	 * @access public
	 * @see Iterator
	 */
	public function valid(){
	   $siteIDs = array_keys($this->data);
	   return isset($siteIDs[self::$index]);    
	}
	
}