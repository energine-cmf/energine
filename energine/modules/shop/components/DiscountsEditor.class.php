<?php
/**
 * Содержит класс DiscountsEditor
 *
 * @package energine
 * @subpackage shop
 * @author 1m.dm
 * @copyright ColoCall 2006
 * @version $Id$
 */

//require_once('core/modules/share/components/Grid.class.php');

/**
 * Редактор скидок
 *
 * @package energine
 * @subpackage shop
 * @author 1m.dm
 */
class DiscountsEditor extends Grid {

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
        $this->setTableName('shop_discounts');
    }

    protected function prepare() {
        parent::prepare();
        if ($this->getAction() == 'add' || $this->getAction() == 'edit') {
            $dateDescr = $this->getDataDescription();
            $fieldDescr = $dateDescr->getFieldDescriptionByName('group_id');
            if ($this->getAction() == 'add') {
                $result = $this->dbh->selectRequest('SELECT * FROM `user_groups` WHERE group_id NOT IN (SELECT group_id FROM shop_discounts)');
            }
            elseif ($this->getAction() == 'edit') {
                $data = $this->getData();
                $field = $data->getFieldByName('group_id');
                $currGroupId = $field->getRowData(0);
                $currGroupId = intval($currGroupId[0]);
                $result = $this->dbh->selectRequest(
                    'SELECT * FROM `user_groups` WHERE group_id NOT IN (SELECT group_id FROM shop_discounts WHERE group_id != %s)',
                    $currGroupId
                );
            }
            $fieldDescr->loadAvailableValues($result, 'group_id', 'group_name');
        }
    }
}
