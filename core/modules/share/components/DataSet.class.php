<?php
/**
 * Содержит класс DataSet
 *
 * @package energine
 * @subpackage share
 * @author dr.Pavka
 * @copyright Energine 2006
 * @version $Id: DataSet.class.php,v 1.32 2008/12/13 14:10:09 pavka Exp $
 */

//require_once('core/framework/Component.class.php');
//require_once('core/framework/DataDescription.class.php');
//require_once('core/framework/Data.class.php');
//require_once('core/framework/SimpleBuilder.class.php');
//require_once('core/framework/Pager.class.php');
//require_once('core/modules/share/components/Toolbar.class.php');

/**
 * Предок для формы, списка, и дерева; содержит методы работы с панелью инструментов и набором данных.
 *
 * @package energine
 * @subpackage share
 * @author dr.Pavka
 * @abstract
 */
abstract class DataSet extends Component {

    /**
     * Тип компонента - список
     */
    const COMPONENT_TYPE_LIST = 'list';

    /**
     * Тип компонента - форма
     */
    const COMPONENT_TYPE_FORM = 'form';

    /**
     * Тип формы - форма добавления
     */
    const COMPONENT_TYPE_FORM_ADD = QAL::INSERT;

    /**
     * Тип формы - форма редактирования
     */
    const COMPONENT_TYPE_FORM_ALTER = QAL::UPDATE;

    /**
     * Префикс названия панели инструментов
     */
    const TB_PREFIX = 'toolbar_';

    /**
     * Описание данных
     *
     * @var DataDescription
     * @access private
     */
    private $dataDescription = false;

    /**
     * Данные
     *
     * @var Data
     * @access private
     */
    private $data = false;

    /**
     * Панель инструментов
     *
     * @var Toolbar
     * @access private
     */
    private $toolbar = false;

    /**
     * JavaScript
     *
     * @var DOMNode
     * @access protected
     */
    protected $js;

    /**
     * Тип компонента
     *
     * @var string
     * @access private
     */
    private $type;

    /**
     * Переводы которые добавляются к форме/списку
     *
     * @var array
     * @access private
     */
    private $translations = array();


    /**
     * Список страниц (pager)
     *
     * @var Pager
     */
    protected $pager;

    /**
     * Набор табов
     *
     * @var array
     * @access protected
     */
    protected $tabs = array();

    /**
     * Конструктор класса
     *
     * @return void
     */
    public function __construct($name, $module, Document $document,  array $params = null) {
        parent::__construct($name, $module, $document,  $params);
        $this->setType(self::COMPONENT_TYPE_FORM);
        if(!$this->getParam('recordsPerPage'))$this->setParam('recordsPerPage', 20);
        if($this->getParam('title'))
            $this->setTitle(
                $this->translate($this->getParam('title')
            )
        );
    }

    /**
     * Добавлены:
     * Параметр datasetAction
     * Параметр recordsPerPage
     *
     * @return array
     * @access protected
     */
    protected function defineParams() {
        $this->setProperty('action', '');
        return array_merge(
        parent::defineParams(),
        array(
        'datasetAction' => '',
        'recordsPerPage' => false,
        'title' => false
        )
        );
    }

    /**
     * Устанавливает данные
     *
     * @return void
     * @access protected
     * @final
     */
    final protected function setData(Data $data) {
        $this->data = $data;
    }

    /**
     * Возвращает объект данных
     *
     * @return Data
     * @access protected
     * @final
     */
    final protected function getData() {
        return $this->data;
    }

    /**
     * Возвращает тулбар
     *
     * @return ToolBar
     * @access protected
     */

    protected function getToolbar() {
        return $this->toolbar;
    }
    /**
      * Устанавливает обїект тулбара
      *
      * @return void
      * @access protected
      */

    protected function setToolbar(Toolbar $toolbar) {
        $this->toolbar = $toolbar;
    }

    /**
     * Устанавливает описание данных
     *
     * @return void
     * @access protected
     * @final
     */
    final protected function setDataDescription(DataDescription $dataDescription) {
        $this->dataDescription = $dataDescription;
    }

