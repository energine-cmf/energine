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
     * @var FormResults
     */
    private $results;

    private $formComponent;
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
        $this->setOrder(array('form_creation_date' => QAL::DESC));
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
    protected function showResult(){
        list($formID) = $this->getStateParams();
        E()->getRequest()->shiftPath(2);
        $this->results = $this->document->componentManager->createComponent('form', 'forms', 'FormResults', array('form_id' => $formID));
        $this->results->run();
    }
    /*
     * Method viewForm for Form preview in FormsEditor
     */
    protected function viewForm(){
        $this->setType(self::COMPONENT_TYPE_FORM);

        $formID = $this->getStateParams();
        if(!$formID = intval($formID[0]))
            throw new SystemException('ERR_INVALID_FORM_ID');

        $tableName = $this->getConfigValue('forms.database').'.form_'.$formID;
        if(!$this->dbh->tableExists($tableName))
            throw new SystemException('ERR_NO_FORM_TABLE_FOUND');

        $columnsInfo = $this->dbh->getColumnsInfo($tableName);
        foreach($columnsInfo as $key=>$value){
            $columnsInfo[$key]['tabName'] = $this->translate('TXT_TAB_FORM');
        }
        $dd = new DataDescription();
        $dd->load($columnsInfo);
        $this->setDataDescription($dd);

        $d = new Data();
        $f = new Field('pk_id');
        $f->setData(1, true);
        $d->addField($f);
        $this->setData($d);

        $this->setBuilder(new Builder());
        $toolbars = $this->createToolbar();
        if (!empty($toolbars)) {
            $this->addToolbar($toolbars);
        }
        $this->js = $this->buildJS();
    }

    public function build(){
        if($this->getState() == 'editForm'){
            $result = $this->form->build();
        }
        elseif($this->getState() == 'showResult'){
    	    $result = $this->results->build();
        }
        else {
            $result = parent::build();
        }
        return $result;
    }
}