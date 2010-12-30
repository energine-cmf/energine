<?php
/**
 * Содержит класс WidgetsRepository
 *
 * @package energine
 * @subpackage share
 * @author spacelord
 * @copyright Energine 2010
 * @version $Id
 */


/**
 * Список виджетов (компонентов)
 *
 * @package energine
 * @subpackage share
 * @author spacelord
 */


class WidgetsRepository extends Grid {
    /**
     * @var Component 
     *
     */
    private $tmpComponent;

    /**
     * Конструктор класса
     *
     * @param string $name
     * @param string $module

     * @param array $params
     * @access public
     */
	public function __construct($name, $module,   array $params = null) {
        parent::__construct($name, $module,  $params);
        $this->setTableName('share_widgets');
	}
    /**
     * Постройка формы редактирования параметров компонента
     * Получает на вход XML данные виджета, на основании которых и строит форму
     *
     * @throws SystemException
     * @return void
     */    
    protected function buildParamsForm() {
        if (!isset($_POST['modalBoxData'])) {
            throw new SystemException('ERR_INSUFFICIENT_DATA');
}        if (!$widgetXML = simplexml_load_string($_POST['modalBoxData'])) {
            throw new SystemException('ERR_BAD_XML_DESCR');
}        list($componentName) = $this->getStateParams();
        $component =
                ComponentManager::findBlockByName($widgetXML, $componentName);
        $dd = new DataDescription();
        $d = new Data();
        $this->setType(self::COMPONENT_TYPE_FORM_ALTER);
        $this->setDataDescription($dd);
        $this->setData($d);
        $this->setBuilder(new Builder());
        $this->js = $this->buildJS();
        foreach ($component->params->children() as $param) {
            $paramName = (string) $param['name'];

            $paramType = (isset($param['type']))?(string) $param['type']:FieldDescription::FIELD_TYPE_STRING;

            $fd = new FieldDescription($paramName);
            $fd->setType($paramType)->setProperty('tabName', $this->translate('TAB_PARAMS'));
            if(($paramType == FieldDescription::FIELD_TYPE_SELECT) && isset($param['values'])){
                $availableValues = array();
                foreach(explode('|', (string)$param['values']) as $value){
                    array_push($availableValues, array('key' => $value, 'value' =>$value));
                }
                $fd->loadAvailableValues($availableValues, 'key', 'value');
            }
            $dd->addFieldDescription($fd);
            $f = new Field($paramName);
            $f->setRowData(0, $param);
            $d->addField($f);
        }
        $this->addToolbar($this->createToolbar());
    }

    public function buildWidget() {
        if (!isset($_POST['xml'])) {
            throw new SystemException('ERR_BAD_DATA');
        }
        $xml = $_POST['xml'];
        $xml = simplexml_load_string($xml);
        unset($_SERVER['HTTP_X_REQUEST']);
        $this->request->setPathOffset($this->request->getPathOffset() + 1);
        $this->tmpComponent =
                ComponentManager::createBlockFromDescription($xml);
        $this->tmpComponent->run();
    }

    public function build() {
        switch ($this->getState()) {
            case 'buildWidget':
                $result = $this->tmpComponent->build();
                break;
            default:
                $result = parent::build();
                break;
        }

        return $result;
    }

/*    protected function deleteWidget() {
        inspect($_POST);
    }*/

    protected function saveContent() {
        if (!isset($_POST['xml'])){
            throw new SystemException('ERR_INSUFFICIENT_DATA');
        }
        $xml = $_POST['xml'];
        if(!simplexml_load_string($xml)){
            throw new SystemException('ERR_BAD_XML');
        }
        $this->dbh->modify(QAL::UPDATE, 'share_sitemap', array('smap_content_xml' => $xml), array('smap_id' => E()->getDocument()->getID()));
        $b = new JSONCustomBuilder();
        $b->setProperties(array(
            'xml'=> $xml,
            'result' => true,
            'mode' => 'none'
        ));
        $this->setBuilder($b);
    }
}