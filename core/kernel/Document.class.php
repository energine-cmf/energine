<?php

/**
 * Класс Document.
 *
 * @package energine
 * @subpackage kernel
 * @author dr.Pavka
 * @copyright Energine 2006
 */

/**
 * Документ страницы.
 *
 * @package energine
 * @subpackage kernel
 * @author dr.Pavka
 * @final
 */
final class Document extends DBWorker implements IDocument {
    /**
     * Зарезервированный сегмент URL для single-режима
     */
    const SINGLE_SEGMENT = 'single';

    /**
     * Путь к директории с конфигурационными шаблонами
     */
    const TEMPLATES_DIR = 'templates/';

    /**
     * @access private
     * @var int идентификатор документа
     */
    private $id = false;

    /**
     * @access private
     * @var int идентификатор языка документа
     */
    private $lang;

    /**
     * @access protected
     * @var Language информация о языках системы
     */
    protected $language;

    /**
     * @access protected
     * @var Sitemap карта сайта
     */
    protected $sitemap;

    /**
     * @access private
     * @var Request
     */
    private $request;

    /**
     * @access public
     * @var ComponentManager менеджер компонентов
     */
    public $componentManager;

    /**
     * @access private
     * @var DOMDocument результирующий документ
     */
    private $doc;

    /**
     * @access private
     * @var int права пользователя на документ
     */
    private $rights = false;

    /**
     * @access private
     * @var array свойства документа
     */
    private $properties = array();

    /**
     * @access public
     * @var AuthUser экземпляр класса AuthUser
     * @see AuthUser
     */
    public $user;

    /**
     * @access private
     * @var array информация о документе
     * @see Sitemap::getDocumentInfo()
     */
    private $documentInfo = array();

    /**
     * Массив констант для перевода
     *
     * @var array
     * @access private
     */
    private $translations = array();

    /**
     * Конструктор класса.
     *
     * @access public
     * @return void
     */
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
     * Возвращает идентификатор документа.
     *
     * @access public
     * @return int
     */
    public function getID() {
        return $this->id;
    }

    /**
     * Возвращает идентификатор языка документа.
     *
     * @access public
     * @return int
     */
    public function getLang() {
        return $this->lang;
    }

    /**
     * Запускает построение компонентов страницы и возвращает результат в виде
     * собранного DOM-документа страницы.
     *
     * @access public
     * @return DOMDocument
     */
    public function build() {
        //Если у нас не режим json

        $this->doc = new DOMDocument('1.0', 'UTF-8');
        $dom_root = $this->doc->createElement('document');
        $dom_root->setAttribute('debug', $this->getConfigValue('site.debug'));
        $dom_root->setAttribute('editable', $this->isEditable());
        $this->setProperty('url', (string)$this->request->getURI());

        $this->doc->appendChild($dom_root);
        if (!isset($this->properties['title'])) {
            $this->setProperty('title', $this->documentInfo['Name']);
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
            $this->getConfigValue('site.resizer')) ? $resizerURL : $baseURL));
        $prop->setAttribute('folder', E()->getSiteManager()->getCurrentSite()->folder);
        $prop->setAttribute('default', E()->getSiteManager()->getDefaultSite()->base);
        $dom_documentProperties->appendChild($prop);

        $prop = $this->doc->createElement('property', $this->getLang());
        $prop->setAttribute('name', 'lang');
        $prop->setAttribute('abbr', $this->request->getLangSegment());
        $prop->setAttribute('default', E()->getLanguage()->getDefault());
        $prop->setAttribute('real_abbr', E()->getLanguage()->getAbbrByID($this->getLang()));
        $dom_documentProperties->appendChild($prop);
        unset($prop, $staticURL, $baseURL);

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
                } catch (Exception $e) {
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
    }

    /**
     * Определяет компоненты страницы и загружает их в менеджер компонентов.
     *
     * @access public
     *
     * @return void
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
                $this->componentManager->addComponent(
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
            $this->componentManager->add($this->componentManager->createComponent('breadCrumbs', 'share', 'BreadCrumbs'));
            //Если пользователь не авторизован и авторизационный домен не включает текущеий домен - то добавляем компонент для кроссдоменной авторизации
            if(
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
     * Запускает работу всех компонентов страницы.
     *
     * @access public
     * @return void
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
     * Возвращает результирующий DOM-документ.
     *
     * @access public
     * @return DOMDocument
     */
    public function getResult() {
        return $this->doc;
    }

    /**
     * Возвращает объект текущего пользователя.
     *
     * @access public
     * @return AuthUser
     */
    public function getUser() {
        return $this->user;
    }

    /**
     * Возвращает права пользователя на документ.
     *
     * one of [ACCESS_NONE=0, ACCESS_READ=1, ACCESS_EDIT=2, ACCESS_FULL=3]
     *
     * @access public
     * @return int
     */
    public function getRights() {
        return $this->rights;
    }

    /**
     * Устанавливает значение свойства документа.
     *
     * @access public
     * @param string $propName
     * @param string $propValue
     * @return void
     */
    public function setProperty($propName, $propValue) {
        $this->properties[$propName] = $propValue;
    }

    /**
     * Возвращает значение свойства документа.
     *
     * @access public
     * @param string $propName
     * @return string
     */
    public function getProperty($propName) {
        if (isset($this->properties[$propName])) {
            return $this->properties[$propName];
        }
        return false;
    }

    /**
     * Удаляет свойство документа.
     *
     * @access protected
     * @param string $propName
     * @return void
     */
    protected function removeProperty($propName) {
        if (isset($this->properties[$propName])) {
            unset($this->properties[$propName]);
        }
    }

    /**
     * Добавляет константу перевода к документу
     *
     * @param string
     * @param Component
     *
     * @return void
     * @access public
     */

    public function addTranslation($const, Component $component = null) {
        $this->translations[$const] =
            (!is_null($component)) ? $component->getName() : null;
    }

    public function isEditable() {
        if ($this->getRights() > 2) {
            return ($this->getConfigValue('site.debug')) ? isset($_REQUEST['editMode']) : isset($_POST['editMode']);
        } else {
            return false;
        }
    }

    /**
     * Возвращает информацию о xml коде документа
     * Если значение xml отсутствует или неверно  - пытается загрузить XML из соответсвующего файла
     * @static
     * @throws SystemException
     * @param  $documentID int Идентификатор документа
     * @return object
     */
    static public function getTemplatesData($documentID) {
        $loadDataFromFile = function($fileName, $type) {
            if (!($result = simplexml_load_string(file_get_contents_stripped(
                Document::TEMPLATES_DIR .
                    constant(
                        'DivisionEditor::TMPL_' .
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
        foreach (array(DivisionEditor::TMPL_LAYOUT, DivisionEditor::TMPL_CONTENT) as $type) {
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
}