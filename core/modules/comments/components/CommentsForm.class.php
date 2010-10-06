<?php
/**
 * Содержит класс CommentsForm
 *
 * @package energine
 * @subpackage comments
 * @author sign
 */

/**
 * Вывод комментариев и формы комментирования
 *
 * Пример использования в *.content.xml
 * <component name="commentsForm" module="comments" class="CommentsForm">
<params>
<param name="bind">newsArchive</param>
<param name="comment_tables">stb_news_comment</param>
<param name="show_comments">1</param>
<param name="show_form">1</param>
</params>
</component>
 *
 * @package energine
 * @subpackage comments
 * @author sign
 */
class CommentsForm extends DataSet {
    /**
     * связанный компонент
     *
     * @access private
     * @var DBDataSet|boolean
     */
    private $bindComponent;

    /**
     * Таблица с комментариями - должна быть задана как параметер компонента
     * @var string
     */
    private $commentTable = '';

    /**
     * Комментируемая таблица
     * @var string
     */
    private $targetTable = '';

    /**
     * Комментарии древовидные? определяется параметром is_tree
     * @var bool
     */
    private $isTree = false;

    /**
     * @var bool
     */
    private $isExistsTables = null;

    /**
     * Конструктор класса
     *
     * @param string $name
     * @param string $module
     * @param Document $document
     * @param array $params
     * @access public
     */
    public function __construct($name, $module, Document $document, array $params = null) {
        // если комментарии скрыты то бессмысленно показывать форму
        if (!isset($params['show_comments']) or !$params['show_comments']) {
            $params['show_form'] = 0;
        }
        parent::__construct($name, $module, $document, $params);

        $this->commentTable = $this->getParam('comment_tables');
        $this->targetTable =
                substr($this->commentTable, 0, strrpos($this->commentTable, '_'));

        $this->bindComponent =
                $this->document->componentManager->getBlockByName($this->getParam('bind'));
        $this->isTree = $this->getParam('is_tree');
        $this->setProperty('limit', $this->getParam('textLimit'));
    }

    /**
     * Существуют ли комментируемая таблица и таблица комментариев
     * @return bool
     */
    protected function isExistsNeedTables() {
        if (is_null($this->isExistsTables)) {
            $this->isExistsTables =
                    (bool) $this->dbh->tableExists($this->commentTable) &&
                            (bool) $this->dbh->tableExists($this->targetTable);
        }
        return $this->isExistsTables;
    }

    /**
     * Сохраняем комментарий и отдаём json
     *  отключается параметром компонента show_form
     *
     * Только для авторизованных пользователей
     */
    protected function saveComment() {
        try {
            if (!$this->getParam('show_form')) {
                throw new Exception('Adding comments has been disabled');
            }

            if (!$this->document->user->isAuthenticated()) {
                throw new Exception('Add comment can auth user only');
            }

            if (!isset($_POST['target_id']) or !($targetId = (int) $_POST['target_id']))
                throw new Exception('Mistake targetId');

            if(!$this->isTargetEditable()){
                throw new SystemException('read only');
            }

            if (isset($_POST['comment_name']) and $commentName = trim($_POST['comment_name'])) {
                if ($this->isTree and isset($_POST['parent_id'])) {
                    $parentId = intval($_POST['parent_id']);
                }
                else $parentId = 0;

                $commentName = $this->clearPost($commentName);

                if(isset($_POST['comment_id']) and $commentId = intval($_POST['comment_id'])){
                    // отредактированный коммент
                    if(!$isUpdated = $this->updateComment($targetId, $commentName, $commentId))
                        throw new Exception('Save error');
                }
                else{
                    // новый коммент
                    $comment = $this->addComment($targetId, $commentName, $parentId);
                }
            }
            else {
                throw new Exception('Comment is empty');
            }

            if(!empty($isUpdated)){
                $result = json_encode(array(
                    'result'=>true,
                    'mode' => 'update',
                    'data' => array('comment_name' => $commentName))
                );   
            }
            else{
                $result = $this->buildResult(array($comment));
            }
        }
        catch (SystemException $e) {
            $message['errors'][] = array('message' =>
            $e->getMessage() . current($e->getCustomMessage()));
            $result =
                    json_encode(array_merge(array('result' => false, 'header' => $this->translate('TXT_SHIT_HAPPENS')), $message));
        }

        $this->response->setHeader('Content-Type', 'text/javascript; charset=utf-8');
        $this->response->write($result);
        $this->response->commit();
    }

