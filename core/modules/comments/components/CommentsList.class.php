<?php
/**
 * Содержит класс CommentsList
 *
 * @package energine
 * @subpackage comments
 * @author sign
 */

/**
 * Компонент комментариев
 *
 * @package energine
 * @subpackage comments
 * @author sign
 *
 * Пример использования
 *
 * $commentsParams = array(
            'table_name' => $this->getTableName(),
            'is_tree' => false,
            'target_ids' => $this->getData()->getFieldByName('news_id')->getData()
        );
        $commentsList = $this->document->componentManager->createComponent('commentsList', 'comments', 'CommentsList', $commentsParams);
        $commentsList->run();
        $this->document->componentManager->addComponent($commentsList);
 */
class CommentsList extends DataSet
{
	/**
	 * Коментарии древовидные
	 * @var bool
	 */
	protected $isTree = false;


    protected $targetIds = array();

    /**
     * @var array
     */
    private $loadedData = null;

    /**
     * @var Comments
     */
    protected $comments = null;

    private $commentsFieldName = '';

    /**
     * @var Component
     */
    private $bindComponent = null;

    /**
     * Конструктор
     *
     * В $param ожидаются поля
     * table_name string - имя комментируемой таблицы
     * target_ids array - айдишники комментируемых сущностей
     * is_tree bool - комментарии древовидные?
     *
     * @param string  $name
     * @param string  $module

     * @param  array $params
     * @return void
     */
    public function __construct($name, $module,   array $params = null) {
        parent::__construct($name, $module,  $params);
        $this->setProperty('exttype', 'comments');
        $this->setType(self::COMPONENT_TYPE_LIST);

        $commentTable = $this->dbh->tableExists($this->getParam('table_name'). '_comment');

		$this->isTree = $this->getParam('is_tree');

        $this->setProperty('is_tree', $this->isTree);

        $right = $this->document->getRights();
        $this->setProperty('is_editable', (int)($right > 1)); // добавлять и править/удалять своё
        $this->setProperty('is_admin', (int)($right > 2));    // godmode
        if(AuthUser::getInstance()->isAuthenticated()){
            $this->document->setProperty('CURRENT_UID', AuthUser::getInstance()->getID());
        }

        $this->targetIds = $this->getParam('target_ids');

        $this->comments = new Comments($commentTable, $this->isTree);
    }

    /**
     * Описание полей комментария
     * @return DataDescription
     */
    protected function createDataDescription(){
        $dataDescription = new DataDescription();

		$fd = new FieldDescription('comment_id');
		$fd->setType(FieldDescription::FIELD_TYPE_INT);
		$fd->setProperty('key', true); // для построения дерева
		$dataDescription->addFieldDescription($fd);

		// если у нас древовидная структура - добавляем предка
		if($this->isTree){
			$fd = new FieldDescription('comment_parent_id');
			$fd->setType(FieldDescription::FIELD_TYPE_INT);
			$dataDescription->addFieldDescription($fd);
		}

		// комментиркемая сущность
		$fd = new FieldDescription('target_id');
		$fd->setType(FieldDescription::FIELD_TYPE_INT);
		$dataDescription->addFieldDescription($fd);

		$fd = new FieldDescription('u_id');
		$fd->setType(FieldDescription::FIELD_TYPE_INT);
		$dataDescription->addFieldDescription($fd);

		$fd = new FieldDescription('comment_created');
		$fd->setType(FieldDescription::FIELD_TYPE_DATETIME)->setProperty('outputFormat', '%E');
		$dataDescription->addFieldDescription($fd);

		$fd = new FieldDescription('comment_name');
		$fd->setType(FieldDescription::FIELD_TYPE_STRING);
		$dataDescription->addFieldDescription($fd);

		$fd = new FieldDescription('comment_approved');
		$fd->setType(FieldDescription::FIELD_TYPE_BOOL);
		$dataDescription->addFieldDescription($fd);

		// Инфа о юзере
		$fd = new FieldDescription('u_nick');
		$fd->setType(FieldDescription::FIELD_TYPE_STRING);
		$dataDescription->addFieldDescription($fd);

		$fd = new FieldDescription('u_avatar_img');
		$fd->setType(FieldDescription::FIELD_TYPE_IMAGE);
		$dataDescription->addFieldDescription($fd);

        $fd = new FieldDescription('u_sex');
		$fd->setType(FieldDescription::FIELD_TYPE_STRING);
		$dataDescription->addFieldDescription($fd);

        $fd = new FieldDescription('u_place');
		$fd->setType(FieldDescription::FIELD_TYPE_STRING);
		$dataDescription->addFieldDescription($fd);

		return $dataDescription;
    }

