<?php
/**
 * @file.
 * Document.
 * It contains the definition to:
 * @code
final class Document;
 * @endcode
 * @author dr.Pavka
 * @copyright Energine 2006
 * @version 1.0.0
 */
namespace Energine\share\gears;

use Energine\share\components\Component;

/**
 * Page document.
 * @code
final class Document;
 * @endcode
 * @final
 */
final class Document extends Primitive implements IDocument {
    use DBWorker;
    /**
     * Template content.
     * @var string TMPL_CONTENT
     */
    const TMPL_CONTENT = 'content';
    /**
     * Template layout.
     * @var string TMPL_LAYOUT
     */
    const TMPL_LAYOUT = 'layout';

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
     * Component manager.
     * @var ComponentManager $componentManager
     */
    public $componentManager;
    /**
     * Exemplar of the AuthUser class.
     * @var AuthUser $user
     */
    public $user;
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
     * Name of the BreadCrumbs default class
     * @var string
     */
    private $breadCrumbsClass = 'Energine\share\components\BreadCrumbs';
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
     * Request.
     * @var Request $request
     */
    private $request;
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
     * @var int $rights
     */
    private $rights = false;
    /**
     * Document properties.
     * @var array $properties
     */
    private $properties = [];
    /**
     * Document information.
     * @var array $documentInfo
     * @see Sitemap::getDocumentInfo()
     */
    private $documentInfo = [];

    /**
     * Array of constants for translations.
     * @var array $translations
     */
    private $translations = [];
    /**
     * Array of document javascripts (js behaviours included into content/layout files)
     *
     * @var \DOMElement[]
     */
    private $js = [];

    public function __construct() {
        parent::__construct();
        $this->user = E()->getUser();
        $this->language = E()->getLanguage();
        $this->lang = $this->language->getCurrent();
        $this->sitemap = E()->getMap();
        $this->request = E()->getRequest();
        $segments = $this->request->getPath();
        $this->componentManager = new ComponentManager($this);
        // получаем идентификатор документа
        if (isset($segments[0]) && $segments[0] == self::SINGLE_SEGMENT) {
            $segments = [];
        }
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
            E()->getResponse()->setRedirect($this->documentInfo['RedirectUrl']);
        }
        // загружаем компоненты страницы
        //$this->loadComponents($this->documentInfo['templateID']);

        // устанавливаем свойства документа
        if ($this->documentInfo['MetaKeywords'])
            $this->setProperty('keywords', $this->documentInfo['MetaKeywords']);
        if ($this->documentInfo['MetaDescription'])
            $this->setProperty('description', $this->documentInfo['MetaDescription']);

