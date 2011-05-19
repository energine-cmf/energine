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

    protected function delete(){
        list($fieldIndex) = $this->getStateParams();
        $this->constructor->delete($fieldIndex);
        
        $this->setBuilder(new JSONCustomBuilder());
    }

    protected function save(){
        if(isset($_POST)){
           $this->constructor->save($_POST);
        }
        $this->setBuilder(new JSONCustomBuilder());
    }
}