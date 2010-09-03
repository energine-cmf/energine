<?php
/**
 * Содержит класс FeedbackList
 *
 * @package energine
 * @subpackage share
 * @author dr.Pavka
 * @copyright Energine 2007
 * @version $Id: FeedbackList.class.php,v 1.6 2008/08/27 15:39:16 chyk Exp $
 */

//require_once('core/modules/share/components/Grid.class.php');

/**
 * Спсиок сообщений поступивших с формы связи
 *
 * @package energine
 * @subpackage share
 * @author dr.Pavka
 */
class FeedbackList extends Grid {
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
        $this->setTableName('apps_feedback');
        $this->setOrder(array('feed_date'=> QAL::DESC));
	}
}