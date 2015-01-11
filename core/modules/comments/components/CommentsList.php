<?php
/**
 * @file
 * CommentsList
 *
 * It contains the definition to:
 * @code
class CommentsList;
@endcode
 *
 * @author sign
 *
 * @version 1.0.0
 */
namespace Energine\comments\components;
use Energine\comments\gears\Comments;
use Energine\share\components\DataSet, Energine\share\gears\Pager, Energine\share\gears\TreeBuilder, Energine\share\gears\TreeConverter, Energine\share\gears\FieldDescription, Energine\share\gears\DataDescription;
/**
 * List of comments.
 *
 * @code
class CommentsList;
@endcode
 *
 * Usage example:
 * @code
$commentsParams = array(
    'table_name' => $this->getTableName(),
    'is_tree' => false,
    'target_ids' => $this->getData()->getFieldByName('news_id')->getData()
);
$commentsList = $this->document->componentManager->createComponent('commentsList', 'comments', 'CommentsList', $commentsParams);
$commentsList->run();
$this->document->componentManager->addComponent($commentsList);
@endcode
 */
class CommentsList extends DataSet {
    /**
     * Are the comments tree-like?
     * @var bool $isTree
     */
    protected $isTree = false;

    /**
     * Comment ID(s).
     * @var int|array $targetIds
     */
    protected $targetIds = array();

    /**
     * Loaded data.
     * @var array $loadedData
     */
    private $loadedData = null;

    /**
     * Comments.
     * @var Comments $comments
     */
    protected $comments = null;

    //todo VZ: remove this?
    /**
     * Comment field name.
     * @var string $commentsFieldName
     */
    private $commentsFieldName = '';
    

    /**
     * Component to which this list is bound.
     * @var Component $bindComponent
     */
    private $bindComponent = null;

    /**
     * @copydoc DataSet::__construct
     */
    /*
     * В $param ожидаются поля
     * table_name string - имя комментируемой таблицы
     * target_ids array - айдишники комментируемых сущностей
     * is_tree bool - комментарии древовидные?
     */
    public function __construct($name, $module, array $params = null) {
        parent::__construct($name, $module, $params);
        $this->setProperty('exttype', 'comments');
        $this->setType(self::COMPONENT_TYPE_LIST);

        $commentTable = $this->dbh->tableExists(
            $this->getParam('table_name') . '_comment');

        $this->isTree = $this->getParam('is_tree');

        $this->setProperty('is_tree', $this->isTree);

        $right = $this->document->getRights();
        $this->setProperty('is_editable', (int) (
                $right > 1)); // добавлять и править/удалять своё
        $this->setProperty('is_admin', (int) ($right > 2)); // godmode
        if (E()->getAUser()->isAuthenticated()) {
            $this->document->setProperty('CURRENT_UID', E()->getAUser()->getID());
        }

        $this->targetIds = $this->getParam('target_ids');

        $this->comments = new Comments($commentTable, $this->isTree);
    }