    /**
     * Возвращает описание данных
     *
     * @return DataDescription
     * @access protected
     * @final
     */
    final protected function getDataDescription() {
        // Существует ли описание данных?
        // Без описания данных компонент не сможет нормально работать.
        if (!$this->dataDescription) {
            throw new SystemException('ERR_DEV_NO_DATA_DESCRIPTION', SystemException::ERR_DEVELOPER, $this->getName());
        }

        // Описание данных не должно быть пустым
        if ($this->dataDescription->getLength() == 0) {
            //throw new SystemException('ERR_DEV_EMPTY_DATA_DESCRIPTION', SystemException::ERR_DEVELOPER, $this->getName());
        }

        return $this->dataDescription;
    }

    /**
	 * Подготовительные действия перед вызовом основного действия.
	 *
	 * @return void
	 * @access protected
	 */
    protected function prepare() {
        $this->setBuilder($this->createBuilder());
        $this->setDataDescription($this->createDataDescription());
        $this->createPager();
        $data = $this->createData();
        if ($data instanceof Data) {
            $this->setData($data);
        }
        if ($toolbar = $this->createToolbar()) {
            $this->setToolbar($toolbar);
        }
        $this->js = $this->buildJS();
    }

    /**
     * Создает построитель
     *
     * @return Builder
     * @access protected
     */
    protected function createBuilder() {
        return new SimpleBuilder($this->getTitle());
    }

    /**
     * Создаем объект описания данных
     *
     * @return DataDescription
     * @access protected
     */
    protected function createDataDescription() {
        // описание данных из конфигурации
        $configDataDescriptionObject = new DataDescription();
        if ($this->config->getCurrentMethodConfig()) {
            $configDataDescriptionObject->loadXML($this->config->getCurrentMethodConfig()->fields);
        }


        // внешнее описание данных
        $externalDataDescription = $this->loadDataDescription();
        if (is_null($externalDataDescription)) {
            throw new SystemException('ERR_DEV_LOAD_DATA_DESCR_IS_FUNCTION', SystemException::ERR_DEVELOPER);
        }

        // если существует внешнее описание данных - пересекаем с описанием из конфига
        if ($externalDataDescription) {
            $externalDataDescriptionObject = new DataDescription();
            $externalDataDescriptionObject->load($externalDataDescription);
            $configDataDescriptionObject = $configDataDescriptionObject->intersect($externalDataDescriptionObject);
        }

        return $configDataDescriptionObject;
    }

    /**
     * Создание панели инструментов
     *
     * @return void
     * @access protected
     */
    protected function createToolbar() {
        $toolbar = false;
        if ($this->config->getCurrentMethodConfig()) {
            $toolbar = new Toolbar(self::TB_PREFIX.$this->getName());
            $toolbar->attachToComponent($this);
            $toolbar->loadXML($this->config->getCurrentMethodConfig()->toolbar);
            $toolbar->translate();
        }

        return $toolbar;
    }

    /**
     * Создает листалку
     *
     * @return void
     * @access protected
     */

    protected function createPager() {
        $recordsPerPage = intval($this->getParam('recordsPerPage'));
        if ($recordsPerPage > 0) {
            $this->pager = new Pager($recordsPerPage);
            if ($this->isActive() && $this->getType() == self::COMPONENT_TYPE_LIST) {
                $actionParams = $this->getActionParams();
                if (isset($actionParams[0])) {
                    $page = intval($actionParams[0]);
                }
                else {
                    $page = 1;
                }
                $this->pager->setCurrentPage($page);
            }

            $this->pager->setProperty('title', $this->translate('TXT_PAGES'));
        }
    }

    /**
     * Абстрактный метод загрузки данных
     *
     * @return mixed
     * @access protected
     */
    protected function loadData() {
        return false;
    }

    /**
     * Абстрактный метод загрузки описания данных
     * Используется для загрузки внешнего описания данных (не из конфигурации)
     *
     * @return mixed
     * @access protected
     */
    protected function loadDataDescription() {
        return false;
    }

