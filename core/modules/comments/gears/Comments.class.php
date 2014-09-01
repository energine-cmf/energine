<?php
/**
 * @file
 * Comments
 *
 * It contains the definition to:
 * @code
class Comments;
@endcode
 *
 * @author sign
 *
 * @version 1.0.0
 */
namespace Energine\comments\gears;
use Energine\share\gears\DBWorker, Energine\share\gears\QAL;
/**
 * Comments CRUD.
 *
 * @code
class Comments;
@endcode
 *
 * Examples:
 * - Comments IDs:
 * @code
$targetIds      = $this->getData()->getFieldByName('news_id')->getData(); // [3,4]
list($targetId) = $this->getData()->getFieldByName('news_id')->getData(); // 3
@endcode
 * - Amount of comments:
 * @code
$comments = new Comments('stb_news_comment');
$cc = $comments->getCountByIds($targetIds); // {3:12, 4:3:}
$cc = $comments->getCountByIds(array(-123)); // {0:0}
@endcode
 * - New comment:
 * @code
$comments->insertItem(
$targetId,
$userId,
'Comment text'
);
@endcode
 * - List of comments:
 * @code
$cList = $comments->getListByIds($targetId);
@endcode
 * - Edit and save:
 * @code
$cItem = $cList[0];
$cItem['comment_name'] = '123 1231 123123';
$comments->saveItem($cItem);
@endcode
 *
 * - Delete:
 * @code
$comments->deleteItem($targetId);
@endcode
 */
class Comments extends DBWorker
{
	/*
	 * Таблица комментируемой сущности
	 * @var string
	 */
//	private $baseTable = '';
	
	/**
     * Table with comments.
	 * @var string $commentTable
	 */
	protected $commentTable = '';
	
	/**
     * Are comments tree-like?
	 * @var bool $isTree
	 */
	protected $isTree = false;

    /**
     * Count last list.
     * @var int $countLastList
     */
    protected $countLastList = null;
	
	/**
	 * @param string $commentTable Table name with comments.
	 * @param bool $isTree Are comments tree-like?
	 */
	public function __construct($commentTable, $isTree=false){
		$this->commentTable = $commentTable;
		$this->isTree = $isTree;
		
		parent::__construct();
	}

	/**
	 * Create instance.
     *
	 * @param string $tableName Commented table.
     * @param bool $isTree Are comments tree-like?
	 * @return Comments|null
	 */
	public static function createInstanceFor($tableName, $isTree=false){
        //@TODO переделать
		if($commentTable = E()->getController()->dbh->tableExists($tableName. '_comment')){
			return new Comments($commentTable, $isTree);
		}
		return null;
	}
	
	/**
     * Get table with comments.
	 *
	 * @return string
	 */
	public function getCommentTable(){
		return $this->commentTable;
	}
	
	/**
     * Get list of comments by IDs.
     *
	 * @param int|array $targetIds Comment ID(s).
     * @param mixed $limitArr Array limit.
	 * @return array
     *
     * @see Comments::getCountByLastList()
	 */
	public function getListByIds($targetIds, $limitArr=null){
		if(!$targetIds)
			return array();
			
		if(is_array($targetIds)){
			$ids = implode(',', $targetIds);
			$cond = "target_id IN($ids)"; 
		}
		else{
			$cond = 'target_id = '. intval($targetIds);
		}

        if($limitArr){
            $limit = 'limit '. implode(',', $limitArr);
        }
        else{
            $limit = '';
        }

        if($limit){
            $comments = $this->dbh->selectRequest(
                "SELECT SQL_CALC_FOUND_ROWS *
                 FROM {$this->commentTable}
                 WHERE $cond
                 ORDER BY comment_created
                 $limit"
            );
            if(is_array($this->countLastList = $this->dbh->selectRequest('SELECT FOUND_ROWS() as c'))){
                list($this->countLastList) = $this->countLastList;
                $this->countLastList = $this->countLastList['c'];
            }
            else $this->countLastList = 0;
        }
        else{
            $comments = $this->dbh->selectRequest(
                "SELECT *
                 FROM {$this->commentTable}
                 WHERE $cond
                 ORDER BY comment_created
                 $limit"
            );
            $this->countLastList = null;
        }
		return $comments;
	}

    /**
     * Get count by last list.
     *
     * @return int|null
     *
     * @note Result of <tt>SELECT FOUND_ROWS()</tt> after @c getListByIds() with limit.
     *
     * @see Comments::getListByIds()
     */
    public function getCountByLastList(){
        return $this->countLastList;
    }

    public function getPageCount(array $targetIDs, $recordsPerPage){
        return ($result = (int) simplifyDBResult(
            $this->dbh->select($this->commentTable,
                    'CEIL(COUNT(*)/' . $recordsPerPage .
                            ') as c ', array('target_id' => $targetIDs)),
            'c',
            true
        ))?$result:1;
    }

