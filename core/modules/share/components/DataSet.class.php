<?php
/**
 * Содержит класс DataSet
 *
 * @package energine
 * @subpackage share
 * @author dr.Pavka
 * @copyright Energine 2006
 */


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
     * Панели инструментов
     *
     * @var array of Toolbar
     * @access private
     */
    private $toolbar = array();

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
     * Список страниц (pager)
     *
     * @var Pager
     */
    protected $pager;


    /**
     * Конструктор класса
     *
     * @return void
     */
    public function __construct($name, $module, array $params = null) {
        parent::__construct($name, $module, $params);
        $this->setType(self::COMPONENT_TYPE_FORM);
        if (!$this->getParam('recordsPerPage'))$this->setParam('recordsPerPage', Grid::RECORD_PER_PAGE);
        if ($this->getParam('template')) {
            $this->setProperty('template', $this->getParam('template') . '/');
        }
        if ($this->getParam('title'))
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
        $this->setProperty('state', '');
        return array_merge(
            parent::defineParams(),
            array(
                'datasetAction' => '',
                'recordsPerPage' => false,
                'title' => false,
                'template' => false,
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
     * Возвращает набор тулбаров
     *
     * @param string $toolbarName
     * @return mixed (array of ToolBar or Toolbar)
     * @access protected
     */

    protected function getToolbar($toolbarName = false) {
        $result = array();
        if (!$toolbarName) {
            $result = $this->toolbar;
        }
        elseif (isset($this->toolbar[$toolbarName])) {
            $result = $this->toolbar[$toolbarName];
        }

        return $result;
    }

    /**
     * Устанавливает объекты тулбара
     *
     * @param mixed
     * @return void
     * @access protected
     */

    protected function addToolbar($toolbar) {
        if (!is_array($toolbar)) {
            $toolbar = array($toolbar);
        }
        foreach ($toolbar as $tb)
            if ($tb instanceof Toolbar) {
                $this->toolbar[$tb->getName()] = $tb;
            }
            else {
                throw new SystemException('ERR_BAD_TOOLBAR', SystemException::ERR_DEVELOPER);
            }
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
        /*if ($this->dataDescription->isEmpty()) {
              //throw new SystemException('ERR_DEV_EMPTY_DATA_DESCRIPTION', SystemException::ERR_DEVELOPER, $this->getName());
          }*/

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

        $toolbars = $this->createToolbar();

        if (!empty($toolbars)) {
            $this->addToolbar($toolbars);
        }
        $this->js = $this->buildJS();
    }

    /**
     * Создает построитель
     *
     * @return AbstractBuilder
     * @access protected
     */
    protected function createBuilder() {
        if (!isset($this->builder) || !$this->builder)
            return new Builder($this->getTitle());
        else return $this->builder;
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
        if ($this->config->getCurrentStateConfig()) {
            $configDataDescriptionObject->loadXML($this->config->getCurrentStateConfig()->fields);
        }


        // внешнее описание данных
        $externalDataDescription = $this->loadDataDescription();
        if (is_null($externalDataDescription)) {
            throw new SystemException('ERR_DEV_LOAD_DATA_DESCR_IS_FUNCTION', SystemException::ERR_DEVELOPER);
        }

        // если существует внешнее описание данных - пересекаем с описанием из конфиг
        if ($externalDataDescription) {
            $externalDataDescriptionObject = new DataDescription();
            $externalDataDescriptionObject->load($externalDataDescription);
            $configDataDescriptionObject =
                    $configDataDescriptionObject->intersect($externalDataDescriptionObject);
        }

        return $configDataDescriptionObject;
    }

    /**
     * Создание панелей инструментов
     *
     * @return Toolbar[]
     * @access protected
     */
    protected function createToolbar() {
        $result = array();
        if ($config = $this->config->getCurrentStateConfig()) {
            foreach ($config->toolbar as $toolbarDescription) {
                $toolbarName = ((string) $toolbarDescription['name']) ?
                        (string) $toolbarDescription['name'] :
                        self::TB_PREFIX . $this->getName();

                $toolbar = new Toolbar($toolbarName);
                $toolbar->attachToComponent($this);
                $toolbar->loadXML($toolbarDescription);
                $toolbar->translate();
                $result[$toolbarName] = $toolbar;
            }
        }
        return $result;
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
            if ($this->isActive() &&
                    $this->getType() == self::COMPONENT_TYPE_LIST) {
                $actionParams = $this->getStateParams(true);
                if (!isset($actionParams['pageNumber']) ||
                        !($page = intval($actionParams['pageNumber']))) {
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
     * передает ему данные и описание данны
     *
     * @return DOMDocument
     * @access public
     */
    public function build() {
        if (!$this->getBuilder()) {
            throw new SystemException(
                'ERR_DEV_NO_BUILDER:' . $this->getName() . ': ' .
                        $this->getState(), SystemException::ERR_CRITICAL, $this->getName());
        }

        // передаем данные и описание данных построителю
        if ($this->getData() && method_exists($this->getBuilder(), 'setData')) {
            $this->getBuilder()->setData($this->getData());
        }

        if (method_exists($this->getBuilder(), 'setDataDescription'))
            $this->getBuilder()->setDataDescription($this->getDataDescription());

        // вызываем родительский метод построения
        $result = parent::build();


        if ($this->js) {
            $result->documentElement->appendChild($result->importNode($this->js, true));
        }
        $toolbars = $this->getToolbar();

        if (!empty($toolbars))
            foreach ($toolbars as $tb)
                if ($toolbar = $tb->build()) {
                    $result->documentElement->appendChild(
                        $result->importNode($toolbar, true)
                    );
                }
        if ($this->pager && $this->getType() == self::COMPONENT_TYPE_LIST &&
                $pagerData = $this->pager->build()) {
            $pager = $result->importNode($pagerData, true);
            $result->documentElement->appendChild($pager);
        }

        //Работа с константами переводов
        if (($methodConfig = $this->config->getCurrentStateConfig()) &&
                $methodConfig->translations) {
            foreach ($methodConfig->translations->translation as $translation) {
                $this->addTranslation((string) $translation['const']);
            }
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
        $result = new Data();

        if (is_array($data)) {
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
        if (($config = $this->config->getCurrentStateConfig()) &&
                $config->javascript) {
            $result = $this->doc->createElement('javascript');
            foreach ($config->javascript->object as $value) {
                $JSObjectXML = $this->doc->createElement('object');
                $JSObjectXML->setAttribute('name', $value['name']);
                $JSObjectXML->setAttribute('path', ($value['path']) ?
                        $value['path'] . '/' : '');
                $result->appendChild($JSObjectXML);
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
            $action = $this->request->getLangSegment() .
                    $this->request->getPath(Request::PATH_TEMPLATE, true) .
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
        if (in_array($type, array(self::COMPONENT_TYPE_FORM_ADD, self::COMPONENT_TYPE_FORM_ALTER))) {
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

    final protected function addTranslation() {
        foreach (func_get_args() as $tag) {
            $this->document->addTranslation($tag, $this);
        }

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
        $this->response->setHeader('Content-Disposition',
                ': attachment; filename="' . $fileName . '"');
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
     * Добавляет переводы для тулбара WYSIWYG
     * вызывается в потомках
     *
     * @return void
     * @access protected
     * @final
     */
    final protected function addWYSIWYGTranslations() {
        $translations = array(
            'BTN_ITALIC',
            'BTN_HREF',
            'BTN_UL',
            'BTN_OL',
            'BTN_ALIGN_LEFT',
            'TXT_PREVIEW',
            'BTN_FILE_LIBRARY',
            'BTN_INSERT_IMAGE',
            'BTN_VIEWSOURCE',
            'TXT_PREVIEW',
            'TXT_RESET',
            'TXT_H1',
            'TXT_H2',
            'TXT_H3',
            'TXT_H4',
            'TXT_H5',
            'TXT_H6',
            'TXT_ADDRESS',
            'BTN_SAVE',
            'BTN_BOLD',
            'BTN_ALIGN_CENTER',
            'BTN_ALIGN_RIGHT',
            'BTN_ALIGN_JUSTIFY',
        );
        call_user_func_array(array($this, 'addTranslation'), $translations);
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
        //Если подключено расширение tidy

        if (function_exists('tidy_get_output') && $aggressive) {
            try {
                $tidy = new tidy();
                $config = array(
                    'bare' => true,
                    'drop-font-tags' => true,
                    'drop-proprietary-attributes' => true,
                    'hide-comments' => true,
                    'logical-emphasis' => true,
                    'numeric-entities' => true,
                    'show-body-only' => true,
                    'quote-nbsp' => false,
                    'indent' => 'auto',
                    'wrap' => 72,
                    'output-html' => true,
                );
                //if ($aggressive) {
                $config = array_merge(
                    $config,
                    array(
                        'clean' => true,
                        'word-2000' => true,
                        'drop-empty-paras' => true
                    )
                );
                //}
                $data = $tidy->repairString($data, $config, 'utf8');

            }
            catch (Exception $dummyError) {
                //inspect($dummyError);
            }
            unset($tidy);

        }

/*
		$jewix = new Jevix();
		$jewix->cfgSetXHTMLMode(true);
		$jewix->cfgSetAutoBrMode(false);
		$jewix->cfgSetAutoLinkMode(false);

		$shortTags  = array('br', 'hr');
		$allowedTags = array(
            'a', 'abbr', 'acronym', 'address', 'b', 'big', 'blockquote', 'br', 'cite',
            'code', 'col', 'colgroup', 'dd', 'del', 'dfn', 'div', 'dl', 'dt', 'em',
            'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'hr', 'i', 'ins', 'kbd', 'li', 'ol',
            'p', 'q', 's', 'samp', 'small', 'span', 'strong', 'sub', 'sup','pre',
            'table', 'tbody', 'td', 'tfoot', 'th', 'thead', 'tr', 'tt', 'u', 'ul', 'var'
            );

            if (!$aggressive) {
            	$allowedTags = array_merge($allowedTags, array(
                'img',
                'object',
                'param',
                'embed',
                'map',
                'area'
                ));
                array_push($shortTags, 'img');
            }
            $jewix->cfgAllowTags($allowedTags);
            $jewix->cfgSetTagShort($shortTags);

            $jewix->cfgSetTagNoTypography(array('code', 'pre', 'blockquote'));
            $jewix->cfgSetTagPreformatted(array('code', 'pre', 'blockquote'));

            $jewix->cfgAllowTagParams('table', array('cellpadding', 'cellspacing'));
            $jewix->cfgAllowTagParams('td', array('colspan', 'rowspan'));
            $jewix->cfgAllowTagParams('th', array('colspan', 'rowspan'));
            $jewix->cfgAllowTagParams('a', array('href', 'target'));


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
 *
 */
        $base = E()->getSiteManager()->getCurrentSite()->base;
        $data =
                str_replace(
                    (strpos($data, '%7E')) ? str_replace('~', '%7E', $base) : $base,
                    '',
                    $data
                );
        //$data = str_replace('&amp;', '&', $data);
        // stop($data);
        return $data;
    }
}

