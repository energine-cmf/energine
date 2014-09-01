<?php
/**
 * @file
 * CommentsHelper
 *
 * It contains the definition to:
 * @code
class CommentsHelper;
@endcode
 *
 * @author sign
 *
 * @version 1.0.0
 */
namespace Energine\comments\gears;
use Energine\share\gears\DataDescription, Energine\share\gears\Data, Energine\share\gears\FieldDescription, Energine\share\gears\Field, Energine\share\gears\Builder, Energine\share\gears\TreeBuilder, Energine\share\gears\TreeConverter;
/**
 * Comments helper.
 *
 * @code
class CommentsHelper;
@endcode
 *
 * Example from news component:
 * @code
$comments = new CommentsHelper('stb_news_comment');
$comments->createAndAddField(
$this->getDataDescription(),
$this->getData(),
$this->getData()->getFieldByName('news_id')->getData()
);
@endcode
 */
class CommentsHelper extends Comments
{
    /**
     * Comment field name.
     * @var string $commentsFieldName
     */
    private $commentsFieldName = 'comments';
	
	/**
	 * @copydoc Comments::createInstanceFor
	 */
	public static function createInstanceFor($tableName, $isTree=false){
		//@TODO переделать
		if($commentTable = E()->getController()->dbh->tableExists($tableName. '_comment')){
			return new CommentsHelper($commentTable, $isTree);
		}
		return null;
	}
	
	/**
     * Get comments, build and integrate as field @c $fieldName into Data and DataDescription.
	 *
	 * @param DataDescription $desc Data description.
	 * @param Data $data Data.
	 * @param int|array $targetIds Comment ID(s)
	 * @param string $fieldName Field name.
	 */
	public function createAndAddField(DataDescription $desc, Data $data, $targetIds, $fieldName='comments'){
		$desc->addFieldDescription($this->createFieldDescription($fieldName));
		$data->addField($this->createField($targetIds));
	}
	
	/**
     * Create field description to the comment.
	 *
	 * @param string $fieldName Field name.
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
     * Create field.
	 *
	 * @param int|array $targetIds Comment ID(s).
	 * @return Field
	 */
	protected function createField($targetIds){
		 $f = new Field($this->commentsFieldName);
		 $f->setRowProperty(0, 'is_tree', (bool)$this->isTree);
		 $f->setRowProperty(0, 'is_editable', (int)E()->getAUser()->isAuthenticated());
		 
		 $data = $this->getBuildedListByIds($targetIds);
		 
		 $f->setData($data);
		 return $f;
	}

    //todo VZ: I do not understand this function.
	/**
	 * Get built list.
     *
	 * @param int|array $targetIds Comment ID(s).
	 * @return \DOMNode
	 */
	public function getBuildedListByIds($targetIds){
		$data = $this->getListByIds($targetIds); // список комментариев
		$data = $this->addUsersInfo($data); // в $data добавляем инфу о пользователях
		return $this->buildComments($data);
	}
	
	/**
     * Build comments.
     *
	 * @param array $data Data.
	 * @return \DOMNode
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
     * Create new data description.
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
		
		return $dataDescription;
	}
	
	/**
     * Add information about users.
	 *
	 * @param array $data Data.
	 * @return array
	 */
	private function addUsersInfo($data){
		if($data && is_array($data)){
			$usersInfo = $this->getUsersByComments($data);
			$usersInfo = convertDBResult($usersInfo, 'u_id');

			foreach($data as &$item){
				$user = $usersInfo[$item['u_id']];
				$item['u_fullname'] = $user['u_fullname'];
			}
		}
		return $data;
	}

    //todo VZ: same function name in CommentsList
	/**
     * Get users who left comments.
	 *
	 * @param array $data Data.
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

