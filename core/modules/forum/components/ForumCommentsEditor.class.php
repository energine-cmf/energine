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

    protected function loadDataDescription(){
        $result = parent::loadDataDescription();
        if($this->getAction() == 'edit'){
            unset($result['comment_parent_id']);
        }
        return $result;
    }

    
}