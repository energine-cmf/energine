<?php
/**
 * Содержит класс FormConstuctor
 *
 * @package energine
 * @subpackage forms
 * @author d.pavka
 * @copyright d.pavka@gmail.com
 */

/**
 * Конструктор формы
 *
 * @package energine
 * @subpackage forms
 * @author d.pavka@gmail.com
 */
class FormEditor extends DataSet
{
    /**
     * @var SelectorValuesEditor
     */
    private $SVEditor;

    /**
     * @var FormConstructor
     */
    private $constructor;

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
        if (!$this->getParam('form_id')) {
            throw new SystemException('ERR_BAD_FORM_ID');
        }
        $this->constructor = new FormConstructor($this->getParam('form_id'));
        $this->setProperty('exttype', 'grid');
        $this->setType(self::COMPONENT_TYPE_LIST);

    }

    protected function defineParams()
    {
        return array_merge(
            parent::defineParams(),
            array(
                'form_id' => false,
                'active' => true
            )
        );
    }

    protected function createDataDescription()
    {
        //We work with other fields when we edit field - so create other DataDescription.
        if(in_array($this->getState(), array('edit'))){
            $dd = new DataDescription();
            $dd->load(array(
                 'ltag_id' => array(
                     'nullable' => false,
                     'length' => 10,
                     'default' => '',
                     'key' => true,
                     'type' => FieldDescription::FIELD_TYPE_INT,
                     'index' => 'PRI',
                     'tableName' => 'share_lang_tags',
                     'languageID' => false,
                 ),
                 'lang_id' => array(
                     'nullable' => false,
                     'length' => 10,
                     'default' => '',
                     'key' => false,
                     'type' => FieldDescription::FIELD_TYPE_INT,
                     'index' => 'PRI',
                     'tableName' => 'share_lang_tags_translation',
                     'languageID' => true,
                     'isMultilanguage' => true
                 ),
                'field_type' => array(
                     'nullable' => false,
                     'length' => 255,
                     'default' => '',
                     'key' => false,
                     'type' => FieldDescription::FIELD_TYPE_STRING,
                     'mode' => FieldDescription::FIELD_MODE_READ,
                     'index' => false,
                     'languageID' => false
                 ),
                 'ltag_name' => array(
                     'nullable' => false,
                     'length' => 255,
                     'default' => '',
                     'key' => false,
                     'type' => FieldDescription::FIELD_TYPE_STRING,
                     'index' => false,
                     'languageID' => false
                 ),
                'ltag_value_rtf' => array(
                     'nullable' => false,
                     'length' => 1024,
                     'default' => '',
                     'key' => false,
                     'type' => FieldDescription::FIELD_TYPE_TEXT,
                     'index' => false,
                     'tableName' => 'share_lang_tags_translation',
                     'isMultilanguage' => true
                 )));
            $dd->getFieldDescriptionByName('ltag_name')->setMode(FieldDescription::FIELD_MODE_READ);
            return $dd;
        } else {
            return $this->constructor->getDataDescription();
        }
    }

    protected function createBuilder() {
        return new MultiLanguageBuilder();
    }

    protected function loadData()
    {
        return false;
    }

    protected function createData()
    {
        return $this->constructor->getData((isset($_POST['languageID']))?$_POST['languageID']:E()->getLanguage()->getDefault());
    }

    protected function getRawData()
    {
        $this->setBuilder(new JSONBuilder());
        $this->setDataDescription($this->createDataDescription());
        $this->createPager();

        //$this->applyUserFilter();
        //$this->applyUserSort();

        $this->setData($this->createData());

        if ($this->pager) $this->getBuilder()->setPager($this->pager);
    }

    protected function add(){
        $this->setType(self::COMPONENT_TYPE_FORM_ADD);
        $this->setBuilder($this->createBuilder());
        $this->setDataDescription($this->createDataDescription());
        $this->setData(new Data());
        $toolbars = $this->createToolbar();
        if (!empty($toolbars)) {
            $this->addToolbar($toolbars);
        }
        $this->js = $this->buildJS();
    }
    /*
     * @access protected
     * edit() function gets information about selected field to modal form, so user can edit it.
     *
     * */
    protected function edit(){
        $this->setType(self::COMPONENT_TYPE_FORM_ALTER);
        $this->setBuilder($this->createBuilder());

        $fieldID = $this->getStateParams();
        if(!is_array($fieldID) || !$fieldID[0]){
            throw new SystemException('ERR_WRONG_FIELD_ID');
        }
        $fieldName = $this->getFieldnameByIndex($fieldID[0]);
        //Get field translations
        $result = $this->dbh->selectRequest(
                'SELECT * FROM share_lang_tags lt
                    LEFT JOIN share_lang_tags_translation ltt ON lt.ltag_id=ltt.ltag_id 
                    WHERE lt.ltag_name = %s','FIELD_'.strtoupper($fieldName));

        if(is_array($result)){
            //Load field translations, translation tag and ID.
            $this->setDataDescription($this->createDataDescription());
            $d = new Data();
            $d->load($result);
            $this->setData($d);
            //Load information about field type.
            $fieldsInfo = $this->dbh->getColumnsInfo($this->getConfigValue('forms.database').'.form_'.$this->getParam('form_id'));
            $fieldInfo = $fieldsInfo[strtolower($fieldName)];
            $fieldInfo = FieldDescription::convertType($fieldInfo['type'], $fieldName, $fieldInfo['length']);
            $fieldInfo = 'FIELD_TYPE_'.strtoupper($fieldInfo);
            //Destroy unused variables.
            unset($fieldsInfo, $fieldName, $fieldID);
            //Create field FIELD_TYPE.
            $f = new Field('field_type');
            $f->setRowData(1,$this->translate($fieldInfo));
            $this->getData()->addField($f);
            $this->getDataDescription()->getFieldDescriptionByName('field_type')->setProperty('tabName','TXT_PROPERTIES');
        }
        //Create toolbar and JS.
        $toolbars = $this->createToolbar();
        if (!empty($toolbars)) {
            $this->addToolbar($toolbars);
        }
        $this->js = $this->buildJS();
    }

    protected function main()
    {
        $this->setBuilder($this->createBuilder());
        $this->setDataDescription($this->createDataDescription());
        $this->createPager();
        $this->setData(new Data());
        $toolbars = $this->createToolbar();
        if (!empty($toolbars)) {
            $this->addToolbar($toolbars);
        }
        $this->js = $this->buildJS();
        $this->addTranslation('TXT_FILTER', 'BTN_APPLY_FILTER', 'TXT_RESET_FILTER', 'TXT_FILTER_SIGN_BETWEEN', 'TXT_FILTER_SIGN_CONTAINS', 'TXT_FILTER_SIGN_NOT_CONTAINS');
    }

    protected function up(){
        list($fieldIndex) = $this->getStateParams();
        $this->constructor->changeOrder(Grid::DIR_UP, $fieldIndex);
        $b = new JSONCustomBuilder();
        $b->setProperties(array(
               'result' => true,
               'dir' => Grid::DIR_UP
          ));
        $this->setBuilder($b);
    }

    protected function down(){
        list($fieldIndex) = $this->getStateParams();
        $this->constructor->changeOrder(Grid::DIR_DOWN, $fieldIndex);
        $b = new JSONCustomBuilder();
        $b->setProperties(array(
               'result' => true,
               'dir' => Grid::DIR_DOWN
          ));
        $this->setBuilder($b);
    }

    protected function editSelector(){
        list($fieldIndex) = $this->getStateParams();
        E()->getRequest()->shiftPath(2);
        $fieldInfo = $this->getFieldnameByIndex($fieldIndex, true);

        $tableName = current($fieldInfo);
        $tableName = $tableName['key']['tableName'];
        $this->SVEditor = $this->document->componentManager->createComponent('form', 'forms', 'SelectorValuesEditor', array('table_name' => $tableName));
        $this->SVEditor->run();

    }


    protected function delete(){
        list($fieldIndex) = $this->getStateParams();
        $this->constructor->delete($this->getFieldnameByIndex($fieldIndex));

        $this->setBuilder(new JSONCustomBuilder());
    }

    protected function save(){
        if(isset($_POST)){
           $this->constructor->save($_POST);
        }
        $this->setBuilder(new JSONCustomBuilder());
    }

    protected function saveField(){
        $tableName = 'share_lang_tags_translation';
        if(isset($_POST) && isset($_POST[$tableName]) && isset($_POST['share_lang_tags'])){
            $translations = $_POST[$tableName];
            if(is_array($translations)){
                foreach($translations as $key=>$value){
                    $result = $this->dbh->modify(
                                QAL::UPDATE,
                                $tableName,
                                array('ltag_value_rtf' => $value['ltag_value_rtf']),
                                array('ltag_id' => $_POST['share_lang_tags'], 'lang_id' => $value['lang_id']));
                }
            }

        }
        $this->setBuilder(new JSONCustomBuilder());
    }

    public function build(){
        if($this->getState() == 'editSelector'){
            $result = $this->SVEditor->build();
        }
        else {
            $result = parent::build();
        }

        return $result;
    }

    private function getFieldnameByIndex($fieldIndex, $asArray = false){
        if($fieldIndex == 1){
            throw new SystemException('ERR_BAD_REQUEST', SystemException::ERR_WARNING);
        }
        $colsInfo = $this->dbh->getColumnsInfo($this->constructor->getTableName());
        $cols = array_keys($colsInfo);
        if(!isset($cols[$fieldIndex - 1])){
            throw new SystemException('ERR_BAD_REQUEST', SystemException::ERR_WARNING);
        }

        if($asArray){
            return array(
                $cols[$fieldIndex - 1] => $colsInfo[$cols[$fieldIndex - 1]]
            );
        }
        else {
            return $cols[$fieldIndex - 1];
        }
    }

}