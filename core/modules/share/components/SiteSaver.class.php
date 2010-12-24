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
            E()->TagManager->bind($_POST['tags'], $id, 'share_sites_tags');
        } 
        
        if($this->getMode() == QAL::INSERT){
            //При создании нового проекта   ищем параметр конфигурации указывающий на идентификатор 
            //шаблонного раздела
            if(isset($_POST['copy_site_structure'])) {
                $this->copyStructure((int)$_POST['copy_site_structure'], $id);
            } 
            else {
                //Если не задан параметр конфигурации  - создаем одну страницу
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
        }
        return $result;
    }
    
    /**
      * Копирование структуры сайта
      * 
      * @return void
      * @access private
      */
    private function copyStructure($sourceSiteID, $destinationSiteID){
        $source = $this->dbh->select(
            'share_sitemap', 
            array('smap_id', 'smap_layout', 'smap_content', 'smap_pid', 'smap_segment', 'smap_order_num', 'smap_redirect_url'),
            array('site_id' => $sourceSiteID)
        );
        
        if(is_array($source)){
           $oldtoNewMAP = $this->copyRows($source, null, '', $destinationSiteID);
           foreach ($oldtoNewMAP as $oldID => $newID) {
            $this->dbh->modifyRequest('
                INSERT INTO share_sitemap_translation( 
                    smap_id, 
                    lang_id, 
                    smap_name, 
                    smap_description_rtf, 
                    smap_html_title, 
                    smap_meta_keywords, 
                    smap_meta_description, 
                    smap_is_disabled) 
                SELECT 
                    %s, 
                    lang_id, 
                    smap_name, 
                    smap_description_rtf, 
                    smap_html_title, 
                    smap_meta_keywords, 
                    smap_meta_description, 
                    smap_is_disabled
                 FROM share_sitemap_translation
                 WHERE smap_id = %s
                 ', $newID, $oldID
            );
            $this->dbh->modifyRequest(
                'INSERT INTO share_sitemap_tags(smap_id, tag_id)
                 SELECT %s, tag_id
                    FROM share_sitemap_tags
                    WHERE smap_id = %s
                ', $newID, $oldID
            );
            $this->dbh->modifyRequest(
                'INSERT INTO share_access_level '.
                '(smap_id, right_id, group_id) '.
                'SELECT %s, al.right_id, al.group_id '.
                   'FROM `share_access_level` al '.
                   'WHERE al.smap_id = %s', $newID, $oldID
            );	
           }
        }
        //throw new SystemException('sdsdsd');
    }
    
    /**
      * Рекурсивный итератор по набору данных для копирования 
      * 
      * @return void
      * @access private
      */
    private function copyRows($source, $PID, $newPID, $siteID){
    	$result = array();
    	//inspect(func_get_args());
        foreach($source as $key => $row) {
           if($row['smap_pid'] == $PID) {
           	    
           	   $newRow = $row; 
               $newRow['site_id'] = $siteID;
               $newRow['smap_pid'] = $newPID;
               if($row['smap_segment'] === '') $newRow['smap_segment'] = QAL::EMPTY_STRING;
               $oldPID = $row['smap_id'];
               unset($newRow['smap_id']);
               //unset($source[$key]);
               $result += $this->copyRows($source, $oldPID, $result[$row['smap_id']] = $this->dbh->modify(QAL::INSERT, 'share_sitemap', $newRow ), $siteID);
           }
        }
        
        return $result;
    }
}