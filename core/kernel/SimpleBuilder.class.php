<?php
/**
 * Содержит класс SimpleBuilder.
 *
 * @package energine
 * @subpackage core
 * @author dr.Pavka
 * @copyright Energine 2010
 */
/**
 * Упрощенный построитель.
 * Используется в тех случаях когда нет необходимости
 * выводить все аттрибуты поля
 *
 * @package energine
 * @subpackage core
 * @author dr.Pavka
 */
class SimpleBuilder extends Builder {
    /**
     * Конструктор класса.
     *
     * @param string recordset title
     * @access public
     * @return void
     */
    public function __construct($title = '') {
        parent::__construct();
        $this->title = $title;
    }
    /**
     * 
     *
     * @access protected
     * @param string $fieldName
     * @param FieldDescription $fieldInfo
     * @param mixed $fieldValue
     * @param mixed $fieldProperties
     * @return DOMNode
     */
    protected function createField($fieldName, FieldDescription $fieldInfo, $fieldValue = false, $fieldProperties = false) {
        foreach(
            array(
                'nullable',
                'pattern',
                'message',
                'tabName',
                'tableName',
                'sort',
                'customField',
                'deleteFileTitle',
                /*'msgOpenField',
                'msgCloseField',*/
                'default'
            ) as $propertyName
        ) {
            $fieldInfo->removeProperty($propertyName);
        }
        
        return parent::createField($fieldName, $fieldInfo, $fieldValue, $fieldProperties);
    }
}
