<?php 
/**
 * Содержит класс SiteList
 *
 * @package energine
 * @subpackage share
 * @author d.pavka
 * @copyright d.pavka@gmail.com
 */

 /**
  * Список сайтов
  *
  * @package energine
  * @subpackage share
  * @author d.pavka@gmail.com
  */
 class SiteList extends DataSet {
    /**
     * Конструктор класса
     *
     * @param string $name
     * @param string $module
     * @param Document $document
     * @param array $params
     * @access public
     */
    public function __construct($name, $module, Document $document,  array $params = null) {
        parent::__construct($name, $module, $document,  $params);
        $this->setType(self::COMPONENT_TYPE_LIST);
    }

    protected function defineParams() {
        $result = array_merge(parent::defineParams(),
        array(
        'tags' => '',
        'recordsPerPage' => false
        ));
        return $result;
    }    
    
    /**
      * Загружаем данные SiteManager
      * 
      * @return array
      * @access protected
      */
    protected function loadData(){
    	$result = array();
    	$filteredIDs = true;
    	
    	if($this->getParam('tags'))
            $filteredIDs = TagManager::getInstance()->getFilter($this->getParam('tags'), 'share_sites_tags');
        
        if(!empty($filteredIDs))    	
        foreach(SiteManager::getInstance() as $siteID => $site){
        	if(
        	   ($filteredIDs !== true)  && in_array($siteID, $filteredIDs)
        	   ||
        	   ($filteredIDs === true)
        	){
	            $result[] = array(
	                'site_id' => $site->id,
	                'site_name' => $site->name,
	                'site_host' => $site->protocol.'://'.$site->host.(($site->port != 80)?':'.$site->port:'').$site->root    
                );
        	}    	
        }
        return $result;
    }
    
    
}