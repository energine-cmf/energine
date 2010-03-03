<?php
/**
 * Содержит класс StaffFeed.
 *
 * @package energine
 * @subpackage misc
 * @author d.pavka
 * @copyright d.pavka@gmail.com
 */

 /**
 * Список сотрудников
 *
 * @package energine
 * @subpackage misc
 * @author d.pavka
 */
 class StaffFeed extends Feed {
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
        $this->setTableName('hrm_staff');
        $this->setOrder(array('staff_order_num' => QAL::ASC));
    }
}