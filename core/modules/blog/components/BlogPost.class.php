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
  * Посты опубликованные в будущем - не публикуются
 *
 * @package energine
 * @subpackage blog
 * @author sign
 */
class BlogPost extends DBDataSet {
    /**
     * Календарь
     *
     * @access private
     * @var Calendar
     */
    private $calendar;

    /**
     * sql-фрагмент  условия запроса выборки записей в блогах по календарю
     * @see BlogPost::loadPosts()
     * @var string
     */
    private $calendarFilter = '';

    /**
     * Параметры построения календаря
     * @var array
     */
    private $calendarParams = array();

    /**
     * Конструктор класса
     *
     * @param string $name
     * @param string $module

     * @param array $params
     * @access public
     */
    public function __construct($name, $module,   array $params = null) {
        parent::__construct($name, $module,  $params);
        $this->setTableName('blog_post');
//        $this->setFilter(array('post_is_draft'=>0));
        $this->setParam('onlyCurrentLang', true);
        $this->setOrder(array('post_created' => QAL::DESC));

        if ($this->getParam('showCalendar') and in_array($this->getState(), array('main', 'viewBlog'))) {
            //наполняем $this->additionalFilter и $this->calendarParams
            // $this->addFilterCondition() бесполезен  - @see BlogPost::loadPosts()
            $this->calendarFilter = $this->createCalendarFilters($this->calendarParams);
            // а  для метода view параметры календаря - @see self::prepare()
        }
    }

    /**
     * Корректируем запросы по параметрам календаря из ActionParams
     * при выполнении методов main() и viewBlog
     * только если установлен параметер компонента showCalendar
     *
     * при просмотре одной записи блога в календаре будут даты блога этого поста, как у списка постов
     * но id блога вычисляется не здесь а после извлечения данных @see self::prepare()
     *
     * метод должен выполниться до вызова self::prepare()
     *
     * @param  array $calendarParams Параметры запроса построения календаря
     * @return string where-часть запроса выборки постов
     */
    protected function createCalendarFilters(array &$calendarParams){
        $additionalFilter = '';

        if (in_array($this->getState(), array('main', 'viewBlog')) and $this->getParam('showCalendar')) {
        $ap = $this->getStateParams(true);
            $dateFieldName = 'p.post_created';
            if (isset($ap['year']) && isset($ap['month']) &&
                    isset($ap['day'])) {
                if ($this->getParam('showCalendar')) {
                    $calendarParams['month'] = $ap['month'];
                    $calendarParams['year'] = $ap['year'];
                }
                //Фильтр будет добавлен позже, после того как будет обработан календарь, который использует фильтры компонента
                $additionalFilter =
                        'DAY('.$dateFieldName.') = "' . $ap['day'] .
                                '" AND MONTH('.$dateFieldName.') = "' .
                                $ap['month'] .
                                '" AND YEAR('.$dateFieldName.') = "' .
                                $ap['year'] . '"';
            }
            elseif (isset($ap['year']) && isset($ap['month'])) {
                if ($this->getParam('showCalendar')) {
                    $calendarParams['month'] = $ap['month'];
                    $calendarParams['year'] = $ap['year'];
                }
                $additionalFilter =
                        'MONTH('.$dateFieldName.') = "' . $ap['month'] .
                                '" AND YEAR('.$dateFieldName.') = "' .
                                $ap['year'] . '"';
            }
            elseif (isset($ap['year'])) {
                if ($this->getParam('showCalendar')) {
                    $calendarParams['year'] = $ap['year'];
                }
                $additionalFilter =
                        'YEAR('.$dateFieldName.') = "' . $ap['year'] . '"';
            }

            if ($this->getState() == 'viewBlog'){
                // ищем посты только одного блога
                $blogId = $this->getStateParams();
                list($blogId) = $blogId;
                $calendarParams['blog_id'] = $blogId;
                $calendarParams['template'] = "blogs/blog/$blogId/";
            }
        }
        
        return $additionalFilter;
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
        if($postId = $this->getStateParams()){
			// редактируем существующий пост
        	list($postId) = $postId;
		}
		else{
			throw new SystemException('ERR_404', SystemException::ERR_404);
		}

        $this->setDataSetAction("post/$postId/save/");

		$this->prepare();
        $this->getDataDescription()->getFieldDescriptionByName('blog_id')->setType(FieldDescription::FIELD_TYPE_HIDDEN);
	}