	/**
     * Get amount of comments by IDs.
	 *
	 * @param int|array $targetIds ID(s).
	 * @param bool $singleRow Return only the first result?
	 * @return int|int[]
     *
     * @see Comments::getCountByLastList()
	 */
	public function getCountByIds(array $targetIds, $singleRow = false){
		if(!$targetIds){
			return $singleRow ? 0 : array(0);
		}
		if(is_array($targetIds)){
			$ids = implode(',', $targetIds);
			$cond = "target_id IN($ids)"; 
		}
		else{
			$cond = 'target_id = '. intval($targetIds);
		}

		$count = $this->dbh->select(
			"SELECT target_id, COUNT(*) as count 
			 FROM {$this->commentTable}
			 WHERE $cond
			 GROUP BY target_id"
		);

		$result = array();
		if($singleRow){
			$result = intval($count[0]['count']);
		}
		elseif(is_array($count)){
			foreach($count as $item){
				list($targetId, $num) = array_values($item);
				$result[intval($targetId)] = $num;
			}
		}
		return $result;
	}
	
	/**
     * Save/add comment.
	 *
	 * Input argument looks like:
     * @code
[int comment_id,] int target_id, int u_id, string comment_name, [string|int comment_created,] [int comment_parent_id,] [bool comment_approved]
@endcode
	 * 
	 * @param array $comment
	 * @return int|bool
	 */
    // поле comment_created - либо строка 'Y-m-d H:i:s' либо timestamp
	public function saveItem(array $comment){
		if(isset($comment['comment_id'])){
			$mode = QAL::UPDATE;
			$cond = array('comment_id' => $comment['comment_id']);
			
			if(is_int($comment['comment_created'])){// пришёл timestamp
				$comment['comment_created'] = date('Y-m-d H:i:s', $comment['comment_created']);
			}
		}
		else{
			$mode = QAL::INSERT;
			$cond = null;
			
			if(!isset($comment['comment_created']) or !$comment['comment_created']){
				$comment['comment_created'] = date('Y-m-d H:i:s'); 
			}
		}	

		if(!isset($comment['comment_parent_id']) or !intval($comment['comment_parent_id'])){
			// @see QAL::modify() (transform null=>'' and ''=>null)
			$comment['comment_parent_id'] = '';
		}
		
		if(isset($comment['comment_approved']) && $comment['comment_approved']){
			$comment['comment_approved'] = 1;
		}
		else{ // @see QAL::modify() (transform (''|0)=>null)
			$comment['comment_approved'] = '0';
		}
		
		return $this->dbh->modify($mode, $this->commentTable, $comment, $cond);
	}
	
	/**
     * Insert comment.
	 *
	 * @param int $targetId Target ID.
	 * @param int $userId User ID.
	 * @param string $name Comment name.
	 * @param string $created Comment time.
	 * @param int $parentId Parent ID.
	 * @param bool $approved Approved?
	 * @return int|bool
	 */
	public function insertItem($targetId, $userId, $name, $created='', $parentId=null, $approved=false){
		$item = array(
			'target_id' => (int)$targetId,
			'u_id'	=> (int)$userId,
			'comment_name'		=> $name,
			'comment_created'	=> $created,
			'comment_parent_id' => $parentId,
			'comment_approved'  => $approved
		);
		return $this->saveItem($item);
	}
	
	/**
     * Delete comment(s).
	 *
	 * @param int|array $commentIds Comment ID(s).
	 * @return bool
	 */
	public function deleteItem($commentIds){
		if(!is_array($commentIds)){
			$commentIds = array($commentIds);
		}
		// если комментарии древовидные - то изменяем ссылки в потомках на родителя удаляемого элемента
		if($this->isTree){
			$parentIds = $this->dbh->select(
				$this->commentTable, 
				array('comment_id', 'comment_parent_id'),
				array('comment_id' => $commentIds)
			);
			// если нашлись потомки то меняем им ссылку на родителя
			if(is_array($parentIds)){
				foreach($parentIds as $pair){
					$baseParentId = (int)array_pop($pair);
					$baseParentId = $baseParentId ? $baseParentId : ''; // @see QAL::modify() (transform (''|0)=>null)
					if($baseParentId){ // если NULL то можно не удалять - сработает каскадное обнуление
						$commentId = (int)array_pop($pair);
						$this->dbh->modify(QAL::UPDATE, $this->commentTable, 
							array('comment_parent_id' => $baseParentId),
							array('comment_parent_id' => $commentId)
						);
					}
				}
			}
		}
		return $this->dbh->modify(QAL::DELETE, $this->commentTable, null, array('comment_id' => $commentIds));
	}
}

