<?php 
/**
 * Содержит класс ForumTheme
 *
 * @package energine
 * @subpackage forum
 * @author sign
 */

 /**
  * Темы форума
  *
  * @package energine
  * @subpackage forum
  * @author sign
  */
class ForumTheme extends DBDataSet {
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
        $this->setTableName('forum_theme');
    }

    /**
     * Переходим на список категорий форума -  список тем сам по себе не имеет смысла
     * @return void
     */
    protected function main(){
        $this->response->redirectToCurrentSection("../");
    }


    protected function view(){
        $this->addPropertyCurrUser();
        parent::view();

        // добавляем комментарии
        if($comments = CommentsHelper::createInsatceFor($this->getTableName())) {
            $comments->createAndAddField(
                $this->getDataDescription(),
                $this->getData(),
                $this->getData()->getFieldByName('theme_id')->getData()
            );
        }
        $this->getDataDescription()->getFieldDescriptionByName('u_id')->setType(FieldDescription::FIELD_TYPE_INT);
        $this->getDataDescription()->getFieldDescriptionByName('category_id')->setType(FieldDescription::FIELD_TYPE_INT);

        // добавляем описание извлечённых полей
        $this->addDescription();
    }

    protected function loadData(){
        $data = false;
        if($this->getAction() == 'view'){
            $themeId = $this->getActionParams();
        	list($themeId) = $themeId;
            $data = $this->loadTheme($themeId);
        }
        else{
            $data = parent::loadData();
        }
        return $data;
    }

    private function loadTheme($themeId){
        $sql = 'SELECT t.*, c.comment_created, c.comment_name,
            fc.category_name,
            u.u_id,
            IF(LENGTH(TRIM(u.u_nick)), u.u_nick, u.u_name) u_name,
            u.u_avatar_img
        FROM forum_theme t
            JOIN forum_category fc ON fc.category_id = t.category_id
            LEFT JOIN forum_theme_comment c ON c.comment_id = t.comment_id
            LEFT JOIN user_users u ON u.u_id = c.u_id
        WHERE t.theme_id = %s
        ';
        return $this->dbh->selectRequest($sql, $themeId);
    }

    private function addDescription(){
        $fd = new FieldDescription('category_name');
        $fd->setType(FieldDescription::FIELD_TYPE_STRING);
        $this->getDataDescription()->addFieldDescription($fd);

        $fd = new FieldDescription('u_id');
        $fd->setType(FieldDescription::FIELD_TYPE_INT);
        $this->getDataDescription()->addFieldDescription($fd);

        $fd = new FieldDescription('comment_created');
        $fd->setType(FieldDescription::FIELD_TYPE_DATETIME);
        $this->getDataDescription()->addFieldDescription($fd);

        $fd = new FieldDescription('comment_name');
        $fd->setType(FieldDescription::FIELD_TYPE_TEXT);
        $this->getDataDescription()->addFieldDescription($fd);

        $fd = new FieldDescription('u_name');
        $fd->setType(FieldDescription::FIELD_TYPE_STRING);
        $this->getDataDescription()->addFieldDescription($fd);

        $fd = new FieldDescription('u_avatar_img');
        $fd->setType(FieldDescription::FIELD_TYPE_IMAGE);
        $this->getDataDescription()->addFieldDescription($fd);
    }


	protected function create(){
        if(!AuthUser::getInstance()->isAuthenticated()){
			// @todo add SystemException::ERR_401
			throw new SystemException('ERR_404', SystemException::ERR_404);
		}

        $categoryId = $this->getActionParams();
        list($categoryId) = $categoryId;
        if(!$categoryId){
            throw new SystemException('ERR_404', SystemException::ERR_404);
		}

        $this->setType(self::COMPONENT_TYPE_FORM_ADD);
        $this->prepare();
        $this->setDataSetAction("save-theme/");

        $this->getDataDescription()->getFieldDescriptionByName('theme_text')->setType(FieldDescription::FIELD_TYPE_HTML_BLOCK);
        $this->getDataDescription()->getFieldDescriptionByName('category_id')->setType(FieldDescription::FIELD_TYPE_HIDDEN);
        $this->getData()->getFieldByName('category_id')->setData($categoryId);
	}

    protected function save(){
        // нечего сохранять
		if(!isset($_POST['forum_theme'])){
			// @todo redirect to edit|create
			throw new SystemException('ERR_404', SystemException::ERR_404);
		}
        $data = $_POST['forum_theme'];

        // не авторизованый юзер
        if(!AuthUser::getInstance()->isAuthenticated()){
			// @todo add SystemException::ERR_401
			throw new SystemException('ERR_404', SystemException::ERR_404);
		}

        if(isset($data['theme_id']) and $themeId = intval($data['theme_id'])){
            $condition = array('theme_id' => $themeId);
            if(!$this->isCanEditTheme($themeId)){
                // @todo add SystemException::ERR_401
                throw new SystemException('ERR_404', SystemException::ERR_404);
            }
        }
        else{
            $themeId = 0;
            $data['theme_created'] = date('Y-m-d H:i:s');
            $condition = null;
            $data['u_id'] = AuthUser::getInstance()->getID();
        }

		$data['theme_id'] = (int)$themeId;

		$res = $this->dbh->modify(
			$themeId ? QAL::UPDATE : QAL::INSERT,
			$this->getTableName(),
			$data,
            $condition
		);

		if(!$themeId){
			$themeId = (int)$res;
		}
		$this->response->redirectToCurrentSection("$themeId/");
	}

    /**
     * Редактируем тему
     *
     * @throws SystemException если тема не существует
     * @return void
     */
	protected function modify(){

        if($themeId = $this->getActionParams()){
			// редактируем существующий пост
        	list($themeId) = $themeId;
		}
		else{
			throw new SystemException('ERR_404', SystemException::ERR_404);
		}

        $this->addFilterCondition(array('theme_id' => $themeId));
        $this->setType(self::COMPONENT_TYPE_FORM_ALTER);
        $this->setDataSetAction("$themeId/save-theme/");

		$this->prepare();
        $this->getDataDescription()->getFieldDescriptionByName('theme_id')->setType(FieldDescription::FIELD_TYPE_HIDDEN);
	}

    /**
     * Информация о текущем пользователеле
     * @see forum.xslt
     * @return void
     */
    private function addPropertyCurrUser(){
        if(AuthUser::getInstance()->isAuthenticated()){
    		$this->setProperty('curr_user_id', AuthUser::getInstance()->getID());
    	}
        // признак админа - выводим ему ссылки edit/delete во всех блогах
        if(in_array('1', AuthUser::getInstance()->getGroups())){
    		$this->setProperty('curr_user_is_admin', '1');
    	}
    }

    /**
     * Удаляем тему
     * @throws SystemException
     * @return void
     */
    protected function remove(){
        $themeId = $this->getActionParams();
        list($themeId) = $themeId;

        if(!$this->isCanEditTheme($themeId)){
            // @todo add SystemException::ERR_401
			throw new SystemException('ERR_404', SystemException::ERR_404);
        }

        $this->dbh->modify(QAL::DELETE, $this->getTableName(), null, array('theme_id'=>$themeId));

        $this->response->redirectToCurrentSection("/../../");
    }

    /**
     * Может ли текущий юзер редактировать/удалять тему
     *
     * @param  $themeId
     * @return bool
     */
    private function isCanEditTheme($themeId){
        $access = false;

        if(!$themeId or !AuthUser::getInstance()->isAuthenticated())
		    return false;

        // администратор
        if(in_array('1', AuthUser::getInstance()->getGroups())){
    		$access = true;
    	}
        else{
            // создатель темы?
            $uid = AuthUser::getInstance()->getID();
            if($themeUId = $this->dbh->select($this->getTableName(),'u_id', array('theme_id' => $themeId))){
                if($themeUId[0]['u_id'] == $uid){
                    $access =  true;
                }
            }
        }
        return $access;
    }
}