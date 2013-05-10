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


/**
 * Список сообщений поступивших с формы связи
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

     * @param array $params
     * @access public
     */
    public function __construct($name, $module, array $params = null) {
        parent::__construct($name, $module, $params);
        $this->setOrder(array('feed_date' => QAL::DESC));
    }

    protected function defineParams() {
        $result = array_merge(
            parent::defineParams(),
            array(
                 'tableName' => 'apps_feedback',
            )
        );
        return $result;
    }
}