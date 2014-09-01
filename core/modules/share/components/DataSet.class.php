<?php
/**
 * @file
 * DataSet.
 *
 * It contains the definition to:
 * @code
abstract class DataSet;
@endcode
 *
 * @author dr.Pavka
 * @copyright Energine 2006
 *
 * @version 1.0.0
 */

namespace Energine\share\components;
use Energine\share\gears\Toolbar, Energine\share\gears\QAL,Energine\share\gears\Component, Energine\share\gears\SystemException, Energine\share\gears\DataDescription, Energine\share\gears\FieldDescription, Energine\share\gears\Data, Energine\share\gears\Builder, Energine\share\gears\Object, Energine\share\gears\SimpleBuilder, Energine\share\gears\DataSetConfig, Energine\share\gears\Pager;
/**
 * Abstract data set.
 *
 * @code
abstract class DataSet;
@endcode
 *
 * This is a parent class for form, list and tree; it contains methods to work with toolbar and data sets.
 *
 * @abstract
 */
abstract class DataSet extends Component {
    /**
     * File library.
     * @var FileRepository $fileLibrary
     */
    protected $fileLibrary;

    /**
     * Image manager.
     * @var ImageManager $imageManager
     */
    protected $imageManager;
    /**
     * Source.
     * @var TextBlockSource $source
     */
    private $source;

    /**
     * Component type: list.
     * @var string COMPONENT_TYPE_LIST
     */
    const COMPONENT_TYPE_LIST = 'list';

    /**
     * Component type: form.
     * @var string COMPONENT_TYPE_FORM
     */
    const COMPONENT_TYPE_FORM = 'form';

    /**
     * Form type: insert form.
     * @var string COMPONENT_TYPE_FORM_ADD
     */
    const COMPONENT_TYPE_FORM_ADD = QAL::INSERT;

    /**
     * Form type: edit form.
     * @var string COMPONENT_TYPE_FORM_ALTER
     */
    const COMPONENT_TYPE_FORM_ALTER = QAL::UPDATE;

    /**
     * Prefix for toolbar name.
     * @var string TB_PREFIX
     */
    const TB_PREFIX = 'toolbar_';

    /**
     * Data description.
     * @var DataDescription $dataDescription
     */
    private $dataDescription = false;

    /**
     * Data
     * @var Data $data
     */
    private $data = false;

    /**
     * Array of toolbars.
     * @var array $toolbar
     */
    private $toolbar = array();

    /**
     * JavaScript
     * @var \DOMNode $js
     */
    protected $js;

    /**
     * Component type.
     * @var string $type
     */
    private $type;

    /**
     * List of pages (pager).
     * @var Pager $pager
     */
    protected $pager;
    /**
     * Default amount of records per page.
     * @var int RECORD_PER_PAGE
     */
    const RECORD_PER_PAGE = 50;


