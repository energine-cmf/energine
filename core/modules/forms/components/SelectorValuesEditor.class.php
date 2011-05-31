<?php
/**
 * Содержит класс SelectorValuesEditor
 *
 * @package energine
 * @subpackage forms
 * @author d.pavka
 * @copyright d.pavka@gmail.com
 */

 /**
  * Редатор значений селекта
  * вызывается из
  * @see FormEditor
  * @package energine
  * @subpackage forms
  * @author d.pavka@gmail.com
  */
 class SelectorValuesEditor extends Grid {
     private $mainTableName;
     private $fieldName;
    /**
     * Конструктор класса
     *
     * @param string $name
     * @param string $module
     * @param array $params
     * @access public
     */
    public function __construct($name, $module, array $params = null) {
        parent::__construct($name, $module,  $params);
        $this->fieldName = $this->getParam('field_name');
        $this->mainTableName = $this->getParam('table_name');
    }

     protected function defineParams(){
         return array_merge(
            parent::defineParams(),
            array(
                'field_name' => false,
                'table_name' => false
            )
        );
     }
}