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

    private $userEditor;

    private $banIPEditor;
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

     * @param array $params
     * @access public
     */
    public function __construct($name, $module, array $params = null) {
        // если комментарии скрыты то бессмысленно показывать форму
        if (!isset($params['show_comments']) or !$params['show_comments']) {
            $params['show_form'] = 0;
        }
        parent::__construct($name, $module, $params);

        $this->commentTable = $this->getParam('comment_tables');
        $this->targetTable =
                substr($this->commentTable, 0, strrpos($this->commentTable, '_'));

        $this->bindComponent =
                $this->document->componentManager->getBlockByName($this->getParam('bind'));
        $this->isTree = $this->getParam('is_tree');
        $this->setProperty('limit', $this->getParam('textLimit'));

        $this->setProperty('is_anonymous', (string) !$this->document->user->isAuthenticated());

        $this->addTranslation('TXT_ENTER_CAPTCHA');
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
        $builder = new JSONCustomBuilder();
        $this->setBuilder($builder);

        try {
            if (!$this->getParam('show_form')) {
                throw new Exception('Adding comments has been disabled');
            }

            if (!$this->getParam('allows_anonymous') && !$this->document->user->isAuthenticated()) {
                throw new Exception('Add comment can auth user only');
            }

            if (!isset($_POST['target_id']) or
                    !($targetId = (int) $_POST['target_id']))
                throw new Exception('Mistake targetId');

            if (!$this->isTargetEditable()) {
                throw new SystemException('read only');
            }

            if (!$this->document->getUser()->isAuthenticated() && empty($_POST['comment_nick'])) {
                throw new SystemException('TXT_COMMENT_NICK_IS_REQUIRED');
            }

            if (!$this->document->getUser()->isAuthenticated()) {
                $this->checkCaptcha();
            }

            if (isset($_POST['comment_name']) and
                    $commentName = trim($_POST['comment_name'])) {
                if ($this->isTree and isset($_POST['parent_id'])) {
                    $parentId = intval($_POST['parent_id']);
                }
                else $parentId = 0;

                $commentName = $this->clearPost($commentName);
                $commentNick = $this->clearPost((isset($_POST['comment_nick'])) ? $_POST['comment_nick'] : '');

                if (isset($_POST['comment_id']) and
                        $commentId = intval($_POST['comment_id'])) {
                    // отредактированный коммент
                    if (!$isUpdated =
                            $this->updateComment($targetId, $commentName, $commentNick, $commentId))
                        throw new Exception('Save error');
                }
                else {
                    // новый коммент
                    $comment =
                            $this->addComment($targetId, $commentName, $commentNick, $parentId);
                }
            }
            else {
                throw new SystemException('Comment is empty');
            }

            if (!empty($isUpdated)) {
                $builder->setProperties(array(
                    'result' => true,
                    'mode' => 'update',
                    'data' => array(
                        'comment_name' => $commentName,
                        'comment_nick' => $commentNick,
                        'comment_id' => $commentId
                    )
                ));
            }
            else {
                $this->setBuilder($this->buildResult(array($comment)));
            }
        }
        catch (SystemException $e) {
            $message['errors'][] = array('message' =>
            $e->getMessage() . current($e->getCustomMessage()));
            $builder->setProperties(
                array_merge(array('result' => false, 'header' => $this->translate('TXT_SHIT_HAPPENS')), $message));
        }
    }

    /**
     * Чистим переимущетвенно текстовый ввод пользователя
     *
     * @param  string $s
     * @return string
     */
    protected function clearPost($s) {
        return strip_tags($s);
    }

    /**
     * Права на текущий раздел текущего пользователя больше чем "Read only"
     * @return bool
     */
    protected function isTargetEditable() {
        if (!$this->getParam('allows_anonymous') && !E()->getAUser()->isAuthenticated())
            return false;
        $right =
                E()->getMap()->getDocumentRights($this->document->getID());
        return $right >= ACCESS_READ;
    }

    private function updateComment($targetId, $commentName, $commentNick, $commentId) {
        if (!in_array('1', E()->getAUser()->getGroups())) {
            if (!$this->isTargetEditable()) { // юзеру доступно только чтение
                return false;
            }
            // если не админ -  проверяем авторство
            $comments =
                    $this->dbh->select($this->commentTable, true, array('comment_id' => $commentId));
            if (!$comments) { // удалён
                return false;
            }
            $comment = $comments[0];
            if (E()->getAUser()->getID() != $comment['u_id']) {
                // не автор - запретить!
                return false;
            }
        }
        return $this->dbh->modify(QAL::UPDATE, $this->commentTable,
            array(
                'comment_name' => $commentName,
                'comment_nick' => $commentNick,
            ),
            array('comment_id' => $commentId)
        );
    }

    protected function deleteComment() {
        $builder = new JSONCustomBuilder();
        $this->setBuilder($builder);

        try {
            if (!$this->getParam('show_form')) {
                throw new Exception('Adding comments has been disabled');
            }

            if (!$this->document->user->isAuthenticated()) {
                throw new Exception('Add comment can auth user only');
            }

            if (!isset($_POST['comment_id']) or
                    !($commentId = (int) $_POST['comment_id']))
                throw new Exception('Mistake arg');
            $builder->setProperties(
                array(
                    'mode' => 'delete',
                    'result' => $this->removeComment($commentId)
                )
            );
        }
        catch (SystemException $e) {
            $message['errors'][] = array('message' =>
            $e->getMessage() . current($e->getCustomMessage()));
            $builder->setProperties(
                array_merge(
                    array('result' => false, 'header' => $this->translate('TXT_SHIT_HAPPENS')),
                    $message)
            );
        }
    }

    /**
     * Удалить комментарий
     *
     * @param  int $id
     * @return bool
     */
    private function removeComment($id) {
        if (!in_array('1', E()->getAUser()->getGroups())) {
            if (!$this->isTargetEditable()) { // юзеру доступно только чтение
                return false;
            }
            // если не админ -  проверяем авторство
            $comments =
                    $this->dbh->select($this->commentTable, true, array('comment_id' => $id));
            if (!$comments) { // уже удалён
                return true;
            }
            $comment = $comments[0];
            if (!E()->getAUser() || E()->getAUser()->getID() != $comment['u_id']) {
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
            'bind_state' => 'view',
            'bind_pk_param_idx' => 0,
            'show_comments' => false,
            'show_form' => false,
            'textLimit' => 250,
            'allows_anonymous' => true,
        ));
        return $result;
    }

    /**
     * При построении формы назначаем ID комментируемого элемента
     *
     */
    protected function prepare() {
        if ($this->getState() == 'deleteComment') {
            ;
            ;
        }
        else {
            if (($this->bindComponent &&
                    $this->bindComponent->getState() == $this->getParam('bind_state')) &&
                    ($this->getState() == 'main')
                    && $this->getParam('show_form') &&
                    $this->getParam('show_comments')
                    && $this->isExistsNeedTables()) {
                parent::prepare();

                if (
                    $this->document->getUser()->isAuthenticated()
                    &&
                    ($captcha =
                        $this->getDataDescription()->getFieldDescriptionByName('captcha'))
                ) {
                    $this->getDataDescription()->removeFieldDescription($captcha);
                }

                //ID комментируемого элемента
                $ap = $this->bindComponent->getStateParams(true);
                //Тут костыль
                if (is_array($ap)) {
                    $apk = array_keys($ap);
                    $param_idx = $this->getParam('bind_pk_param_idx');
                    $apName = (isset($apk[$param_idx])) ? $apk[$param_idx] : $apk[sizeof($apk) - 1];
                    $targetId = $ap[$apName];
                } else {
                    $targetId = $this->document->getID();
                }
                if ($this->isTargetEditable()) {
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
                else {
                    // форма нужна только для вывода списка комментариев
                    $this->setProperty('hide_form', 1);
                }
                $this->addTranslation('COMMENTS'); // коментирии
            }
            else {
                $this->disable();
            }

            if ($this->getParam('show_comments') &&
                    $this->isExistsNeedTables() &&
                    is_object($this->bindComponent) &&
                    $this->bindComponent->getState() == $this->getParam('bind_state')
                    && $this->bindComponent->getData() &&
                    !$this->bindComponent->getData()->isEmpty()) {
                $this->showComments();
            }
        }
    }

    /**
     * Add to DB
     *
     * Возвращает комментарий в виде массива добавив к нему поле (u_nick)
     * с информацией о юзере
     *
     * @param int $targetId       Комментируемая запись
     * @param string $commentName Комментарий
     * @param int $parentId       Родительский комментарий
     * @return array
     */
    private function addComment($targetId, $commentName, $commentNick, $parentId = null) {

        if ($this->document->user && $this->document->user->getID()) {
            $uId = $this->document->user->getID();
            $userInfo = $this->getUserInfo($uId);
            $userName = array_shift($userInfo);
        } else {
            $uId = null;
            $userName = $commentNick;
        }

        $created = time(); // для JSONBuilder
        $createdStr = date('Y-m-d H:i:s', $created); // для запроса

        $parentIdSql = intval($parentId) ? intval($parentId) : 'NULL';
        $uIdSql = intval($uId) ? intval($uId) : 'NULL';

        $commentId = $this->dbh->modifyRequest("INSERT {$this->commentTable}
        	SET target_id = %s,
        		comment_parent_id = $parentIdSql, 
        		comment_name = %s,
        		comment_nick = %s,
        		u_id = $uIdSql,
        		comment_created = %s,
        		comment_approved = 0",
            $targetId, $commentName, $commentNick, $createdStr
        );

        return array(
            'is_tree' => (int) $this->isTree, // для отрисовки или не отрисовки ссылки "коментировать" в js
            'comment_id' => $commentId,
            'comment_parent_id' => $parentId,
            'target_id' => $targetId,
            'u_id' => $uId,
            'comment_created' => $createdStr,
            'comment_name' => $commentName,
            'comment_approved' => 0,
            'u_nick' => $userName
        );
    }

    /**
     * Имя и аватар юзера
     *
     * Возврвщает массив с полями 'u_nick'
     *
     * @param int $uId
     * @return array string[]
     */
    private function getUserInfo($uId) {
        $result = array('u_nick' => '');
        $userInfo = $this->dbh->select('user_users',
            array('u_nick', 'u_fullname'),
            array('u_id' => $uId),
            null, array(1)
        );
        if ($userInfo) {
            $result = $userInfo[0];
            if (!$result['u_nick']) {
                $result['u_nick'] = $result['u_fullname'];
            }
            unset($result['u_fullname']);
        }
        return $result;
    }

    /**
     * Билдим результаты как JSON
     *
     * @param array $comment
     * @return IBuilder
     */
    private function buildResult($comment) {
        $builder = new CommentsJSONBuilder();

        $dataDescription = new DataDescription();
        $this->setDataDescription($dataDescription);
        $localData = new Data();
        $this->setData($localData);
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

        return $builder;
    }

    /**
     * Показываем комментарии
     * @return void
     */
    protected function showComments() {

        if ($this->getParam('bind_state') == 'main') {
            $targetIds = $this->document->getID();
        } else {
            $priFieldName = $this->bindComponent->getPK();
            $targetIds = $this->bindComponent->getData()->getFieldByName($priFieldName)->getData();
        }

        $commentsParams = array(
            'active' => true,
            'table_name' => $this->targetTable,
            'is_tree' => $this->getParam('is_tree'),
            'bind' => $this->getParam('bind'),
            'recordsPerPage' => $this->getParam('recordsPerPage'),
            'target_ids' => $targetIds
        );

        $this->setProperty('bind', $this->getParam('bind'));

        /** @var $commentsList CommentsList */
        $commentsList =
                $this->document->componentManager->createComponent('commentsList', 'comments', 'CommentsList', $commentsParams);

        $this->document->componentManager->addComponent($commentsList);

        if ($this->getParam('bind') && $forumTheme =
                $this->document->componentManager->getBlockByName($this->getParam('bind'))) {
            $ap = $forumTheme->getStateParams(true);
            if (isset($ap['pageNumber'])) {
                $commentsList->addActionParam('pageNumber', $ap['pageNumber']);
            }
        }
        $commentsList->run();
    }

    protected function ban(){
        $this->request->setPathOffset($this->request->getPathOffset() + 1);
        $this->userEditor = $this->document->componentManager->createComponent('ue','user','UserEditor');
        $this->userEditor->run();
    }

    public function build(){
        $result = '';
        switch($this->getState()){
            case 'ban':
                $result = $this->userEditor->build();
                break;
            case 'banip':
                $result = $this->banIPEditor->build();
                break;
            default:
                $result = parent::build();
                break;
        }

        return $result;
    }

    protected function banip(){
        $this->request->setPathOffset($this->request->getPathOffset() + 1);
        $this->banIPEditor = $this->document->componentManager->createComponent('bie','user','BanIPEditor');
        $this->banIPEditor->run();
    }

    protected function checkCaptcha() {
        require_once('core/modules/share/gears/recaptchalib.php');
        $privatekey = $this->getConfigValue('recaptcha.private');
        $resp = recaptcha_check_answer($privatekey,
            $_SERVER["REMOTE_ADDR"],
            $_POST["recaptcha_challenge_field"],
            $_POST["recaptcha_response_field"]);

        if (!$resp->is_valid) {
            throw new SystemException($this->translate('TXT_BAD_CAPTCHA'), SystemException::ERR_CRITICAL);
        }
    }

}