    /**
     * @copydoc DataSet::createDataDescription
     */
    protected function createDataDescription() {
        $dataDescription = new DataDescription();

        $fd = new FieldDescription('comment_id');
        $fd->setType(FieldDescription::FIELD_TYPE_INT);
        $fd->setProperty('key', true); // для построения дерева
        $dataDescription->addFieldDescription($fd);

        // если у нас древовидная структура - добавляем предка
        if ($this->isTree) {
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

        return $dataDescription;
    }

    /**
     * @copydoc DataSet::createBuilder
     */
    // Если комментарии не древовидные или отсутствуют возвращается Builder иначе - TreeBuilder
    protected function createBuilder() {
        if ($this->isTree and is_array($data = $this->loadData())) {
            $builder = new TreeBuilder();
            $tree =
                    TreeConverter::convert($data, 'comment_id', 'comment_parent_id');
            $builder->setTree($tree);
        }
        else {
            $builder = parent::createBuilder();
        }
        $this->builder = $builder;
        return $this->builder;
    }

    /**
     * @copydoc DataSet::createPager
     */
    // Создаём педжер только один раз
    protected function createPager() {
        if (!$this->pager) {
            $recordsPerPage = intval($this->getParam('recordsPerPage'));
            if ($recordsPerPage > 0) {
                $this->pager = new Pager($recordsPerPage);
                if ($this->isActive() &&
                        $this->getType() == self::COMPONENT_TYPE_LIST) {
                    $actionParams = $this->getStateParams(true);
                    if (
                            !isset($actionParams['pageNumber'])
                            ||
                            (
                                !($page = intval($actionParams['pageNumber']))
                                &&
                                ($actionParams['pageNumber'] !== 'last')
                            )
                    ) {
                        $page = 1;
                    }
                    elseif($actionParams['pageNumber'] === 'last'){
                        $page = $this->comments->getPageCount($this->targetIds, $recordsPerPage);    
                    }
                    $this->pager->setCurrentPage($page);
                }

                $this->pager->setProperty('title', $this->translate('TXT_PAGES'));
            }
        }
    }

    /**
     * @copydoc DataSet::loadData
     */
    // Загружаем комментарии и информацию о пользователях
    protected function loadData() {
        if (is_null($this->loadedData)) {
            $this->createPager();

            $limitArr = null;
            if (!$this->isTree and $this->pager) {
                // pager существует -- загружаем только часть данных, текущую страницу
                $limitArr = $this->pager->getLimit();
            }
            $this->loadedData =
                    $this->comments->getListByIds($this->targetIds, $limitArr);
            if ($this->pager and $limitArr) {
                $this->setType(self::COMPONENT_TYPE_LIST);
                $this->pager->setRecordsCount($this->comments->getCountByLastList());
            }
            $this->loadedData = $this->addUsersInfo($this->loadedData);

            $this->setProperty('comment_count', is_array($this->loadedData) ? count($this->loadedData) : 0);
        }
        return $this->loadedData;
    }


    /**
     * @copydoc DataSet::defineParams
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
     * Add information about users.
     *
     * @param array $data Data.
     * @return array
     */
    private function addUsersInfo($data) {
        if ($data && is_array($data)) {
            $usersInfo = $this->getUsersByComments($data);
            $usersInfo = convertDBResult($usersInfo, 'u_id');

            foreach ($data as &$item) {
                if ($item['u_id']) {
                    $user = $usersInfo[$item['u_id']];
                    $item['u_nick'] =
                            trim($user['u_nick']) ? trim($user['u_nick']) : $user['u_fullname'];
                } else {
                    $item['u_nick'] = $item['comment_nick'];
                }
            }
        }
        return $data;
    }

    //todo VZ: same function name in CommentsHelper
    /**
     * Get users who left comments.
     * Информация о юзерах оставивших комментарии
     *
     * @param array $data
     * @return array
     */
    private function getUsersByComments($data) {
        $result = array();

        if ($data && is_array($data)) {
            $userIds = array();
            foreach ($data as $item) {
                if ($item['u_id']) {
                    $userIds[] = $item['u_id'];
                }
            }
            $userIds = array_unique($userIds);

            if ($userIds) {
                $userIds = implode(',', $userIds);
                $result = $this->dbh->selectRequest(
                    'SELECT u.* ' .
                            " FROM user_users u
					 WHERE u.u_id in($userIds)"
                );
            }
        }
        return $result;
    }

    /**
     * @copydoc DataSet::main
     */
    // Если задан связаный компонент (параметер bind) то добавляем ему поле comments (имя задаётся параметром commentsFieldName)
    protected function main() {
        parent::main();

        if ($this->getParam('bind')) {
            $this->bindComponent =
                    $this->document->componentManager->getBlockByName($this->getParam('bind'));

            if (!$this->isTree and $this->pager) {
                $ap = $this->bindComponent->getStateParams(true);
                $subUrl = '';
                if (isset($ap['themeID'])) {
                    $subUrl = $ap['themeID'] . '/';
                }
                $this->pager->setProperty('additional_url', $subUrl);
            }
        }
    }
}

