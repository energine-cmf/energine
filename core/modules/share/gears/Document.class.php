<?php
/**
 * @file.
 * Document.
 *
 * It contains the definition to:
 * @code
final class Document;
@endcode
 *
 * @author dr.Pavka
 * @copyright Energine 2006
 *
 * @version 1.0.0
 */
namespace Energine\share\gears;
use Energine\share\components as cmp;
/**
 * Page document.
 *
 * @code
final class Document;
@endcode
 *
 * @final
 */
final class Document extends DBWorker implements IDocument {
    /**
     * Reserved URL segment for 'single'-mode
     * @var string SINGLE_SEGMENT
     */
    const SINGLE_SEGMENT = 'single';

    /**
     * Path to the directory with templates.
     * @var string TEMPLATES_DIR
     */
    const TEMPLATES_DIR = 'templates/';

    /**
     * Name of the BreadCrumbs default class
     * @var string
     */
    private $breadCrumbsClass = 'BreadCrumbs';

    /**
     * Document ID
     * @var int $id
     */
    private $id = false;

    /**
     * Document language ID.
     * @var int $lang
     */
    private $lang;

    /**
     * Info about system language.
     * @var Language $language
     */
    protected $language;

    /**
     * Site map.
     * @var Sitemap $sitemap
     */
    protected $sitemap;

    /**
     * Request.
     * @var Request $request
     */
    private $request;

    /**
     * Component manager.
     * @var ComponentManager $componentManager
     */
    public $componentManager;

    /**
     * Result document.
     * @var \DOMDocument $doc
     */
    private $doc;

    /**
     * User rights for document.
     * Rights:
     * - ACCESS_NONE = 0
     * - ACCESS_READ = 1
     * - ACCESS_EDIT = 2
     * - ACCESS_FULL = 3
     *
     * @var int $rights
     */
    private $rights = false;

    /**
     * Document properties.
     * @var array $properties
     */
    private $properties = array();

    /**
     * Exemplar of the AuthUser class.
     * @var AuthUser $user
     */
    public $user;

    /**
     * Document information.
     * @var array $documentInfo
     * @see Sitemap::getDocumentInfo()
     */
    private $documentInfo = array();

    /**
     * Array of constants for translations.
     *
     * @var array $translations
     */
    private $translations = array();

    public function __construct() {
        parent::__construct();
        $this->user = E()->getAUser();
        $this->language = E()->getLanguage();
        $this->lang = $this->language->getCurrent();
        $this->sitemap = E()->getMap();
        $this->request = E()->getRequest();
        $segments = $this->request->getPath();
        $this->componentManager = new ComponentManager($this);
        // получаем идентификатор документа
        if (isset($segments[0]) && $segments[0] == self::SINGLE_SEGMENT)
            $segments = array();
        $this->id = $this->sitemap->getIDByURI($segments);
        if (empty($this->id)) {
            throw new SystemException('ERR_404', SystemException::ERR_404);
        }

        // получаем права пользователя на документ
        $this->rights =
            $this->sitemap->getDocumentRights($this->getID(), $this->user->getGroups());
        if ($this->getRights() == ACCESS_NONE) {
            throw new SystemException('ERR_403', SystemException::ERR_403);
        }

        // получаем информацию о документе
        $this->documentInfo = $this->sitemap->getDocumentInfo($this->getID());
        //Если его нет в перечне страниц значит он IsDisabled
        if (!$this->documentInfo) {
            throw new SystemException('ERR_404', SystemException::ERR_404);
        }
        //Если URL редиректа не пустой  - осуществляем редирект по нему
        if (!empty($this->documentInfo['RedirectUrl'])) {
            E()->getResponse()->setStatus('301');
            E()->getResponse()->setRedirect(Response::prepareRedirectURL($this->documentInfo['RedirectUrl']));
        }
        // загружаем компоненты страницы
        //$this->loadComponents($this->documentInfo['templateID']);

        // устанавливаем свойства документа
        $this->setProperty('keywords', $this->documentInfo['MetaKeywords']);
        $this->setProperty('description', $this->documentInfo['MetaDescription']);
        $this->setProperty('robots', $this->documentInfo['MetaRobots']);
        $this->setProperty('ID', $this->getID());
        $this->setProperty('default',
            $this->sitemap->getDefault() == $this->getID());

        //Если сайт - индексируемый
        $currentSite = E()->getSiteManager()->getCurrentSite();
        if ($currentSite->isIndexed) {
            //и сущестует код гугловерификации
            if (($verifyCode = $this->getConfigValue('google.verify')) &&
                !empty($verifyCode)
            ) {
                //то выводим его
                $this->setProperty('google_verify', $verifyCode);
            }
            if (($analyticsCode = $currentSite->gaCode) || (($analyticsCode = $this->getConfigValue('google.analytics')) &&
                    !empty($analyticsCode))
            ) {
                $this->setProperty('google_analytics', $analyticsCode);
            }
        }
    }

