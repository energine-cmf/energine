<?php
/**
 * Содержит класс ReviewsFeedEditor.
 *
 * @package energine
 * @subpackage site
 * @author d.pavka
 * @copyright d.pavka@gmal.com
 * @version $Id$
 */

 /**
 * Редактор списка отзывов
 *
 * @package energine
 * @subpackage site
 * @author d.pavka
 * @final
 */
final class ReviewsFeedEditor extends FeedEditor {
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
        $this->setTableName('site_reviews');
        $this->setOrderColumn('review_order_num');
    }

}