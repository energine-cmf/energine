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
     * @var CommentsHelper
     */
    protected $comments = null;

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
     * @param Document $document
     * @param  array $params
     * @return void
     */
    public function __construct($name, $module, Document $document,  array $params = null) {
        parent::__construct($name, $module, $document,  $params);
        $this->setProperty('exttype', 'comments');
        $this->setProperty('type', 'list');

        $commentTable = $this->dbh->tableExists($this->getParam('table_name'). '_comment');

		$this->isTree = $this->getParam('is_tree');

        $this->setProperty('is_tree', $this->isTree);
        $this->setProperty('is_editable', (int)AuthUser::getInstance()->isAuthenticated());

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
		$fd = new FieldDescription('u_fullname');
		$fd->setType(FieldDescription::FIELD_TYPE_STRING);
		$dataDescription->addFieldDescription($fd);

		$fd = new FieldDescription('u_avatar_img');
		$fd->setType(FieldDescription::FIELD_TYPE_IMAGE);
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
        return $builder;
    }

    /**
     * Загружаем комментарии и информацию о пользователях
     *
     * @return array
     */
    protected function loadData(){
        if(is_null($this->loadedData)){
            $this->loadedData =  $this->comments->getListByIds($this->targetIds);
            $this->loadedData = $this->addUsersInfo($this->loadedData);

            // количество комментариев
            $this->setProperty('comment_count', is_array($this->loadedData)?count($this->loadedData):0);
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
                'is_tree' => 1,
                'target_ids' => array(),
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
				$item['u_fullname'] = $user['u_fullname'];
				$item['u_avatar_img'] = $user['u_avatar_img'];
			}
		}
		return $data;
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
					"SELECT *
					 FROM user_users
					 WHERE u_id in($userIds)"
				);
			}
		}
		return $result;
	}
}