    /**
     * Get document ID.
     *
     * @return int
     */
    public function getID() {
        return $this->id;
    }

    /**
     * Get language ID.
     *
     * @return int
     */
    public function getLang() {
        return $this->lang;
    }

    /**
     * Setting breadCrumbs class name
     * It gives the possibilty to add custom BreadCrumbs class
     *
     * @param string $breadCrumbsClass
     * @throws SystemException
     */
    public function setBreadCrumbs($breadCrumbsClass){
        if(!class_parents($breadCrumbsClass)){
            throw new SystemException('ERR_BAD_BREADCRUMBS_CLASS', SystemException::ERR_DEVELOPER, $breadCrumbsClass);
        }
        $this->breadCrumbsClass = $breadCrumbsClass;
    }

    /**
     * Building resulte XML Document
     */
    public function build() {
        //Если у нас не режим json

        $this->doc = new \DOMDocument('1.0', 'UTF-8');
        $dom_root = $this->doc->createElement('document');
        $dom_root->setAttribute('debug', $this->getConfigValue('site.debug'));
        $dom_root->setAttribute('editable', $this->isEditable());
        $this->setProperty('url', (string)$this->request->getURI());

        $this->doc->appendChild($dom_root);
        if (!isset($this->properties['title'])) {
            $this->setProperty('title', strip_tags($this->documentInfo['Name']));
        }
        $dom_documentProperties = $this->doc->createElement('properties');
        foreach ($this->properties as $propName => $propValue) {
            $dom_property =
                $this->doc->createElement('property', str_replace('&', '&amp;', $propValue));
            $dom_property->setAttribute('name', $propName);
            if ($propName == 'title') {
                $dom_property->setAttribute('alt', $this->documentInfo['HtmlTitle']);
            }
            $dom_documentProperties->appendChild($dom_property);
        }
        $dom_root->appendChild($dom_documentProperties);

        //Дополнительные свойства, имеющие параметры
        $prop = $this->doc->createElement('property', (
        $baseURL = E()->getSiteManager()->getCurrentSite()->base));
        $prop->setAttribute('name', 'base');
        $prop->setAttribute('static', (($staticURL =
            $this->getConfigValue('site.static')) ? $staticURL : $baseURL));
        $prop->setAttribute('media', (($mediaURL =
            $this->getConfigValue('site.media')) ? $mediaURL : $baseURL));
        $prop->setAttribute('resizer', (($resizerURL =
            $this->getConfigValue('site.resizer')) ? $resizerURL : (E()->getSiteManager()->getDefaultSite()->base . 'resizer/')));
        $prop->setAttribute('folder', E()->getSiteManager()->getCurrentSite()->folder);
        $prop->setAttribute('default', E()->getSiteManager()->getDefaultSite()->base);
        $dom_documentProperties->appendChild($prop);

        $prop = $this->doc->createElement('property', $this->getLang());
        $prop->setAttribute('name', 'lang');
        $prop->setAttribute('abbr', $this->request->getLangSegment());
        $prop->setAttribute('default', E()->getLanguage()->getDefault());
        $prop->setAttribute('real_abbr', E()->getLanguage()->getAbbrByID($this->getLang()));
        $dom_documentProperties->appendChild($prop);

        if (($docVars = $this->getConfigValue('site.vars')) && is_array($docVars)) {
            $dom_documentVars = $this->doc->createElement('variables');
            foreach ($docVars as $varName => $varValue) {
                $var = $this->doc->createElement('var', $varValue);
                $var->setAttribute('name', strtoupper($varName));
                $dom_documentVars->appendChild($var);
            }
            $dom_root->appendChild($dom_documentVars);
        }
        if ($og = E()->getOGObject()->build()) {
            $dom_root->appendChild($this->doc->importNode(
                $og,
                true
            ));

        }


        unset($prop, $staticURL, $baseURL, $og);

        foreach ($this->componentManager as $component) {
            $componentResult = false;
            $dom_errors = false;
            try {
                if ($component->enabled()
                    &&
                    ($this->getRights() >= $component->getCurrentStateRights())
                ) {

                    $componentResult = $component->build();
                }
            } catch (DummyException $dummyException) {
            }

            if (!empty($componentResult)) {
                try {
                    $componentResult = $this->doc->importNode(
                        $componentResult->documentElement,
                        true
                    );
                } catch (\Exception $e) {
                    //stop($e->getTraceAsString());
                }
                if ($dom_errors) {
                    $componentResult->insertBefore($dom_errors, $componentResult->firstChild);
                }
                $dom_root->appendChild($componentResult);
            } elseif ($dom_errors) {
                $dom_root->appendChild($dom_errors);
            }
        }

        if (!empty($this->translations)) {
            $dom_translations = $this->doc->createElement('translations');
            $dom_root->appendChild($dom_translations);

            foreach ($this->translations as $const => $componentName) {
                $dom_translation =
                    $this->doc->createElement('translation', $this->translate($const));
                $dom_translation->setAttribute('const', $const);
                if (!is_null($componentName)) {
                    $dom_translation->setAttribute('component', $componentName);
                }
                $dom_translations->appendChild($dom_translation);
            }
        }

        // построение списка подключаемых js библиотек в порядке зависимостей
        $jsmap_file = HTDOCS_DIR . '/system.jsmap.php';

        if (file_exists($jsmap_file)) {

            $js_includes = array();

            $jsmap = include($jsmap_file);

            $xpath = new \DOMXPath($this->doc);
            $nl = $xpath->query('//javascript/behavior');

            if ($nl->length) {
                foreach ($nl as $node) {
                    $cls_path = $node->getAttribute('path');
                    if ($cls_path && substr($cls_path, -1) != '/') {
                        $cls_path .= '/';
                    }
                    $cls = (($cls_path) ? $cls_path : '') . $node->getAttribute('name');
                    $this->createJavascriptDependencies(array($cls), $jsmap, $js_includes);
                }
            }

            $dom_javascript = $this->doc->createElement('javascript');
            foreach ($js_includes as $js) {
                $dom_js_library = $this->doc->createElement('library');
                $dom_js_library->setAttribute('path', $js);
                $dom_javascript->appendChild($dom_js_library);
            }
            $dom_root->appendChild($dom_javascript);
        }
    }