    /**
     * Проверяет наличие пострителя
     * передает ему данные и описание данных
     *
     * @return DOMDocument
     * @access public
     */
    public function build() {
        if (!$this->getBuilder()) {
            throw new SystemException('ERR_DEV_NO_BUILDER', SystemException::ERR_CRITICAL, $this->getName());
        }

        // передаем данные и описание данных построителю
        if ($this->getData()) {
            $this->getBuilder()->setData($this->getData());
        }
        $this->getBuilder()->setDataDescription($this->getDataDescription());

        // вызываем родительский метод построения
        $result = parent::build();


        if ($this->js) {
            $result->documentElement->appendChild($result->importNode($this->js, true));
        }

        if (($toolbar = $this->getToolbar()) && ($toolbar = $toolbar->build())) {
            $result->documentElement->appendChild($result->importNode($toolbar, true));
        }

        if ($this->pager && $this->getType() == self::COMPONENT_TYPE_LIST && $pagerData = $this->pager->build()) {
            $pager = $result->importNode($pagerData, true);
            $result->documentElement->appendChild($pager);
        }

        //Работа с константами переводов
        if (($methodConfig = $this->config->getCurrentMethodConfig()) && $methodConfig->translations) {
            foreach ($methodConfig->translations->translation as $translation) {
                $this->addTranslation($translation['const']);
            }
        }

        if (!empty($this->translations)) {
            $translationsXML = $this->doc->createElement('translations');
            foreach ($this->translations as $tag) {
                $translationXML = $this->doc->createElement('translation', $this->translate($tag));
                $translationXML->setAttribute('const', $tag);
                $translationsXML->appendChild($translationXML);
            }
            $result->documentElement->appendChild($translationsXML);
        }

        if (!empty($this->tabs)) {
            $tabsXML = $this->doc->createElement('tabs');

            foreach ($this->tabs as $tab) {
                $tabsXML->appendChild($tab);
            }

            $result->documentElement->appendChild($tabsXML);
        }

        return $result;
    }

    /**
     * Загружает данные
     *
     * @return Data
     * @access protected
     */
    protected function createData() {
        $result = false;
        $data = $this->loadData();
        if (is_null($data)) {
            throw new SystemException('ERR_DEV_LOAD_DATA_IS_FUNCTION', SystemException::ERR_DEVELOPER);
        }

        if (is_array($data)) {
            $result = new Data();
            $result->load($data);
        }
        return $result;
    }

    /**
     * Строит описание JS объектов
     *
     * @return DOMNode
     * @access protected
     */
    protected function buildJS() {
        $result = false;
        if (($config = $this->config->getCurrentMethodConfig()) && $config->javascript) {
            $result = $this->doc->createElement('javascript');
            foreach ($config->javascript->include as $value) {
                $JSIncludeXML = $this->doc->createElement('include');
                $JSIncludeXML->setAttribute('name', $value['name']);
                $JSIncludeXML->setAttribute('path', 'scripts/');
                $result->appendChild($JSIncludeXML);
            }
            foreach ($config->javascript->object as $value) {
                $JSObjectXML = $this->doc->createElement('object');
                $JSObjectXML->setAttribute('name', $value['name']);
                $JSObjectXML->setAttribute('path', 'scripts/');
                $result->appendChild($JSObjectXML);
            }
            foreach ($config->javascript->param as $value) {
                $JSParamXML = $this->doc->createElement('param', $value);
                $JSParamXML->setAttribute('name', $value['name']);
                $result->appendChild($JSParamXML);
            }
        }
        return $result;
    }

    /**
     * Устанавливает адрес обработчика формы
     *
     * @param string
     * @param bool
     * @access public
     */
    final protected function setDataSetAction($action, $isFullURI = false) {
        // если у нас не полностью сформированный путь, то добавляем информацию о языке + путь к шаблону
        if (!$isFullURI) {
            $action = $this->request->getLangSegment().
            $this->request->getPath(Request::PATH_TEMPLATE, true).
            $action;

            // если в конце нет слеша - добавляем его
            if (substr($action, -1) != '/') {
                $action .= '/';
            }
        }



        $this->setParam('datasetAction', $action);
        $this->setProperty('action', $action);
    }

    /**
     * Возвращает адрес обработчика формы
     *
     * @return string
     * @access public
     */
    final protected function getDataSetAction() {
        return $this->getParam('datasetAction');
    }

    /**
     * Устанавливает тип компонента
     *
     * @param string
     * @return void
     * @access protected
     */
    final protected function setType($type) {
        $this->type = $type;
        if (in_array($type, array(self::COMPONENT_TYPE_FORM_ADD, self::COMPONENT_TYPE_FORM_ALTER ))) {
            $type = self::COMPONENT_TYPE_FORM;
        }
        $this->setProperty('type', $type);
    }

    /**
     * Возвращает тип компонента
     *
     * @return string
     * @access protected
     */
    final protected function getType() {
        return $this->type;
    }

    /**
     * Устанавливает название компонента
     *
     * @param string $title
     */
    final protected function setTitle($title) {
        $this->setProperty('title', $title);
    }

