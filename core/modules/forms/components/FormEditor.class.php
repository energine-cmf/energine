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
        return $this->constructor->getDataDescription();
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

    protected function edit(){
        $this->setType(self::COMPONENT_TYPE_FORM_ALTER);
        $this->setBuilder($this->createBuilder());
        $this->setDataDescription($this->createDataDescription());
        $this->setData($this->createData());
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