    /**
     * Create unique flat array of connected .js-files and their dependencies.
     *
     * @param array $dependencies Dependencies.
     * @param array $jsmap JS map.
     * @param array $js_includes JS includes.
     */
    protected function createJavascriptDependencies($dependencies, $jsmap, &$js_includes) {
        if ($dependencies) {
            foreach ($dependencies as $dep) {
                if (isset($jsmap[$dep]))
                    $this->createJavascriptDependencies($jsmap[$dep], $jsmap, $js_includes);

                if (!in_array($dep, $js_includes)) {
                    $js_includes[] = $dep;
                }
            }
        }
    }

    /**
     * Define and load page components into the ComponentManager.
     *
     * @todo Полный рефакторинг!
     */
    public function loadComponents() {
        // определяем и загружаем описания content- и layout- частей страницы
        $templateData = Document::getTemplatesData($this->getID());
        $contentXML = $templateData->content;
        $layoutXML = $templateData->layout;
        $contentFile = $templateData->contentFile;
        $layoutFile = $templateData->layoutFile;
        unset($templateData);

        // вызывается ли компонент в single режиме?
        $actionParams = $this->request->getPath(Request::PATH_ACTION);
        if (sizeof($actionParams) > 1 &&
            $actionParams[0] == self::SINGLE_SEGMENT
        ) {
            /*
            * Устанавливаем смещение пути на количество существующих
            * сегментов + 1 зарезирвированный сегмент + 1 сегмент
            * имени компонента.
            */
            $this->request->setPathOffset($this->request->getPathOffset() + 2);
            $this->setProperty('single', 'single');
            if ($actionParams[1] == 'pageToolBar') {
                $this->componentManager->add(
                    $this->componentManager->createComponent(
                        'pageToolBar',
                        'share',
                        'DivisionEditor',
                        array('state' => 'showPageToolbar')
                    )
                );
            } // существует ли запрошенный компонент среди компонентов страницы?
            else {
                if (
                    !(
                    $blockDescription = ComponentManager::findBlockByName(
                        $layoutXML,
                        $actionParams[1]
                    )
                    )
                    &&
                    !(
                    $blockDescription = ComponentManager::findBlockByName(
                        $contentXML,
                        $actionParams[1]
                    )
                    )
                ) {
                    throw new SystemException('ERR_NO_SINGLE_COMPONENT', SystemException::ERR_CRITICAL, $actionParams[1]);
                }
                if (E()->getController()->getViewMode() ==
                    DocumentController::TRANSFORM_STRUCTURE_XML
                ) {
                    $int = new IRQ();
                    $int->addBlock($blockDescription);
                    throw $int;
                }
                $this->componentManager->add(
                    ComponentManager::createBlockFromDescription($blockDescription)
                );
            }

        } else {
            if (E()->getController()->getViewMode() ==
                DocumentController::TRANSFORM_STRUCTURE_XML
            ) {
                $int = new IRQ();
                $int->addBlock($layoutXML);
                $int->addBlock($contentXML);
                throw $int;
            }

            foreach (array(
                         $layoutXML,
                         $contentXML
                     ) as $XML) {
                $this->componentManager->add(
                    ComponentManager::createBlockFromDescription($XML, array('file' => ($XML == $contentXML)
                            ? $contentFile : $layoutFile))
                );

            }
            /*
            * Добавляем к набору компонентов страницы
            * обязательные стандартные компоненты:
            *     - BreadCrumbs
            */
            $this->componentManager->add($this->componentManager->createComponent('breadCrumbs', 'share', $this->breadCrumbsClass));
            //Если пользователь не авторизован и авторизационный домен не включает текущеий домен - то добавляем компонент для кроссдоменной авторизации
            if (
                !$this->user->isAuthenticated()
                &&
                (strpos(E()->getSiteManager()->getCurrentSite()->host, $this->getConfigValue('site.domain')) === false)
            )
                $this->componentManager->add(
                    $this->componentManager->createComponent('cdAuth', 'share', 'CrossDomainAuth')
                );
        }

    }