    /**
     * Возвращает название компонента
     *
     * @return string
     */
    final protected function getTitle() {
        return $this->getProperty('title');
    }

    /**
     * Добавляет переводы
     *
     * @return void
     * @access protected
     * @final
     */

    final protected function addTranslation($tag) {
        array_push($this->translations, $tag);
    }
    /**
     * Создает tab
     *
     * @param string
     * @param array
     * @return DOMNode
     * @access protected
     */

    protected function buildTab($tabName, $tabProperties = false) {
        $tabXML	= $this->doc->createElement('tab');
        $tabXML->setAttribute('name', $tabName);
        if ($tabProperties)
        foreach ($tabProperties as $key => $value) {
            $tabXML->setAttribute($key, $value);
        }
        return $tabXML;
    }

    /**
     * Добавляет вкладку к перечню вкладок
     *
     * @param $tab DOMNode
     * @return void
     * @access protected
     * @final
     */

    final protected function addTab(DOMNode $tab) {
        array_push($this->tabs, $tab);
    }

    /**
     * Метод возвращает файл
     *
     * @param $data string данные файла
     * @param $MIMEType string тип файла
     * @param $fileName string имя файла
     *
     * @return void
     * @access protected
     * @final
     */

    final protected function downloadFile($data, $MIMEType, $fileName) {
        $this->response->setHeader('Content-Type', $MIMEType);
        $this->response->setHeader('Content-Disposition', ': attachment; filename="'.$fileName.'"');
        $this->response->write($data);
        $this->response->commit();
    }
    /**
     * Чистка от лишних и вердоносных html тегов
     * Вызывается в single режиме
     *
     * @return void
     * @access protected
     */

    protected function cleanup() {
        $data = isset($_POST['data']) ? $_POST['data'] : '';
        $data = self::cleanupHTML($data);
        $this->response->setHeader('Content-Type', 'application/xml; charset=utf-8');
        $this->response->write($data);
        $this->response->commit();
    }

    /**
     * Удаляет потенциально опасный и лишний HTML код
     *
     * @param string
     * @return string
     * @access protected
     * @static
     */

    public static function cleanupHTML($data) {
        $aggressive = isset($_GET['aggressive']);
        $jewix = new Jevix();
        $jewix->cfgSetXHTMLMode(true);
        $jewix->cfgSetAutoBrMode(false);
        $jewix->cfgSetAutoLinkMode(false);
        $allowedTags = array(
            'a', 'abbr', 'acronym', 'address', 'b', 'big', 'blockquote', 'br', 'cite',
            'code', 'col', 'colgroup', 'dd', 'del', 'dfn', 'div', 'dl', 'dt', 'em',
            'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'hr', 'i', 'ins', 'kbd', 'li', 'ol',
            'p', 'q', 's', 'samp', 'small', 'span', 'strong', 'sub', 'sup',
            'table', 'tbody', 'td', 'tfoot', 'th', 'thead', 'tr', 'tt', 'u', 'ul', 'var'
        );
        
        if (!$aggressive) {
            $allowedTags = array_merge($allowedTags, array(
                'img',
                'object',
                'param',
                'embed',
                'pre',
                'map',
                'area'
            ));
        }
        $jewix->cfgAllowTags($allowedTags);
        
        
        $jewix->cfgAllowTagParams('table', array('cellpadding', 'cellspacing'));
        $jewix->cfgAllowTagParams('td', array('colspan', 'rowspan'));
        $jewix->cfgAllowTagParams('th', array('colspan', 'rowspan'));
        $jewix->cfgAllowTagParams('a', array('href', 'target'));
        //Заменяем все абсолютные ссылки на относительные
        $jewix->cfgSetAutoReplace(
            Request::getInstance()->getBasePath(), 
            ''
        );
        
        $jewix->cfgSetTagCutWithContent(array('script', 'iframe')); 
        if(!$aggressive){
        	array_walk($allowedTags, create_function('$element, $key, $jewix', '$jewix->cfgAllowTagParams($element, array("id", "class", "style"));'), $jewix);
        	$jewix->cfgAllowTagParams('img', 
	            array(
	                'align', 
	                'alt', 
	                'src', 
	                'vspace', 
	                'width',
	                'hspace',
	                'height',
	                'border'
	            )
            );
        }
        
        $errors = false;
        $data = $jewix->parse($data, $errors);
        
        return $data;
    }
}

