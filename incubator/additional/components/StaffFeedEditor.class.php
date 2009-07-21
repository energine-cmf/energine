<?php
/**
 * Содержит класс StaffFeedEditor.
 *
 * @package energine
 * @subpackage misc
 * @author d.pavka
 * @copyright d.pavka@gmail.com
 */

/**
 * Редактор статей
 *
 * @package energine
 * @subpackage misc
 * @author d.pavka
 */
class StaffFeedEditor extends FeedEditor {
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
        $this->setTableName('aux_staff');
        $this->setOrderColumn('staff_order_num');
    }
}