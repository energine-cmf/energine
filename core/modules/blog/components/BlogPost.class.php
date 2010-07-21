<?php
/**
 * Класс Содержит класс списка постов блога.
 *
 * @package energine
 * @subpackage blog
 * @author sign
 */

 /**
 * Посты блога
 *
 * @package energine
 * @subpackage blog
 * @author sign
 */
class BlogPost extends DBDataSet {
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
        $this->setTableName('blog_post');
//        $this->setFilter(array('post_is_draft'=>0));
        $this->setParam('onlyCurrentLang', true);
        
        $this->setOrder('post_created', QAL::DESC);
    }
    
    /**
     * Все посты одного блога
     * 
     * @return void
     */
    protected function viewBlog(){
    	$this->prepare();
	}

    /**
     * Редактируем пост
     *
     * @throws SystemException если пост не существует
     * @return void
     */
	protected function edit(){
        $this->checkAccess();
        
        if($postId = $this->getActionParams()){
			// редактируем существующий пост
        	list($postId) = $postId;
		}
		else{
			throw new SystemException('ERR_404', SystemException::ERR_404);
		}

        $this->setDataSetAction("post/$postId/save/");

		$this->prepare();
	}

    private function checkAccess(){
        if(!AuthUser::getInstance()->isAuthenticated()){
			// @todo add SystemException::ERR_401
			throw new SystemException('ERR_404', SystemException::ERR_404);
		}
    }

    /**
     * Сохранить пост
     *
     * Ожидает $_POST['blog_post']
     *
     * @throws SystemException для неавторизированного пользователя и для 
     * @return void
     */
	protected function save(){
		$this->checkAccess();
		
		if(!isset($_POST['blog_post'])){
			// @todo redirect to edit|create
			throw new SystemException('ERR_404', SystemException::ERR_404);
		}
			
		$data = $_POST['blog_post'];
		
		// целой блог
		$blogId = $this->dbh->select('blog_title', 'blog_id', 
			array('u_id' => AuthUser::getInstance()->getID())
		);
		if(!$blogId){
			// блог не существует - юзер не завёл себе блог
			throw new SystemException('ERR_404', SystemException::ERR_404);
		}
		list($blogId) = $blogId;
		
		if($postId = $this->getActionParams()){
			// редактируем существующий пост
        	list($postId) = $postId;
            $condition = array('post_id'=>$postId);
		}
		else{
			// создаём новый
			$postId = 0;
			$data['post_created'] = date('Y-m-d H:i:s');
            $condition = null;
		}
		
		
		$data['blog_id'] = (int)$blogId;

		$res = $this->dbh->modify(
			$postId ? QAL::UPDATE : QAL::INSERT, 
			$this->getTableName(), 
			$data,
            $condition
		);
		// @todo check error
	
		if(!$postId){
			$postId = (int)$res;
		}
		$this->response->redirectToCurrentSection("post/$postId/edit/");
	}
	
	/**
	 * Создать новый пост
	 * 
	 * @throws SystemException Если юзер не авторизован или если он не завёл себе блог
	 */
	protected function create(){
		$this->checkAccess();
		
        $this->setType(self::COMPONENT_TYPE_FORM);
        $this->setDataSetAction("post/save/");

        $this->prepare();
	}
	
	protected function createData() {
		if($this->getAction() == 'create'){
			$result =  new Data();
		}
		else{
			$result = parent::createData();
		}
		return $result;
	}

    /**
     * Загружаем данные
     * 
     * @return array|false
     */
    protected function loadData(){
    	$data = false;
    	
    	if($this->getAction() == 'main') {
	    	if(is_array($res = $this->loadLastPosts())){
				$data = $res;
				$this->addUserInfoDataDescription();
				$this->loadCommentCount($data);
	        }
    	}
    	elseif($this->getAction() == 'viewBlog') {
    		$blogId = $this->getActionParams();
        	list($blogId) = $blogId;
	    	if(is_array($data = $this->loadBlog($blogId))){
				$this->addUserInfoDataDescription();
				$this->loadCommentCount($data);
	        }
	        else $data = false;
    	}
    	elseif($this->getAction() == 'view') {
    		$postId = $this->getActionParams();
        	list($postId) = $postId;
	    	if(is_array($data = $this->loadPosts($postId))){
				$this->addUserInfoDataDescription();
	        }
	        else $data = false;
    	}
        elseif($this->getAction() == 'edit') {
    		$postId = $this->getActionParams();
        	list($postId) = $postId;
	    	if(!is_array($data = $this->loadPosts($postId))){
				$data = false;
	    	}
    	}
    	else{
    		$data = parent::loadData();
    	}
    	return $data;
    }
    
    /**
     * Записи в блогах
     * 
     * Безпараметров возвращает все записи в блогах
     * 
     * @param int $postId
     * @param int $blogId
     * @param string $limit
     * @return array
     */
    private function loadPosts($postId=0, $blogId=0, $limit=''){
    	$where = '1 ';
    	if($postId){
    		$where .= ' and p.post_id ='.intval($postId);
    	}
    	if($blogId){
    		$where .= ' and b.blog_id ='. intval($blogId);
    	}
        if($limit) $limit = ' LIMIT '. $limit;
         
        $sql = "SELECT p.*, b.blog_name, u.u_fullname, u.u_nick, u.u_avatar_img, u.u_id 
        	FROM blog_post p
        		JOIN blog_title b ON p.blog_id = b.blog_id
        		JOIN user_users u ON u.u_id = b.u_id
        		WHERE $where
        		ORDER BY p.post_created DESC
        		$limit";
        return $this->dbh->selectRequest($sql);
    }
    
    /**
     * Последние записи в блогах
     * 
     * @return array
     */
	private function loadLastPosts(){
        if ($this->pager) {
            // pager существует -- загружаем только часть данных, текущую страницу
            $limit = implode(',', $this->pager->getLimit());
        }
        else{
        	$limit = '';
        }
        return $this->loadPosts(0, 0, $limit);
    }
    
    /**
     * Записи одного блога
     * 
     * @param int $blogId
     * @return array
     */
	private function loadBlog($blogId){
        if ($this->pager) {
            // pager существует -- загружаем только часть данных, текущую страницу
            $limit = implode(',', $this->pager->getLimit());
        }
        else{
        	$limit = '';
        }
        return $this->loadPosts(0, $blogId, $limit);
    }
    
    /**
     * Одна запись
     * 
     * @param int $postId
     * @return array
     */
    private function loadPost($postId){
    	return $this->loadPosts($postId, 0, 1);
    }
    
    /**
     * Добавляем описание полей с инфой о пользователе
     * 
     */
    protected function addUserInfoDataDescription(){
    	$fd = new FieldDescription('u_id');
		$fd->setType(FieldDescription::FIELD_TYPE_INT);
		$this->getDataDescription()->addFieldDescription($fd);
		
    	$fd = new FieldDescription('u_nick');
		$fd->setType(FieldDescription::FIELD_TYPE_STRING);
		$this->getDataDescription()->addFieldDescription($fd);
		
		$fd = new FieldDescription('u_fullname');
		$fd->setType(FieldDescription::FIELD_TYPE_STRING);
		$this->getDataDescription()->addFieldDescription($fd);
		
		$fd = new FieldDescription('u_avatar_img');
		$fd->setType(FieldDescription::FIELD_TYPE_IMAGE);
		$this->getDataDescription()->addFieldDescription($fd);
    }
    
    /**
     * Отменяем формирование списка блогов
     * 
     * @return void
     */
    protected function prepare(){
        // добавляем id текущего пользователя
    	if(AuthUser::getInstance()->isAuthenticated()){
    		$this->setProperty('curr_user_id', AuthUser::getInstance()->getID());
    	}
    	parent::prepare();

        // в выводе методов main, view etc - blog_id представлен как список - отменяем
    	if($this->getAction() != 'create' and $this->getAction() != 'edit'){
    		$this->getDataDescription()->getFieldDescriptionByName('blog_id')->setType(FieldDescription::FIELD_TYPE_INT);
    	}
    	else{
    		$this->setType(self::COMPONENT_TYPE_FORM);
    	}
    	
    }
    
   /**
     * Делаем компонент активным
     * 
     * @return array
     */
    protected function defineParams() {
        return array_merge(
        parent::defineParams(),
        array(
        'active' => true
        )
        );
    }
    
   /**
     * Просмотр поста с комментариями
     *
     * @access protected
     * @return void
     */
    protected function view() {
        parent::view();
        if($this->getData()->isEmpty()) throw new SystemException('ERR_404', SystemException::ERR_404);

        //показываем комментарии
        if($comments = CommentsHelper::createInsatceFor($this->getTableName(), true)){
	 		$comments->createAndAddField(
	 			$this->getDataDescription(),
	 			$this->getData(), 
	 			$this->getData()->getFieldByName('post_id')->getData()
	 		);
    	}

    }
    
   /**
     * считаем комментарии для загруженных постов
     * 
     * @param array $data
     */
    protected function loadCommentCount(&$data){
		$comments = Comments::createInsatceFor($this->getTableName());
		if($commentCount = $comments->getCountByIds(simplifyDBResult($data, 'post_id'))){
	    	foreach($data as &$item){
	        	if(key_exists($item['post_id'], $commentCount)){
					$item['comments_num'] = $commentCount[$item['post_id']];
				}
				else{
					$item['comments_num'] = 0;
				}
			}
            $fd = new FieldDescription('comments_num');
			$fd->setType(FieldDescription::FIELD_TYPE_INT);
			$this->getDataDescription()->addFieldDescription($fd);
        }
    }
}