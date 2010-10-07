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

     * @param array $params
     * @access public
     */
    public function __construct($name, $module,  array $params = null) {
        parent::__construct($name, $module, $params);
        $this->setTableName('forum_theme_comment');
        $this->setOrder(array('comment_created' => QAL::DESC));
        $this->setSaver(new ForumCommentsEditorSaver());
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


    protected function edit() {
        parent::edit();
        $this->getDataDescription()->getFieldDescriptionByName('u_id')->setMode(ACCESS_READ);

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
        $this->getDataDescription()->getFieldDescriptionByName('target_id')->setType(FieldDescription::FIELD_TYPE_CUSTOM)->setProperty('title', $this->translate('FIELD_THEME_NAME2'));
        $themeID = $this->getData()->getFieldByName('target_id');
        $themeID->setRowProperty(0,
            'text',
            simplifyDBResult(
                $this->dbh->select(
                    'forum_theme',
                    'theme_name',
                    array('theme_id' => $themeID->getRowData(0))
                ),
                'theme_name',
                true));
    }

    protected function showThemeEditor() {
        $this->request->setPathOffset($this->request->getPathOffset() + 1);
        $this->themeEditor =
                $this->document->componentManager->createComponent('themeEditor', 'forum', 'ForumThemeEditor', array('config'=>'core/modules/forum/config/ModalForumThemeEditor.component.xml'));
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