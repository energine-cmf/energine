<?php

/**
 * Хелпер комментариев
 * 
 * Пример вызова из компоннта новостей
 * 
 * $comments = new CommentsHelper('stb_news_comment');
 * $comments->createAndAddField(
 *		$this->getDataDescription(),
 *		$this->getData(), 
 *		$this->getData()->getFieldByName('news_id')->getData()
 *	);
 * 
 * 
 * @author sign
 *
 */
class CommentsHelper extends Comments
{
	private $commentsFieldName = 'comments';
	
	/**
	 * 
	 * @param string $tableName Комментируемая таблица
	 * @return CommentsHelper|NULL
	 */
	public static function createInstanceFor($tableName, $isTree=false){
		
		if($commentTable = DocumentController::getInstance()->dbh->tableExists($tableName. '_comment')){
			return new CommentsHelper($commentTable, $isTree);
		}
		return null;
	}
	
	/**
	 * Получить комментарии, сбилдить и встроить как поле $fieldName в Data и DataDescription
	 *
	 * @param DataDescription $desc
	 * @param Data $data
	 * @param mixed $targetIds int|int[] айдишники комментируемых сущностей
	 * @param string $fieldName имя поля в Dom
	 * @return void
	 */
	public function createAndAddField(DataDescription $desc, Data $data, $targetIds, $fieldName='comments'){
		$desc->addFieldDescription($this->createFieldDescription($fieldName));
		$data->addField($this->createField($targetIds));
	}
	
	/**
	 * Описание поля коментариев
	 *
	 * @param string $fieldName
	 * @return FieldDescription
	 */
	protected function createFieldDescription($fieldName=''){
		if($fieldName){
			$this->commentsFieldName = $fieldName;
		}
		$fd = new FieldDescription($this->commentsFieldName);
		$fd->setSystemType(FieldDescription::FIELD_TYPE_CUSTOM);
		return $fd;
	}
	
	/**
	 * Извлекаем комментарии и помещаем в Field
	 *
	 * @param mixed $targetIds int|int[]
	 * @return Field
	 */
	protected function createField($targetIds){
		 $f = new Field($this->commentsFieldName);
		 $f->setRowProperty(0, 'is_tree', (bool)$this->isTree);
		 $f->setRowProperty(0, 'is_editable', (int)AuthUser::getInstance()->isAuthenticated());
		 
		 $data = $this->getBuildedListByIds($targetIds);
		 
		 $f->setData($data);
		 return $f;
	}
	
	/**
	 * 
	 * @param mixed $targetIds int|int[]
	 * @return  DOMNode
	 */
	public function getBuildedListByIds($targetIds){
		$data = $this->getListByIds($targetIds); // список комментариев
		$data = $this->addUsersInfo($data); // в $data добавляем инфу о пользователях
		return $this->buildComments($data);
	}
	
	/**
	 * @param array $data
	 * @return  DOMNode
	 */
	private function buildComments($data){
    	$localData = new Data();
    	if(is_array($data))
    	   $localData->load($data);

    	$dataDescription = $this->createNewDataDescription();
        
		$builder = $this->isTree && !$localData->isEmpty()? new TreeBuilder() : new Builder();
        $builder->setData($localData);
        $builder->setDataDescription($dataDescription);
        
		if($this->isTree && !$localData->isEmpty()){
        	$tree = TreeConverter::convert($data, 'comment_id', 'comment_parent_id'); 
			$builder->setTree($tree);
		}
        
    	$builder->build();
    	return $builder->getResult(); 
	}
	
	/**
	 * 
	 * @return DataDescription
	 */
	public function createNewDataDescription(){
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
		$fd->setType(FieldDescription::FIELD_TYPE_DATETIME);	
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