    /**
     * Чистим переимущетвенно текстовый ввод пользователя
     *
     * @param  string $s
     * @return string
     */
    protected function clearPost($s){
        $allowTags = implode(array('<br><b><strong><em><i><div><ul><ol><li><a>'));
        $s = strip_tags($s, $allowTags);
        $s = nl2br($s);
        // все ньюлайн уже преобразованы, убираем случайные что бы они не помешали нам парсить ссылки
        $s = str_replace(array("\n", "\r"), array('', ''), $s);
        
        $s = preg_replace_callback('|<a\s+(.*)>(.*)</a>|i',
            function($matches){
                $m = array();
                if(!strlen(trim($matches[2])) or !preg_match('%href\s*=\s*(?:"|\')(.*)(?:"|\')%i', $matches[1], $m))
                    return '';
                return '<a href="'. $m[1]. '">'. $matches[2]. '</a>';
            },
            $s
        );
        return $s;
    }

    /**
     * Права на текущий раздел текущего пользователя больше чем "Read only"
     * @return bool
     */
    protected function isTargetEditable(){
        if(!AuthUser::getInstance()->isAuthenticated())
            return false;
        $right = Sitemap::getInstance()->getDocumentRights($this->document->getID());
        return $right > ACCESS_READ;
    }

    protected function updateComment($targetId, $commentName, $commentId){
        if (!in_array('1', AuthUser::getInstance()->getGroups())) {
            if(!$this->isTargetEditable()){ // юзеру доступно только чтение
                return false;
            }
            // если не админ -  проверяем авторство
            $comments = $this->dbh->select($this->commentTable, true, array('comment_id' => $commentId));
            if(!$comments){ // удалён
                return false;
            }
            $comment = $comments[0];
            if(AuthUser::getInstance()->getID() != $comment['u_id']){
                // не автор - запретить!
                return false;
            }
        }
        return $this->dbh->modify(QAL::UPDATE, $this->commentTable,
            array('comment_name' => $commentName),
            array('comment_id' => $commentId)
        );
    }

    protected function deleteComment() {
        try {
            if (!$this->getParam('show_form')) {
                throw new Exception('Adding comments has been disabled');
            }

            if (!$this->document->user->isAuthenticated()) {
                throw new Exception('Add comment can auth user only');
            }

            if (!isset($_POST['comment_id']) or !($commentId = (int) $_POST['comment_id']))
                throw new Exception('Mistake arg');

            $result = json_encode(array(
                'mode' => 'delete',
                'result' => $this->removeComment($commentId)
            ));
        }
        catch (SystemException $e) {
            $message['errors'][] = array('message' =>
            $e->getMessage() . current($e->getCustomMessage()));
            $result = json_encode(array_merge(
                array('result' => false, 'header' => $this->translate('TXT_SHIT_HAPPENS')),
                $message)
            );
        }

        $this->response->setHeader('Content-Type', 'text/javascript; charset=utf-8');
        $this->response->write($result);
        $this->response->commit();
    }

    /**
     * Удалить комментарий
     *
     * @param  int $id
     * @return bool
     */
    private function removeComment($id){
        if (!in_array('1', AuthUser::getInstance()->getGroups())) {
            if(!$this->isTargetEditable()){ // юзеру доступно только чтение
                return false;
            }
            // если не админ -  проверяем авторство
            $comments = $this->dbh->select($this->commentTable, true, array('comment_id' => $id));
            if(!$comments){ // уже удалён
                return true;
            }
            $comment = $comments[0];
            if(AuthUser::getInstance()->getID() != $comment['u_id']){
                // не автор - запретить!
                return false;
            }
        }

        return $this->dbh->modify(QAL::DELETE, $this->commentTable, null, array('comment_id' => $id));
    }

    /**
     * Добавляем обязательный параметер comment_tables - имя таблицы с комментариями
     *
     * @return array
     */
    protected function defineParams() {
        $result = array_merge(parent::defineParams(), array(
            'comment_tables' => '',
            'active' => true,
            'is_tree' => 0,
            'bind' => false,
            'show_comments' => false,
            'show_form' => false,
            'textLimit' => 250
        ));
        return $result;
    }