    /**
     * @copydoc Component::__construct
     */
    public function __construct($name, $module, array $params = null) {
        parent::__construct($name, $module, $params);
        $this->setType(self::COMPONENT_TYPE_FORM);
        if (!$this->getParam('recordsPerPage')) $this->setParam('recordsPerPage', self::RECORD_PER_PAGE);
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
     * @copydoc Component::defineParams
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
     * Set Data object
     * @param Data $data
     * @final
     */
    final protected function setData(Data $data) {
        $this->data = $data;
    }

    /**
     * Get data.
     *
     * @return Data
     *
     */
    public function getData() {
        return $this->data;
    }

    /**
     * Get toolbar(s).
     *
     * @param string|bool $toolbarName Toolbar name.
     * @return Toolbar|array
     */
    protected function getToolbar($toolbarName = false) {
        $result = array();
        if (!$toolbarName) {
            $result = $this->toolbar;
        } elseif (isset($this->toolbar[$toolbarName])) {
            $result = $this->toolbar[$toolbarName];
        }

        return $result;
    }

    /**
     * Add toolbar.
     *
     * @param mixed $toolbar New toolbar.
     *
     * @throws SystemException 'ERR_BAD_TOOLBAR'
     */
    protected function addToolbar($toolbar) {
        if (!is_array($toolbar)) {
            $toolbar = array($toolbar);
        }
        foreach ($toolbar as $tb)
            if ($tb instanceof Toolbar) {
                $this->toolbar[$tb->getName()] = $tb;
            } else {
                throw new SystemException('ERR_BAD_TOOLBAR', SystemException::ERR_DEVELOPER);
            }
    }

    /**
     * Set data description.
     *
     * @final
     */
    final protected function setDataDescription(DataDescription $dataDescription) {
        $this->dataDescription = $dataDescription;
    }

    /**
     * Возвращает описание данных
     *
     * @return DataDescription
     * @final
     *
     * @throws SystemException 'ERR_DEV_NO_DATA_DESCRIPTION'
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
     * @copydoc Component::prepare
     */
    protected function prepare() {
        $this->setBuilder($this->createBuilder());

        //$this->setDataDescription($this->createDataDescription());
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
     * Create builder.
     *
     * @return AbstractBuilder
     */
    protected function createBuilder() {
        if (!isset($this->builder) || !$this->builder)
            return new Builder($this->getTitle());
        else return $this->builder;
    }

    /**
     * Create data description.
     *
     * @return DataDescription
     *
     * @throws SystemException 'ERR_DEV_LOAD_DATA_DESCR_IS_FUNCTION'
     */
    protected function createDataDescription() {
        // описание данных из конфигурации
        $configDataDescriptionObject = new DataDescription();
        if ($this->getConfig()->getCurrentStateConfig()) {
            $configDataDescriptionObject->loadXML($this->getConfig()->getCurrentStateConfig()->fields);
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
     * Create toolbar
     *
     * @return Toolbar|Toolbar[]
     */
    protected function createToolbar() {
        $result = array();
        if ($config = $this->getConfig()->getCurrentStateConfig()) {
            foreach ($config->toolbar as $toolbarDescription) {
                $toolbarName = ((string)$toolbarDescription['name']) ?
                    (string)$toolbarDescription['name'] :
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
     * Create pager.
     */
    protected function createPager() {
        $recordsPerPage = intval($this->getParam('recordsPerPage'));
        if ($recordsPerPage > 0) {
            $this->pager = new Pager($recordsPerPage);
            if ($this->isActive() &&
                $this->getType() == self::COMPONENT_TYPE_LIST
            ) {
                $actionParams = $this->getStateParams(true);
                if (!isset($actionParams['pageNumber']) ||
                    !($page = intval($actionParams['pageNumber']))
                ) {
                    $page = 1;
                }
                $this->pager->setCurrentPage($page);
            }

            $this->pager->setProperty('title', $this->translate('TXT_PAGES'));
        }
    }

    /**
     * Load data.
     *
     * @return mixed
     */
    protected function loadData() {
        return false;
    }

    /**
     * Load data description.
     * Use this to load external data description (not from configurations).
     *
     * @return mixed
     */
    protected function loadDataDescription() {
        return false;
    }

    /**
     * @copydoc IBlock::build
     *
     * @throws SystemException
     */
    public function build() {
        if ($this->getState() == 'fileLibrary') {
            $result = $this->fileLibrary->build();
        } elseif ($this->getState() == 'imageManager') {
            $result = $this->imageManager->build();
        } elseif ($this->getState() == 'source') {
            $result = $this->source->build();
        } else {
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
            if (
                $this->pager && $this->getType() == self::COMPONENT_TYPE_LIST
                &&
                $pagerData = $this->pager->build()
            ) {
                $pager = $result->importNode($pagerData, true);
                $result->documentElement->appendChild($pager);
            }

            //Работа с константами переводов
            if (($methodConfig = $this->getConfig()->getCurrentStateConfig()) &&
                $methodConfig->translations
            ) {
                foreach ($methodConfig->translations->translation as $translation) {
                    $this->addTranslation((string)$translation['const']);
                }
            }
        }
        return $result;
    }

    /**
     * @copydoc DBDataSet::getConfig
     */
    protected function getConfig() {
        if (!$this->config) {
            $this->config = new DataSetConfig(
                $this->getParam('config'),
                get_class($this),
                $this->module
            );
        }
        return $this->config;
    }

    /**
     * Create data.
     *
     * @return \Energine\share\gears\Data
     *
     * @throws SystemException 'ERR_DEV_LOAD_DATA_IS_FUNCTION'
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
     * Create description of JS objects.
     *
     * @return DOMNode
     */
    protected function buildJS() {
        $result = false;
        if (($config = $this->getConfig()->getCurrentStateConfig()) &&
            $config->javascript
        ) {
            $result = $this->doc->createElement('javascript');
            foreach ($config->javascript->behavior as $value) {
                $JSObjectXML = $this->doc->createElement('behavior');
                $JSObjectXML->setAttribute('name', $value['name']);
                $JSObjectXML->setAttribute('path', ($value['path']) ?
                    $value['path'] . '/' : '');
                $result->appendChild($JSObjectXML);
            }
            foreach ($config->javascript->variable as $value) {
                $JSObjectXML = $this->doc->createElement('variable');
                $JSObjectXML->setAttribute('name', $value['name']);
                $JSObjectXML->setAttribute('type', ($value['type']) ?
                    $value['type'] : 'string');
                $JSObjectXML->appendChild(new \DomText((string)$value));
                $result->appendChild($JSObjectXML);
            }
        }
        return $result;
    }

    /**
     * Set action for form processor.
     *
     * @param string $action Action name.
     * @param bool $isFullURI Is the URI full?
     *
     * @final
     */
    final protected function setAction($action, $isFullURI = false) {
        // если у нас не полностью сформированный путь, то добавляем информацию о языке + путь к шаблону
        if (!$isFullURI) {
            $action = $this->request->getLangSegment() .
                $this->request->getPath(\Energine\share\gears\Request::PATH_TEMPLATE, true) .
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
     * Get an address of form processor.
     *
     * @return string
     *
     * @final
     */
    final protected function getDataSetAction() {
        return $this->getParam('datasetAction');
    }

    /**
     * Set component type.
     *
     * @param string $type Component type.
     *
     * @final
     */
    final protected function setType($type) {
        $this->type = $type;
        if (in_array($type, array(self::COMPONENT_TYPE_FORM_ADD, self::COMPONENT_TYPE_FORM_ALTER))) {
            $type = self::COMPONENT_TYPE_FORM;
        }
        $this->setProperty('type', $type);
    }

    /**
     * Get component type.
     *
     * @return string
     *
     * @final
     */
    final protected function getType() {
        return $this->type;
    }

    /**
     * Set component title.
     *
     * @param string $title Title.
     *
     * @final
     */
    final protected function setTitle($title) {
        $this->setProperty('title', $title);
    }

    /**
     * Get component title.
     *
     * @return string
     *
     * @final
     */
    final protected function getTitle() {
        return $this->getProperty('title');
    }

    /**
     * Add translation.
     *
     * @final
     */
    final protected function addTranslation() {
        foreach (func_get_args() as $tag) {
            $this->document->addTranslation($tag, $this);
        }

    }

    /**
     * Download file.
     *
     * @param string $data File data.
     * @param string $MIMEType File type.
     * @param string $fileName Filename.
     *
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
     * Clean up.
     */
    protected function cleanup() {
        $data = isset($_POST['data']) ? $_POST['data'] : '';
        $data = self::cleanupHTML($data);
        $this->response->setHeader('Content-Type', 'text/html; charset=utf-8');
        $this->response->write($data);
        $this->response->commit();
    }

    /**
     * Add translations for WYSIWYG toolbar.
     *
     * @note It is called from children.
     *
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
            'BTN_INSERT_IMAGE_URL',
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
            'TXT_ERROR_NOT_VIDEO_FILE',
            'BTN_SAVE',
            'BTN_BOLD',
            'BTN_ALIGN_CENTER',
            'BTN_ALIGN_RIGHT',
            'BTN_ALIGN_JUSTIFY',
            'BTN_EXT_FLASH',
            'BTN_ACTIVATE'
        );
        call_user_func_array(array($this, 'addTranslation'), $translations);
    }

    /**
     * Get file library.
     */
    protected function fileLibrary() {
        $this->request->shiftPath(1);

        $this->fileLibrary = $this->document->componentManager->createComponent('filelibrary', 'share', 'FileRepository', array('config' => 'core/modules/share/config/FileRepositorySelect.component.xml'));

        $this->fileLibrary->run();
    }

    /**
     * Run source.
     */
    protected function source() {
        $this->source = $this->document->componentManager->createComponent('textblocksource', 'share', 'TextBlockSource', null);
        $this->source->run();
    }

    /**
     * Show image manager.
     */
    protected function imageManager() {
        $this->imageManager = $this->document->componentManager->createComponent('imagemanager', 'share', 'ImageManager', null);
        $this->imageManager->run();
    }

    /**
     * Player for embedding in text areas
     */
    protected function embedPlayer() {
        $sp = $this->getStateParams();
        list($uplId) = $sp;
        $fileInfo = $this->dbh->select(
            'share_uploads',
            array(
                'upl_path',
                'upl_name'
            ),
            array(
                'upl_id' => intval($uplId),
                'upl_internal_type' => \Energine\share\gears\FileRepoInfo::META_TYPE_VIDEO
            )
        );
        if(!is_array($fileInfo)) {
            throw new SystemException('ERROR_NO_VIDEO_FILE', SystemException::ERR_404);
        }
        // Using array_values to transform associative index to key index
        list($file, $name) = array_values($fileInfo[0]);
        $dd = new DataDescription();
        foreach(
            array(
                'file' => FieldDescription::FIELD_TYPE_STRING,
                'name' => FieldDescription::FIELD_TYPE_STRING
            ) as $fName => $fType
        ) {
            $fd = new FieldDescription($fName);
            $fd->setType($fType);
            $dd->addFieldDescription($fd);
        }
        $this->setBuilder(new SimpleBuilder());
        $this->setDataDescription($dd);
        $data = new Data();
        $data->load(
          array(
              array(
                  'file' => $file,
                  'name' => $name
              )
          )
        );
        $this->setData($data);
        $this->js = $this->buildJS();
        E()->getController()->getTransformer()->setFileName('core/modules/share/transformers/embed_player.xslt', true);
    }

    /**
     * Remove malicious and redundant HTML code.
     *
     * @param string $data Data.
     * @return string
     */
    public static function cleanupHTML($data) {
        $aggressive = Object::_getConfigValue('site.aggressive_cleanup', false);

        //Если подключено расширение tidy
        if (function_exists('tidy_get_output') && $aggressive) {
            try {
                $tidy = new \tidy();
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
                        //'clean' => true,
                        'word-2000' => true,
                        'drop-empty-paras' => true
                    )
                );
                //}
                $data = $tidy->repairString($data, $config, 'utf8');

            } catch (\Exception $dummyError) {
                //inspect($dummyError);
            }
            unset($tidy);

        }

        $base = E()->getSiteManager()->getCurrentSite()->base;
        $data = str_replace((strpos($data, '%7E')) ? str_replace('~', '%7E', $base) : $base, '', $data);
        return $data;
    }
}