    /**
     * Run all components.
     */
    public function runComponents() {
        foreach ($this->componentManager as $block) {
            //$block->run();
            if (
                $block->enabled()
                &&
                ($this->getRights() >= $block->getCurrentStateRights())
            ) {
                $block->run();
            }
            /*
            * Запускаем определение текущего действия компонента
            * и загрузку конфигурационной информации.
            */
            //$component->getState();

            // если у пользователя достаточно прав - запускаем работу компонента

        }
    }

    public function getResult() {
        return $this->doc;
    }

    /**
     * Get current user.
     *
     * @return AuthUser
     */
    public function getUser() {
        return $this->user;
    }

    /**
     * Get user rights.
     *
     * @see Document::$rights
     *
     * @return int
     */
    public function getRights() {
        return $this->rights;
    }

    /**
     * Set document property.
     *
     * @param string $propName Property name.
     * @param string $propValue Property value.
     */
    public function setProperty($propName, $propValue) {
        $this->properties[$propName] = $propValue;
    }

    /**
     * Get document property.
     *
     * @param string $propName Property name.
     * @return string|false
     */
    public function getProperty($propName) {
        if (isset($this->properties[$propName])) {
            return $this->properties[$propName];
        }
        return false;
    }

    /**
     * Remove property.
     *
     * @param string $propName Property name.
     */
    public function removeProperty($propName) {
        if (isset($this->properties[$propName])) {
            unset($this->properties[$propName]);
        }
    }