    /**
     * При построении формы назначаем ID комментируемого элемента
     *
     */
    protected function prepare() {
        if($this->getAction() == 'deleteComment'){
            ;;
        }
        else{
            if (($this->bindComponent &&
                    $this->bindComponent->getAction() == 'view') &&
                    ($this->getAction() == 'main')
                    && $this->getParam('show_form') &&
                    $this->getParam('show_comments')
                    && $this->isExistsNeedTables()) {
                parent::prepare();

                //ID комментируемого элемента
                $ap = $this->bindComponent->getActionParams(true);
                //Тут костыль
                $apk = array_keys($ap);
                $apName = $apk[sizeof($apk)-1];
                if($apName == 'pageNumber'){
                    $apName = $apk[sizeof($apk)-2];
                }
                $targetId = $ap[$apName];

                if($this->isTargetEditable()){
                    $this->getDataDescription()->getFieldDescriptionByName('target_id')->setType(FieldDescription::FIELD_TYPE_HIDDEN);
                    
                    $f = new Field('target_id');
                    $f->setData($targetId);
                    $this->getData()->addField($f);

                    // добавляем переводы для формы

                    $this->addTranslation('COMMENT_DO'); // коментировать
                    $this->addTranslation('COMMENT_DO_NEWS'); // коментировать новость
                    $this->addTranslation('COMMENT_REMAIN'); // осталось
                    $this->addTranslation('COMMENT_SYMBOL1'); // символ
                    $this->addTranslation('COMMENT_SYMBOL2'); // символа
                    $this->addTranslation('COMMENT_SYMBOL3'); // символов
                    $this->addTranslation('COMMENT_REALY_REMOVE'); // Действительно удалить комментарий?
                }
                else{
                    // форма нужна только для вывода списка комментариев
                    $this->setProperty('hide_form', 1);
                }
                $this->addTranslation('COMMENTS'); // коментирии
            }
            else {
                $this->disable();
            }
    
            if ($this->getParam('show_comments') && $this->isExistsNeedTables() &&
                    is_object($this->bindComponent) &&
                    $this->bindComponent->getAction() == 'view'
                    && $this->bindComponent->getData() &&
                    !$this->bindComponent->getData()->isEmpty()) {
                $this->showComments();
            }
        }
    }

    /**
     * Add to DB
     *
     * Возвращает комментарий в виде массива добавив к нему два поля (u_nick иu_avatar_img)
     * с информацией о юзере
     *
     * @param int $targetId       Комментируемая запись
     * @param string $commentName Комментарий
     * @param int $parentId       Родительский комментарий
     * @return array
     */
    private function addComment($targetId, $commentName, $parentId = null) {
        $uId = $this->document->user->getID();

        $userInfo = $this->getUserInfo($uId);
        $userName = array_shift($userInfo);
        $userAvatar = array_shift($userInfo);
        $userSex = array_shift($userInfo);
        if (is_bool($userSex)) {
            $userSex = $this->translate($userSex ? 'TXT_MALE' : 'TXT_FEMALE');
        }
        else $userSex = $this->translate('TXT_UNKNOWN');

        $userPlace = array_shift($userInfo);

        $created = time(); // для JSONBuilder
        $createdStr = date('Y-m-d H:i:s', $created); // для запроса

        $parentIdSql = intval($parentId) ? intval($parentId) : 'NULL';
        $commentId = $this->dbh->modifyRequest("INSERT {$this->commentTable} 
        	SET target_id = %s,
        		comment_parent_id = $parentIdSql, 
        		comment_name = %s,
        		u_id = %s,
        		comment_created = %s,
        		comment_approved = 0",
            $targetId, $commentName, $uId, $createdStr
        );
        return array(
            'is_tree' => (int) $this->isTree, // для отрисовки или не отрисовки ссылки "коментировать" в js
            'comment_id' => $commentId,
            'comment_parent_id' => $parentId,
            'target_id' => $targetId,
            'u_id' => $uId,
            'comment_created' => $created,
            'comment_name' => $commentName,
            'comment_approved' => 0,
            'u_nick' => $userName,
            'u_avatar_img' => $userAvatar,
            'u_sex' => $userSex,
            'u_place' => $userPlace
        );
    }

