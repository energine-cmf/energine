<?php
/**
 * Содержит класс ForumCommentsEditor
 *
 * @package energine
 * @subpackage forum
 * @author d.pavka
 * @copyright d.pavka@gmail.com
 */

/**
 * Редактор комментариев форума
 *
 * @package energine
 * @subpackage forum
 * @author d.pavka@gmail.com
 */
class ForumCommentsEditor extends Grid {
    /**
     * @var ForumThemeEditor
     */
    private $themeEditor;

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
        parent::__construct($name, $module, $document, $params);
        $this->setTableName('forum_theme_comment');
        $this->setOrder(array('comment_created' => QAL::DESC));
    }

    protected function loadDataDescription() {
        $result = parent::loadDataDescription();
        if ($this->getAction() == 'edit') {
            unset($result['comment_parent_id']);
            $result['u_id']['key'] = false;
            $result['u_id']['type'] = QAL::COLTYPE_STRING;
            $result['target_id']['key'] = false;
        }
        return $result;
    }

    protected function prepare() {
        parent::prepare();
        if($this->getAction() !== 'getRawData' && $this->getAction() != 'main'){
        $UIDFD = $this->getDataDescription()->getFieldDescriptionByName('u_id');
        $UIDFD->setMode(1);
        $UIDFD->removeProperty('tableName');

        $UID = $this->getData()->getFieldByName('u_id');
        $UID->setRowData(0,
            simplifyDBResult(
                $this->dbh->select(
                    'user_users',
                    'u_fullname',
                    array('u_id' => $UID->getRowData(0))
                ),
                'u_fullname',
                true));
        }
    }

    protected function edit() {
        parent::edit();
    }

    protected function showThemeEditor() {
        $this->request->setPathOffset($this->request->getPathOffset() + 1);
        $this->themeEditor =
                $this->document->componentManager->createComponent('themeEditor', 'forum', 'ForumThemeEditor', null);
        $this->themeEditor->run();
    }

    public function build() {
        switch ($this->getAction()) {
            case 'showThemeEditor':
                $result = $this->themeEditor->build();
                break;
            default:
                $result = parent::build();
        }

        return $result;
    }


}