    /**
     * Изменять посты могут их владельцы и администраторы
     *
     * @throws SystemException при отсутствии доступа
     * @param  int $blogUid
     * @return void
     */
    private function checkAccess($blogUid){
        $user = E()->getAUser();
        if(!$user->isAuthenticated()
            or (($user->getID() != $blogUid) and (!in_array('1', $user->getGroups())))
        ){
			// @todo add SystemException::ERR_401
			throw new SystemException('ERR_404', SystemException::ERR_404);
		}
    }

    /**
     * Сохранить пост
     *
     * Ожидает $_POST['blog_post']
     *
     * после сохранения - перенаправление на просмотр
     *
     * @throws SystemException для неавторизированного пользователя и для 
     * @return void
     */
	protected function save(){
		if(!isset($_POST['blog_post'])){
			// @todo redirect to edit|create
			throw new SystemException('ERR_404', SystemException::ERR_404);
		}
			
		$data = $_POST['blog_post'];

        if(!isset($data['blog_id'])){
            if(!E()->getAUser()->isAuthenticated() or
                !$blogId = $this->dbh->select('blog_title', array('blog_id'),
                array('u_id' => E()->getAUser()->getID())
            )){
                throw new SystemException('ERR_404', SystemException::ERR_404);
            }
            else{
                list($blogId) = $blogId;
            }
        }
        else{
            $blogId = (int)$data['blog_id'];
            if(!$blogUid = $this->dbh->select('blog_title', array('u_id'),
                array('blog_id' => $blogId)
            )){
                // блог не существует - юзер не завёл себе блог
                throw new SystemException('ERR_404', SystemException::ERR_404);
            }
            // владелец блога
            list($blogUid) = $blogUid;

            $this->checkAccess($blogUid);
        }


		
		if($postId = $this->getStateParams()){
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
		$this->response->redirectToCurrentSection("post/$postId/");
	}

    /**
     * Действие по-умолчанию.
     *
     * @access protected
     * @return boolean
     */
    protected function main(){
        $res = parent::main();

        if ($f = $this->getData()->getFieldByName('post_created')) {
            foreach ($f as $fieldIndex => $date) {
                $f->setRowProperty($fieldIndex, 'year', date('Y', $date));
                $f->setRowProperty($fieldIndex, 'month', date('n', $date));
                $f->setRowProperty($fieldIndex, 'day', date('j', $date));
            }
        }

        return $res;
    }
	
	/**
	 * Создать новый пост
	 * 
	 * @throws SystemException Если юзер не авторизован или если он не завёл себе блог
	 */
	protected function create(){
        if(!E()->getAUser()->isAuthenticated()){
			// @todo add SystemException::ERR_401
			throw new SystemException('ERR_404', SystemException::ERR_404);
		}
        
        if(!$blogId = $this->dbh->select('blog_title', array('blog_id'),
			array('u_id' => E()->getAUser()->getID())
		)){
			// блог не существует - юзер не завёл себе блог
			throw new SystemException('ERR_404', SystemException::ERR_404);
		}
		
        $this->setType(self::COMPONENT_TYPE_FORM);
        $this->setDataSetAction("post/save/");

        $this->prepare();
	}
	
	protected function createData() {
		if($this->getState() == 'create'){
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
    	
    	if($this->getState() == 'main') {
            // все последние посты
	    	if(is_array($res = $this->loadLastPosts())){
				$data = $res;
				$this->addUserInfoDataDescription();
				$this->loadCommentCount($data);
	        }
    	}
    	elseif($this->getState() == 'viewBlog') {
            // все посты одного блога
    		$blogId = $this->getStateParams();
        	list($blogId) = $blogId;
	    	if(is_array($data = $this->loadBlog($blogId))){
				$this->addUserInfoDataDescription();
				$this->loadCommentCount($data);
	        }
	        else $data = false;
    	}
    	elseif($this->getState() == 'view') {
            // один пост
    		$postId = $this->getStateParams();
        	list($postId) = $postId;
	    	if(is_array($data = $this->loadPost($postId))){
				$this->addUserInfoDataDescription();
	        }
	        else $data = false;
    	}
        elseif($this->getState() == 'edit') {
    		$postId = $this->getStateParams();
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
     * Без параметров возвращает все записи в блогах
     *
     * Посты опубликованные в будущем - не публикуются
     * 
     * @param int $postId
     * @param int $blogId
     * @param string $limit
     * @return array
     */
    private function loadPosts($postId=0, $blogId=0, $limit=''){
    	$where = 'p.post_created < now() ';
    	$where = '1 ';
    	if($postId){
    		$where .= ' and p.post_id ='.intval($postId);
    	}
    	if($blogId){
    		$where .= ' and b.blog_id ='. intval($blogId);
    	}
        if($this->calendarFilter){
            // фильтры календаря, должны использоваться только в self::main() и self::viewBlog()
            $where .= " and ({$this->calendarFilter})";
        }
        if($limit) $limit = ' LIMIT '. $limit;
         
        $sql = "SELECT p.*, b.blog_name, u.u_fullname, u.u_nick, u.u_id
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
    	$item = $this->loadPosts($postId, 0, 1);

        if($item && $this->getParam('showCalendar')){
            // извлекаем из поста дату для календаря
            $date = new \DateTime();
            $date->setTimestamp($item[0]['post_created']);
            $this->calendarParams['month'] = $date->format('m');
            $this->calendarParams['year'] = $date->format('Y');
//                $calendarParams['date'] = \DateTime::createFromFormat('Y-m-d', $ap['year'].'-'.$ap['month'].'-'.$ap['day']);
                $this->calendarParams['date'] = $date;
        }
        return $item;
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
    }
    
    /**
     * Отменяем формирование списка блогов
     * 
     * @return void
     */
    protected function prepare(){
        // добавляем id текущего пользователя - что бы вывести ему ссылки create/edit/delete в его блоге
    	if(E()->getAUser()->isAuthenticated()){
    		$this->setProperty('curr_user_id', E()->getAUser()->getID());
    	}
        // признак админа - выводим ему ссылки edit/delete во всех блогах
        if(in_array('1', E()->getAUser()->getGroups())){
    		$this->setProperty('curr_user_is_admin', '1');
    	}
    	parent::prepare();

        // в выводе методов main, view etc - blog_id представлен как список - отменяем
    	if($this->getState() != 'create' and $this->getState() != 'edit'){
    		$this->getDataDescription()->getFieldDescriptionByName('blog_id')->setType(FieldDescription::FIELD_TYPE_INT);
    	}
    	else{
    		$this->setType(self::COMPONENT_TYPE_FORM);
    	}

        // параметры календаря $this->calendarParams наполняются восновном в конструкторе
        // но для метода self::view() параметры можно определить лишь после извлечения поста - нам нужна дата поста
        // @see BlogPost::loadPost
        if ($this->getParam('showCalendar')) {
            // фильтр календаря при просмотре одного поста
            if(($this->getState() == 'view') and !$this->getData()->isEmpty()){
                $this->calendarParams['blog_id'] = $this->getData()->getFieldByName('blog_id')->getData();
            }

            //Создаем компонент календаря новостей
            $this->document->componentManager->addComponent(
                $this->calendar = $this->document->componentManager->createComponent(
                    'blogCalendar', 'blog', 'BlogCalendar', $this->calendarParams
                )
            );
            $this->calendar->run();
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
        'active' => true,
        'showCalendar' => 0
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
