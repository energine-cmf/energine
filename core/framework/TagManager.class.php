<?php 

/**
 * Содержит класс TagManager
 *
 * @package energine
 * @subpackage core
 * @author d.pavka
 * @copyright d.pavka@gmail.com
 */

 /**
  * Класс реализующий функциональность по управлению тегами
  *
  * @package energine
  * @subpackage core
  * @author d.pavka@gmail.com
  */
 class TagManager extends Singleton {
 	/**
     * Имя таблицы тегов
 	 */
 	const TAG_TABLENAME = 'share_tags';
 	const TAG_SEPARATOR = ',';
 	
    /**
     * Конструктор класса
     *
     * @access public
     */
    public function __construct() {
        parent::__construct();
    }
    /**
     * Связывание набора тегов с определенным полем
     * 
     * @param $tags string строка тегов
     * @param $mapValue string имя поля-связки в связующей таблице 
     * @param $mapTableName string имя связующей таблицы 
     * @return array
     * @access public
     */
    public function bind($tags, $mapValue, $mapTableName){
        if(!$this->dbh->tableExists($mapTableName)) {
            throw new SystemException('ERR_WRONG_TABLE_NAME', SystemException::ERR_DEVELOPER, $mapTableName);    
        }
        $tags = array_filter(array_map(create_function('$tag', 'return mb_convert_case(trim($tag), MB_CASE_LOWER, "UTF-8");'), explode(self::TAG_SEPARATOR, $tags)));
        //Анализируем структуру таблицы
        $columns = array_keys($this->dbh->getColumnsInfo($mapTableName));
        unset($columns['tag_id']);
        list($mapFieldName) = $columns;        
        $this->dbh->modify(QAL::DELETE, $mapTableName, null, array($mapFieldName => $mapValue));
        
        if(!empty($tags)){
	        foreach($tags as $tag){
	        	try{
	        	  $this->dbh->modify(QAL::INSERT, self::TAG_TABLENAME, array('tag_name' => $tag));
	        	}
	        	catch(Exception $e){
	        	
	        	}
	        }
	        $tagIDs = array_keys($this->getID($tags));
	        foreach($tagIDs as $tagID){
	        	$this->dbh->modify(QAL::INSERT, $mapTableName, array($mapFieldName => $mapValue, 'tag_id' => $tagID));
	        }
        }
    }
    /**
     * Вытягивает имена тегов по переданной информации из связующей таблицы
     * 
     * @param $mapValues string
     * @param $mapTableName
     * @return $tags array
     * @access public
     */
    public function pull($mapValue, $mapTableName){
        if(!$this->dbh->tableExists($mapTableName)) {
            throw new SystemException('ERR_WRONG_TABLE_NAME', SystemException::ERR_DEVELOPER, $mapTableName);    
        }        
        $columns = array_keys($this->dbh->getColumnsInfo($mapTableName));
        $mapFieldName = '';
        unset($columns['tag_id']);
        list($mapFieldName) = $columns;
        $res = $this->dbh->select($mapTableName, array('tag_id'), array($mapFieldName => $mapValue));
        return $this->getTags(simplifyDBResult($res, 'tag_id'));
    }
    
    /**
     * Возвращает идентификатор(ы) тегов по переданным значениям
     * 
     * @param $tag mixed
     * @return array
     * @access public
     */
    public function getID($tag){
    	$result = null;
        if(!is_array($tag)){
        	$tag = array($tag);
        }
        $res = $this->dbh->select(self::TAG_TABLENAME, true, array('tag_name' => $tag));
        if(is_array($res)){
            foreach($res as $row){
            	$result[$row['tag_id']] = $row['tag_name'];
            }	
        }
        
        return $result;
    }
    
    /**
     * Возвращает перечень тегов 
     * 
     * @return array
     * @access public
     */
    public function getTags($tagID){
    	$result = array(); 
        if(!is_array($tagID)){
            $tagID = array($tagID);
        }
        
        $res = $this->dbh->select(self::TAG_TABLENAME, true, array('tag_id' => $tagID));
        if(is_array($res)){
            foreach($res as $row){
                $result[$row['tag_name']] = $row['tag_id'];
            }   
        }
        
        return $result;
    }
    
    /**
     * Возвращает набор ключей связок ассоциированных с тегом
     * 
     * @return array
     * @access public
     */
    public function getFilter($tags, $mapTableName){
        if(!$this->dbh->tableExists($mapTableName)) {
            throw new SystemException('ERR_WRONG_TABLE_NAME', SystemException::ERR_DEVELOPER, $mapTableName);    
        }
    	$result = array();
        $tagInfo = $this->getID($tags);
        if(!empty($tagInfo)){
	        $columns = array_keys($this->dbh->getColumnsInfo($mapTableName));
	        $mapFieldName = '';
	        unset($columns['tag_id']);
	        list($mapFieldName) = $columns;
	        $result = simplifyDBResult($this->dbh->select($mapTableName, array($mapFieldName), array('tag_id' => array_keys($tagInfo))), $mapFieldName);
        }
        return $result;
    }
    
}