<?php
/**
 * Содержит класс ReviewsFeed.
 *
 * @package energine
 * @subpackage site
 * @author d.pavka
 * @copyright d.pavka@gmal.com
 * @version $Id$
 */

 /**
 * Список отзывов
 *
 * @package energine
 * @subpackage site
 * @author d.pavka
 * @final
 */
final class ReviewsFeed extends Feed {
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
        $this->setOrder(array('review_order_num'=>QAL::DESC));
    }

}