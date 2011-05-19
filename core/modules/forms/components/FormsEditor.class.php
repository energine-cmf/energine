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
    /**
     * @var FormEditor
     */
    private $form;
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
        $this->setSaver(new FormsSaver());
    }

    protected function createDataDescription(){
        $result = parent::createDataDescription();
        if(in_array($this->getState(), array('main', 'getRawData'))){
            $result->getFieldDescriptionByName('form_id')->setType(FieldDescription::FIELD_TYPE_INT);
        }

        return $result;
    }

    protected function add(){
        parent::add();
        $this->getDataDescription()->getFieldDescriptionByName('form_creation_date')->setMode(FieldDescription::FIELD_MODE_READ);
        
    }

    protected function edit(){
        parent::edit();
        $this->getDataDescription()->getFieldDescriptionByName('form_creation_date')->setMode(FieldDescription::FIELD_MODE_READ);
    }

    protected function editForm(){
        list($formID) = $this->getStateParams();
        E()->getRequest()->shiftPath(2);
        $this->form = $this->document->componentManager->createComponent('form', 'forms', 'FormEditor', array('form_id' => $formID));
        $this->form->run();
    }

    public function build(){
        if($this->getState() == 'editForm'){
            $result = $this->form->build();
        }
        else {
            $result = parent::build();
        }
        return $result;
    }
}