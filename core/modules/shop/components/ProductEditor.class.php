<?php
/**
 * Содержит класс ProductEditor
 *
 * @package energine
 * @subpackage shop
 * @author Andrii A
 * @copyright Energine 2013
 */

/**
 * Редактор товаров для админ части
 * системы
 *
 * @package energine
 * @subpackage shop
 * @author Andrii A
 */
class ProductEditor extends Grid {
    /**
     * @var DivisionEditor
     */
    private $divisionEditor;

    /**
     * Конструктор класса
     *
     * @access public
     */
    public function __construct($name, $module, array $params = null) {
        parent::__construct($name, $module, $params);
        $this->setTableName('shop_product');
    }

    protected function createDataDescription() {
        $dd = parent::createDataDescription();
        if(in_array($this->getState(), array('add', 'edit'))) {
            $dd->getFieldDescriptionByName('smap_id')->setType(FieldDescription::FIELD_TYPE_SMAP_SELECTOR);
        }
        return $dd;
    }

    /**
     * Вывод дерева разделов для форм добавления/редактирования
     */
    protected function showSmapSelector() {
        $this->request->shiftPath(1);
        $this->divisionEditor = ComponentManager::createBlockFromDescription(
            ComponentManager::getDescriptionFromFile('core/modules/apps/templates/content/site_div_selector.container.xml')
        );
        $this->divisionEditor->run();
    }

    public function build() {
        if ($this->getState() == 'showSmapSelector') {
            $result = $this->divisionEditor->build();
        } else {
            $result = Grid::build();
        }
        return $result;
    }
}