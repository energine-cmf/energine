<?php
/**
 * Содержит класс ArticlesFeedEditor.
 *
 * @package energine
 * @subpackage misc
 * @author d.pavka
 * @copyright d.pavka@gmal.com
 * @version $Id$
 */

/**
 * Редактор статей
 *
 * @package energine
 * @subpackage misc
 * @author d.pavka
 */
class ArticlesFeedEditor extends FeedEditor {
    /**
     * Конструктор класса
     *
     * @param string $name
     * @param string $module
     * @param Document $document
     * @param array $params
     * @access public
     */
    public function __construct($name, $module, Document $document,  array $params = null) {
        parent::__construct($name, $module, $document,  $params);
        $this->setTableName('aux_articles');
    }

    /**
     * Добавляем начальные значения для статуса статьи
     *
     * @access protected
     * @return void
     */
    protected function add() {
        parent::add();
        $this->getData()->getFieldByName('art_is_active')->setData(true, true);
        $this->getData()->getFieldByName('art_is_commentable')->setData(true, true);
        $this->getData()->getFieldByName('art_date')->setData(date('Y-m-d H:i:s'), true);
    }



}