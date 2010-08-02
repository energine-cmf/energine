<?php 
/**
 * Содержит класс ForumCategory
 *
 * @package energine
 * @subpackage forum
 * @author sign
 */

 /**
  * Редактор категорий форумов
  *
  * @package energine
  * @subpackage forum
  * @author sign
  */
class ForumCategory extends DBDataSet {
    const numThemesInCategory = 2;
    
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
        $params['active'] = true;
        parent::__construct($name, $module, $document,  $params);
        $this->setTableName('forum_category');

        if(AuthUser::getInstance()->isAuthenticated()){
    		$this->setProperty('is_can_create_theme', 1);
    	}
    }

    protected function main(){
        if($res = parent::main()){
            // Добавляем темы в категории
            if(!$this->getData()->isEmpty()){
                $fd = new FieldDescription('themes');
                $fd->setSystemType(FieldDescription::FIELD_TYPE_CUSTOM);
                $this->getDataDescription()->addFieldDescription($fd);

                $f = new Field('themes');

                foreach($this->getData()->getFieldByName('category_id')->getData() as $categoryId){
                    $themes = $this->loadThemeByCategory($categoryId);

                    $f->addRowData($this->buildThemes($themes));
                    $this->getData()->addField($f);
                }
            }
        }

        return $res;
    }

    /**
     * Добавляем темы к категории
     * @return void
     */
    protected function view(){
        $this->addPropertyCurrUser();
        parent::view();

        if(!$this->getData()->isEmpty()){
            list($categoryId) = $this->getData()->getFieldByName('category_id')->getData();
            $themes = $this->loadThemeByCategory($categoryId);

            $this->addThemes($themes);
        }
    }

    private function addThemes($themes){
        $fd = new FieldDescription('themes');
		$fd->setSystemType(FieldDescription::FIELD_TYPE_CUSTOM);
        $this->getDataDescription()->addFieldDescription($fd);

        $f = new Field('themes');
        $f->setData($this->buildThemes($themes));
        $this->getData()->addField($f);
    }

    /**
     *
     *
     * @param  array $data
     * @return DOMNode
     */
    private function buildThemes(array $data){
        $localData = new Data();
        $localData->load($data);

    	$dataDescription = $this->createThemeDataDescription();

		$builder = new Builder();
        $builder->setData($localData);
        $builder->setDataDescription($dataDescription);

    	$builder->build();
    	return $builder->getResult();
    }

    /**
     * @return DataDescription
     */
    private function createThemeDataDescription(){
        $descriptions =  array(
            'u_id' =>           FieldDescription::FIELD_TYPE_INT,
            'theme_id' =>       FieldDescription::FIELD_TYPE_INT,
            'category_id' =>    FieldDescription::FIELD_TYPE_INT,
            'theme_created' =>  FieldDescription::FIELD_TYPE_DATETIME,
            'theme_name' =>     FieldDescription::FIELD_TYPE_STRING,
            'theme_text' =>     FieldDescription::FIELD_TYPE_STRING,
            'comment_num' =>    FieldDescription::FIELD_TYPE_INT,
            'comment_id' =>     FieldDescription::FIELD_TYPE_INT,
            'comment_created' =>FieldDescription::FIELD_TYPE_DATETIME,
            'comment_name' =>   FieldDescription::FIELD_TYPE_TEXT,
            'u_name' =>         FieldDescription::FIELD_TYPE_STRING,
            'u_avatar_img' =>   FieldDescription::FIELD_TYPE_IMAGE,
        );
        $dataDescription = new DataDescription();
        foreach($descriptions as $name => $fieldType){
            $fd = new FieldDescription($name);
            $fd->setType($fieldType);
            $dataDescription->addFieldDescription($fd);
        }
        return $dataDescription;
    }

    /**
     * @param  array|int $categoryId
     * @param int $limit
     * @return array
     */
    private function loadThemeByCategory($categoryId, $limit = 0){
        $sql =
            'SELECT t.*, c.comment_created, c.comment_name, u.u_id,
                IF(LENGTH(TRIM(u.u_nick)), u.u_nick, u.u_name) u_name,
                u.u_avatar_img
            FROM forum_theme t
                LEFT JOIN forum_theme_comment c ON c.comment_id = t.comment_id
                LEFT JOIN user_users u ON u.u_id = c.u_id
            WHERE t.category_id = %s
        ';
        return $this->dbh->selectRequest($sql, $categoryId);
    }

    protected function loadData(){
        $data = false;

    	if($this->getAction() == 'main'){
            $data = $this->loadCategoryWithLastThemes();
        }
        else{
            $data = parent::loadData();
        }

        return $data;
    }

    /**
     * @return array|false
     */
    protected function loadCategoryWithLastThemes($categoryId = 0){

        if($categoryId && !is_array($categoryId)){
            $categoryId = (int)$categoryId; 
            $sql = "SELECT c.*, t.theme_id, t.theme_created, t.theme_name
                FROM forum_category c
                    LEFT JOIN forum_theme t ON t.category_id = c.category_id
                    WHERE c.category_id={$categoryId} AND !t.theme_closed
                    ORDER BY t.theme_created DESC
                    LIMIT ". self::numThemesInCategory;
        }
        else{
            $sql = 'SELECT c.*, t.theme_id, t.theme_created, t.theme_name
                FROM forum_category c
                    LEFT JOIN forum_theme t ON t.category_id = c.category_id
                    WHERE !c.category_closed AND !t.theme_closed
                    GROUP BY c.category_id
                    ';
        }
        return $this->dbh->selectRequest($sql);
    }

    private function addPropertyCurrUser(){
        if(AuthUser::getInstance()->isAuthenticated()){
    		$this->setProperty('curr_user_id', AuthUser::getInstance()->getID());
    	}
        // признак админа - выводим ему ссылки edit/delete во всех блогах
        if(in_array('1', AuthUser::getInstance()->getGroups())){
    		$this->setProperty('curr_user_is_admin', '1');
    	}
    }
}