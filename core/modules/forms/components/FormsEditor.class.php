<?php
/**
 * Содержит класс FormEditor
 *
 * @package energine
 * @subpackage forms
 * @author d.pavka
 * @copyright d.pavka@gmail.com
 */

/**
 * Построитель пользовательских форм
 *
 * @package energine
 * @subpackage forms
 * @author d.pavka@gmail.com
 */
class FormsEditor extends Grid {
    private $formDB;
    /**
     * Конструктор класса
     *
     * @param string $name
     * @param string $module
     * @param array $params
     * @access public
     */
    public function __construct($name, $module, array $params = null)
    {
        parent::__construct($name, $module, $params);
        $this->setTableName('frm_forms');
        $this->formDB = $this->getConfigValue('forms.database');
    }

    protected function add(){
        parent::add();
        $this->getDataDescription()->getFieldDescriptionByName('form_creation_date')->setMode(FieldDescription::FIELD_MODE_READ);
    }

    protected function edit(){
        parent::edit();
        $this->getDataDescription()->getFieldDescriptionByName('form_creation_date')->setMode(FieldDescription::FIELD_MODE_READ);
    }
}