        if ($this->documentInfo['MetaRobots'])
            $this->setProperty('robots', implode(',', $this->documentInfo['MetaRobots']));
        $this->setProperty('template', $this->request->getPath(Request::PATH_TEMPLATE, true));
        $this->setProperty('ID', $this->getID());
        $this->setProperty('default',
            $this->sitemap->getDefault() == $this->getID());
    }

    /**
     * Get document ID.
     * @return int
     */
    public function getID() {
        return $this->id;
    }

    /**
     * Get user rights.
     * @see Document::$rights
     * @return int
     */
    public function getRights() {
        return $this->rights;
    }

    /**
     * Set document property.
     * @param string $propName Property name.
     * @param string $propValue Property value.
     */
    public function setProperty($propName, $propValue) {
        $this->properties[$propName] = $propValue;
    }

    /**
     * Setting breadCrumbs class name
     * It gives the possibilty to add custom BreadCrumbs class
     * @param string $breadCrumbsClass
     * @throws SystemException
     */
    public function setBreadCrumbs($breadCrumbsClass) {
        if (!class_parents($breadCrumbsClass)) {
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
            $this->getConfigValue('site.static') ? $staticURL : $baseURL)));
        $prop->setAttribute('media', (($mediaURL =
            $this->getConfigValue('site.media')) ? $mediaURL : $baseURL));
        $prop->setAttribute('resizer', (($resizerURL =
            $this->getConfigValue('site.resizer')) ? $resizerURL : (E()->getSiteManager()->getDefaultSite()->base . 'resizer/')));
        $prop->setAttribute('folder', E()->getSiteManager()->getCurrentSite()->folder);
        $prop->setAttribute('default', E()->getSiteManager()->getDefaultSite()->base);
        $prop->setAttribute('favicon',
            ($favicon = E()->getSiteManager()->getCurrentSite()->faviconFile) ? $favicon : E()->getSiteManager()->getDefaultSite()->faviconFile);
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
        unset($prop, $og);
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
                    $this->doc->createElement('translation', translate($const));
                $dom_translation->setAttribute('const', $const);
                if (!is_null($componentName)) {
                    $dom_translation->setAttribute('component', $componentName);
                }
                $dom_translations->appendChild($dom_translation);
            }
        }

        $jsLibs = Primitive::getConfigValue('site.js-lib');
        if (!isset($jsLibs['mootools'])) {
            $jsLibs['mootools'] = $staticURL . 'scripts/mootools.min.js';
        }
        if (!isset($jsLibs['jquery'])) {
            $jsLibs['jquery'] = 'https://ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js';
        }
        $dom_javascript = $this->doc->createElement('javascript');

        foreach ($jsLibs as $name => $path) {
            $dom_javascript->setAttribute($name, $path);
        }
        $dom_root->appendChild($dom_javascript);
        foreach ($this->js as $behavior) {
            $dom_javascript->appendChild($this->doc->importNode($behavior, true));
        }

        // построение списка подключаемых js библиотек в порядке зависимостей
        $jsMapFile = HTDOCS_DIR . '/system.jsmap.php';
        if (!file_exists($jsMapFile)) {
            throw new \RuntimeException('JS dependencies file ' . $jsMapFile . ' does\'nt exists');
        }
        $jsIncludes = [];
        $jsmap = include($jsMapFile);

        $xpath = new \DOMXPath($this->doc);
        $nl = $xpath->query('//javascript/behavior');

        if ($nl->length) {
            foreach ($nl as $node) {
                $classPath = $node->getAttribute('path');
                if ($classPath && substr($classPath, -1) != '/') {
                    $classPath .= '/';
                }
                $cls = (($classPath) ? $classPath : '') . $node->getAttribute('name');

                $this->createJavascriptDependencies([$cls], $jsmap, $jsIncludes);
            }
        }
        foreach ($jsIncludes as $js) {
            $dom_js_library = $this->doc->createElement('library');
            $dom_js_library->setAttribute('path', $js);
            $onlyName = explode('/', $js);
            $dom_js_library->setAttribute('name', array_pop($onlyName));
            $dom_javascript->appendChild($dom_js_library);
        }

    }

    /**
     * Check if the component editable.
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
     * Get language ID.
     * @return int
     */
    public function getLang() {
        return $this->lang;
    }
    /**modbySD
     * Set language ID.
     */
    public function setLang($lang_id) {
        $this->lang=$lang_id;
    }
    /**
     * Create unique flat array of connected .js-files and their dependencies.
     * @param array $dependencies Dependencies.
     * @param array $jsmap JS map.
     * @param array $jsIncludes JS includes.
     */
    protected function createJavascriptDependencies($dependencies, $jsmap, &$jsIncludes) {
        if ($dependencies) {
            foreach ($dependencies as $dep) {
                if (isset($jsmap[$dep])) {
                    $this->createJavascriptDependencies($jsmap[$dep], $jsmap, $jsIncludes);
                }

                if (!in_array($dep, $jsIncludes)) {
                    $jsIncludes[] = $dep;
                }
            }
        }
    }

    public function getResult() {
        return $this->doc;
    }

    /**
     * Define and load page components into the ComponentManager.
     * @todo Полный рефакторинг!
     */
    public function loadComponents(callable $getStructure) {
        // определяем и загружаем описания content- и layout- частей страницы
        $structure = call_user_func($getStructure, $this->getID());
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
                        'Energine\share\components\DivisionEditor',
                        ['state' => 'showPageToolbar']
                    )
                );
            } // существует ли запрошенный компонент среди компонентов страницы?
            else {
                if (!($blockDescription = ComponentManager::findBlockByName(
                    $structure,
                    $actionParams[1]))
                ) {
                    throw new SystemException('ERR_NO_SINGLE_COMPONENT', SystemException::ERR_CRITICAL,
                        $actionParams[1]);
                }
                if (E()->getController()->getViewMode() ==
                    DocumentController::TRANSFORM_STRUCTURE_XML
                ) {
                    $int = new IRQ();
                    $int->setStructure($blockDescription);
                    throw $int;
                }

                if ($c = ComponentManager::createBlockFromDescription($blockDescription))
                    $this->componentManager->add($c);
            }

        } else {
            if (E()->getController()->getViewMode() ==
                DocumentController::TRANSFORM_STRUCTURE_XML
            ) {
                $int = new IRQ();
                $int->setStructure($structure);
                throw $int;
            }

            foreach ($structure->children() as $XML) {
                if ($c = ComponentManager::createBlockFromDescription($XML))
                    $this->componentManager->add($c);

            }
            /*
            * Добавляем к набору компонентов страницы
            * обязательные стандартные компоненты:
            *     - BreadCrumbs
            */
            $this->componentManager->add($this->componentManager->createComponent('breadCrumbs',
                $this->breadCrumbsClass));
            //Если пользователь не авторизован и авторизационный домен не включает текущеий домен - то добавляем компонент для кроссдоменной авторизации
            if (
                !$this->user->isAuthenticated()
                &&
                (strpos(E()->getSiteManager()->getCurrentSite()->host, $this->getConfigValue('site.domain')) === false)
            ) {
                $this->componentManager->add(
                    $this->componentManager->createComponent('cdAuth', 'Energine\share\components\CrossDomainAuth')
                );
            }
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

    /**
     * Get current user.
     * @return AuthUser
     */
    public function getUser() {
        return $this->user;
    }

    /**
     * Get document property.
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
     * @param string $propName Property name.
     */
    public function removeProperty($propName) {
        if (isset($this->properties[$propName])) {
            unset($this->properties[$propName]);
        }
    }

    /**
     * Add translation constant.
     * @param string $const Translation constant
     * @param Component $component Component object.
     */
    public function addTranslation($const, Component $component = NULL) {
        $this->translations[$const] =
            (!is_null($component)) ? $component->getName() : NULL;
    }

    /**
     * @param $behavior
     * @throws \DOMException
     */
    public function addJSBehavior($behavior) {
        if (is_a($behavior, '\SimpleXMLElement')) {
            if (!($bNode = dom_import_simplexml($behavior))) {
                throw new \DOMException('Bad JS behaviour.');
            }
            array_push($this->js, $bNode);
        }
    }


    public function getXMLDocument() {
        return $this->doc;
    }
}