    /**
     * Add translation constant.
     *
     * @param string $const Translation constant
     * @param Component $component Component object.
     */
    public function addTranslation($const, Component $component = null) {
        $this->translations[$const] =
            (!is_null($component)) ? $component->getName() : null;
    }

    /**
     * Check if the component editable.
     *
     * @return bool
     */
    public function isEditable() {
        if ($this->getRights() > 2) {
            return ($this->getConfigValue('site.debug')) ? isset($_REQUEST['editMode']) : isset($_POST['editMode']);
        } else {
            return false;
        }
    }

    /**
     * Get the information about document XML-code.
     * If the value 'xml' is missed or incorrect then it will try to load XML from the file.
     *
     * @param int $documentID Document ID.
     * @return object
     *
     * @throws SystemException 'ERR_WRONG_[type]'
     * @throws SystemException 'ERR_BAD_DOC_ID'
     */
    static public function getTemplatesData($documentID) {
        $loadDataFromFile = function ($fileName, $type) {
            if (!($result = simplexml_load_string(file_get_contents_stripped(
                Document::TEMPLATES_DIR .
                constant(
                    'Energine\\share\\components\\DivisionEditor::TMPL_' .
                    strtoupper($type)) .
                '/' . $fileName)))
            ) {
                throw new SystemException('ERR_WRONG_' . strtoupper($type));
            }
            return $result;
        };
        if (!($templateData =
            E()->getDB()->select('share_sitemap', array('smap_content_xml as content', 'smap_layout_xml as layout', 'smap_content as content_file', 'smap_layout as layout_file'), array('smap_id' => $documentID)))
        ) {
            throw new SystemException('ERR_BAD_DOC_ID');
        }
        list($templateData) = $templateData;

        libxml_use_internal_errors(true);
        foreach (array(cmp\DivisionEditor::TMPL_LAYOUT, cmp\DivisionEditor::TMPL_CONTENT) as $type) {

            //Если нет данных поле
            if (!$templateData[$type]) {
                //Берем из файла
                $templateData[$type] = $loadDataFromFile($templateData[$type .
                '_file'], $type);
            } else {
                //Если есть данные в поле
                //Пытаемся распарсить
                if (!($templateData[$type] =
                    simplexml_load_string(stripslashes($templateData[$type])))
                ) {
                    //Если не удалось - берем из файла
                    $templateData[$type] = $loadDataFromFile($templateData[$type .
                    '_file'], $type);
                    //и очищаем 
                    E()->getDB()->modify(QAL::UPDATE, 'share_sitemap', array(
                        'smap_' .
                        $type .
                        '_xml' => ''), array('smap_id' => $documentID));
                }
            }
            $templateData[$type . 'File'] = $templateData[$type . '_file'];
            unset($templateData[$type . '_file']);
        }

        return (object)$templateData;
    }

    public function getXMLDocument(){
        return $this->doc;
    }
}