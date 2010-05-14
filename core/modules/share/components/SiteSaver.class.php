<?php 
/**
 * Содержит класс SiteSaver
 *
 * @package energine
 * @subpackage share
 * @author d.pavka
 * @copyright d.pavka@gmail.com
 */

 /**
  * Сохранятор для редактора сайтов
  *
  * @package energine
  * @subpackage share
  * @author d.pavka@gmail.com
  */
 class SiteSaver extends Saver {
    /**
     * Конструктор класса
     *
     * @access public
     */
    public function __construct() {
        parent::__construct();
    }
    /**
     * После сохранения данных сайта, создает новый раздел , переводы и права
     * 
     * @return mixed
     * @access public
     */
    public function save(){
    	$mainTableName = 'share_sites';
    	$translationTableName = 'share_sites_translation';
    	
        if (isset($_POST[$mainTableName]['site_is_default']) && $_POST[$mainTableName]['site_is_default'] !== '0') {
            $this->dbh->modify(QAL::UPDATE, $mainTableName, array('site_is_default'=>0));
        }
    	$result = parent::save();
    	$id = ($this->getMode() == QAL::INSERT)?$result:$this->getData()->getFieldByName('site_id')->getRowData(0);
        
        //Записываем информацию в таблицу тегов
        if(isset($_POST['tags'])){
            TagManager::getInstance()->bind($_POST['tags'], $id, 'share_sites_tags');         
        } 
            if($this->getMode() == QAL::INSERT){
               $smapId = $this->dbh->modifyRequest(
               'INSERT INTO share_sitemap '.
               '(site_id, `smap_layout`, `smap_content`, `smap_segment`) '.
               'SELECT %s, smap_layout, smap_content, \'\' '.
               'FROM `share_sitemap` '.
               'WHERE site_id= %s and smap_pid is null',
               $id,
               ($siteId = SiteManager::getInstance()->getDefaultSite()->id)
           );   
           foreach($_POST[$translationTableName] as $langID => $siteInfo){
               $this->dbh->modify(
                  QAL::INSERT,
                  'share_sitemap_translation',
                  array(
                      'lang_id' => $langID,
                      'smap_id' => $smapId,
                      'smap_name' => $siteInfo['site_name']
                  )
               );   
           }
           $this->dbh->modifyRequest(
	           'INSERT INTO share_access_level '.
	           '(smap_id, right_id, group_id) '.
	           'SELECT %s, al.right_id, al.group_id '.
	           'FROM `share_access_level` al '.
	           'LEFT JOIN share_sitemap s ON s.smap_id = al.smap_id '.
	           'WHERE s.smap_pid is NULL AND site_id= %s',
               $smapId,
               $siteId 
           );
           
        }
        return $result;
    }
}