    /**
     * Создать билдер
     *
     * Если комментарии не древовидные или отсутствуют возвращается Builder иначе - TreeBuilder 
     * @return Builder|TreeBuilder
     */
    protected function createBuilder(){
        if($this->isTree and is_array($data = $this->loadData())){
            $builder = new TreeBuilder();
            $tree = TreeConverter::convert($data, 'comment_id', 'comment_parent_id');
            $builder->setTree($tree);
        }
        else{
            $builder = parent::createBuilder();
        }
        $this->builder = $builder;
        return $this->builder;
    }

    /**
     * Создаём педжер только один раз
     * @return void
     */
    protected function createPager() {
       if(!$this->pager){
           parent::createPager();
       }
    }

    /**
     * Загружаем комментарии и информацию о пользователях
     *
     * @return array
     */
    protected function loadData(){
        if(is_null($this->loadedData)){
            $this->createPager();

            $limitArr = null;
            if(!$this->isTree and $this->pager) {
                // pager существует -- загружаем только часть данных, текущую страницу
                $limitArr = $this->pager->getLimit();
            }
            $this->loadedData = $this->comments->getListByIds($this->targetIds, $limitArr);
            if($this->pager and $limitArr){
                $this->setType(self::COMPONENT_TYPE_LIST);
                $this->pager->setRecordsCount($this->comments->getCountByLastList());
            }
            $this->loadedData = $this->addUsersInfo($this->loadedData);

            $this->setProperty('comment_count', is_array($this->loadedData) ? count($this->loadedData) : 0);
        }
        return $this->loadedData;
    }


    /**
     * @return array
     */
    protected function defineParams() {
        return array_merge(
            parent::defineParams(),
            array(
                'table_name' => '',
                'is_tree' => 0,
                'target_ids' => array(),
                'bind' => '',
                'commentsFieldName' => 'comments',
            )
        );
    }

    /**
	 * Внедряем инфу о юзерах в комментарии
	 *
	 * @param array $data
	 * @return array
	 */
	private function addUsersInfo($data){
		if($data && is_array($data)){
			$usersInfo = $this->getUsersByComments($data);
			$usersInfo = convertDBResult($usersInfo, 'u_id');

			foreach($data as &$item){
				$user = $usersInfo[$item['u_id']];
				$item['u_nick'] = trim($user['u_nick']) ? trim($user['u_nick']) : $user['u_fullname'];
                $item['u_sex'] = $user['u_sex'];
                $item['u_place'] = $user['u_place'];
                if($user['u_avatar_img']){
				    $item['u_avatar_img'] = $user['u_avatar_img'];
                }
                else{
                    $item['u_avatar_img'] = $this->getNotExistsAvatar($item['u_id'], $user['u_is_male']);
                }
			}
		}
		return $data;
	}

    /**
     * Путь к несуществующей аватарке
     * @param  int $uid
     * @param  boolean $sex
     * @return string
     *
     * @see CommentsForm::getNotExistsAvatar()
     */
    private function getNotExistsAvatar($uid, $sex = null){
        $addDir = '';
        if(!is_null($sex)){
            $addDir = intval((bool)$sex). '/';
        }
        return 'uploads/avatars/auto/'.$addDir. md5($uid). '.jpg';
    }

	/**
	 * Информация о юзерах оставивших комментарии
	 *
	 * @param array $data
	 * @return array
	 */
	private function getUsersByComments($data){
		$result = array();

		if($data && is_array($data)){
			$userIds =  array();
			foreach($data as $item){
				$userIds[] = $item['u_id'];
			}
			$userIds = array_unique($userIds);

			if($userIds){
				$userIds = implode(',', $userIds);
				$result = $this->dbh->selectRequest(
					'SELECT u.*, '.
                    ' CASE WHEN u.u_is_male IS NULL THEN "'.$this->translate('TXT_UNKNOWN').'" WHEN u_is_male = 1 THEN "'.$this->translate('TXT_MALE').'" ELSE "'.$this->translate('TXT_FEMALE').'" END as u_sex '.
					" FROM user_users u
					 WHERE u.u_id in($userIds)"
				);
			}
		}
		return $result;
	}

    /**
     * Если задан связаный компонент (параметер bind)
     * то добавляем ему поле comments (имя задаётся параметром commentsFieldName)
     *
     * @return void
     */
    protected function main(){
        parent::main();

        if($this->getParam('bind')){
            $this->bindComponent = $this->document->componentManager->getBlockByName($this->getParam('bind'));

            if(!$this->isTree and $this->pager) {
                $ap = $this->bindComponent->getActionParams(true);
                $subUrl = '';
                if(isset($ap['themeID'])){
                    $subUrl = $ap['themeID'].'/';
                }
                $this->pager->setProperty('additional_url', $subUrl);
            }
        }
    }
}