    /**
     * Имя и аватар юзера
     *
     * Возврвщает массив с полями 'u_nick','u_avatar_img'
     *
     * @param int $uId
     * @return array string[]
     */
    private function getUserInfo($uId) {
        $result = array('u_nick' => '', 'u_avatar_img' => '');
        $userInfo = $this->dbh->select('user_users',
            array('u_nick', 'u_avatar_img', 'u_is_male, u_place','u_fullname'),
            array('u_id' => $uId),
            null, array(1)
        );
        if ($userInfo) {
            $result = $userInfo[0];
            if (!$result['u_avatar_img']) {
                $result['u_avatar_img'] =
                        $this->getNotExistsAvatar($uId, $result['u_is_male']);
            }
            if(!$result['u_nick']){
                $result['u_nick'] = $result['u_fullname'];
            }
            unset($result['u_fullname']);
        }
        return $result;
    }

    /**
     * Путь к несуществующей аватарке
     * @param  int $uid
     * @param  boolean $sex
     * @return string
     *
     * @see CommentsList::getNotExistsAvatar()
     */
    private function getNotExistsAvatar($uid, $sex = null) {
        $addDir = '';
        if (!is_null($sex)) {
            $addDir = intval((bool) $sex) . '/';
        }
        return 'uploads/avatars/auto/' . $addDir . md5($uid) . '.jpg';
    }

    /**
     * Билдим результаты как JSON
     *
     * @param array $comment
     * @return mixed
     */
    private function buildResult($comment) {
        $builder = new JSONBuilder();

        $dataDescription = new DataDescription();
        $localData = new Data();
        $localData->load($comment);

        $fd = new FieldDescription('comment_id');
        $fd->setType(FieldDescription::FIELD_TYPE_INT);
        $dataDescription->addFieldDescription($fd);

        if ($this->isTree) {
            $fd = new FieldDescription('is_tree');
            $fd->setType(FieldDescription::FIELD_TYPE_BOOL);
            $dataDescription->addFieldDescription($fd);

            if (isset($comment[0]['comment_parent_id'])) {
                $fd = new FieldDescription('comment_parent_id');
                $fd->setType(FieldDescription::FIELD_TYPE_INT);
                $dataDescription->addFieldDescription($fd);
            }
        }

        $fd = new FieldDescription('target_id');
        $fd->setType(FieldDescription::FIELD_TYPE_INT);
        $dataDescription->addFieldDescription($fd);

        $fd = new FieldDescription('comment_name');
        $fd->setType(FieldDescription::FIELD_TYPE_STRING);
        $dataDescription->addFieldDescription($fd);

        $fd = new FieldDescription('u_id');
        $fd->setType(FieldDescription::FIELD_TYPE_INT);
        $dataDescription->addFieldDescription($fd);

        $fd = new FieldDescription('comment_created');
        $fd->setType(FieldDescription::FIELD_TYPE_DATETIME);
        $fd->setProperty('outputFormat', '%E');
        $dataDescription->addFieldDescription($fd);

        $fd = new FieldDescription('comment_approved');
        $fd->setType(FieldDescription::FIELD_TYPE_BOOL);
        $dataDescription->addFieldDescription($fd);

        $builder->setData($localData);
        $builder->setDataDescription($dataDescription);

        //добавляем поля о прокоментировавшем
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

        if ($builder->build()) {
            $result = $builder->getResult();
        }
        else {
            $result = $builder->getErrors();
        }

        return $result;
    }

    /**
     * Показываем комментарии
     * @return void
     */
    protected function showComments() {
        $priFieldName = $this->bindComponent->getPK();

        $commentsParams = array(
            'active'=>true,
            'table_name' => $this->targetTable,
            'is_tree' => $this->getParam('is_tree'),
            'bind' => $this->getParam('bind'),
            'recordsPerPage' => $this->getParam('recordsPerPage'),
            'target_ids' => $this->bindComponent->getData()->getFieldByName($priFieldName)->getData()
        );

        $this->setProperty('bind', $this->getParam('bind'));

        /** @var $commentsList CommentsList */
        $commentsList =
                $this->document->componentManager->createComponent('commentsList', 'comments', 'CommentsList', $commentsParams);

        $this->document->componentManager->addComponent($commentsList);

        if ($this->getParam('bind') && $forumTheme =
                $this->document->componentManager->getBlockByName($this->getParam('bind'))) {
            $ap = $forumTheme->getActionParams(true);
            if (isset($ap['pageNumber'])) {
                $commentsList->addActionParam('pageNumber', $ap['pageNumber']);
            }
        }
        $commentsList->run();
    }
}
