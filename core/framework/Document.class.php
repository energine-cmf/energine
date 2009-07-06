<?php

/**
 * Класс Document.
 *
 * @package energine
 * @subpackage core
 * @author dr.Pavka
 * @copyright Energine 2006
 * @version $Id$
 */

//require_once('core/framework/DBWorker.class.php');
//require_once('core/framework/AuthUser.class.php');
//require_once('core/framework/Language.class.php');
//require_once('core/framework/Sitemap.class.php');
//require_once('core/framework/Request.class.php');
//require_once('core/framework/Response.class.php');
//require_once('core/framework/ComponentManager.class.php');

/**
 * Документ страницы.
 *
 * @package energine
 * @subpackage core
 * @author dr.Pavka
 * @final
 */
final class Document extends DBWorker {
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
     * @var DOMDocument результирующий DOM-документ
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
     * Перечень компонентов layout'а
     *
     * @var array
     * @access private
     */
    private $layoutComponents = array();

    /**
     * Перечень компонентов content'а
     *
     * @var array
     * @access private
     */
    private $contentComponents = array();

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
        $this->user = AuthUser::getInstance();
        $this->language = Language::getInstance();
        $this->lang = $this->language->getCurrent();
        $this->sitemap = Sitemap::getInstance();
        $this->request = Request::getInstance();
        $this->componentManager = new ComponentManager($this);
//inspect(Request::getInstance()->getRootPath());
        // получаем идентификатор документа
        $segments = $this->request->getPath();
        if (isset($segments[0]) && $segments[0] == self::SINGLE_SEGMENT) $segments = array();
        $this->id = $this->sitemap->getIDByURI($segments, true);
        if (empty($this->id)) {
            throw new SystemException('ERR_404', SystemException::ERR_404);
        }

        // получаем права пользователя на документ
        $this->rights = $this->sitemap->getDocumentRights($this->getID(), $this->user->getGroups());
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
		if(!empty($this->documentInfo['RedirectUrl'])){
			Response::getInstance()->setStatus('301');
			Response::getInstance()->setRedirect($this->documentInfo['RedirectUrl']);
		}
        // загружаем компоненты страницы
        $this->loadComponents($this->documentInfo['templateID']);

        // устанавливаем свойства документа
        $this->setProperty('base', $this->request->getBasePath());
        $this->setProperty('keywords', $this->documentInfo['MetaKeywords']);
        $this->setProperty('description', $this->documentInfo['MetaDescription']);
        $this->setProperty('ID', $this->getID());
        $this->setProperty('final', $this->documentInfo['isFinal']);
	    $this->setProperty('default', $this->sitemap->getDefault()==$this->getID());

        /*
        * Если в каком-либо компоненте происходит ошибка, не позволяющая ему
        * продолжить работу, генерируется фиктивное исключение, с помощью
        * которого прерывается работа компонента. В дальнейшем, при вызове
        * метода Document::build, происходит обработка всех возникших ошибок.
        */
        try {
            $this->runComponents();
        }
        catch (DummyException $dummyException) {}
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
        $this->doc = new DOMDocument('1.0', 'UTF-8');
        $dom_root = $this->doc->createElement('document');
        $dom_root->setAttribute('debug', $this->getConfigValue('site.debug'));
        $this->setProperty('url', (string)$this->request->getURI());

        $this->doc->appendChild($dom_root);
        if (!isset($this->properties['title'])) {
            $this->setProperty('title', $this->documentInfo['Name']);
        }
        $dom_documentProperties = $this->doc->createElement('properties');
        foreach ($this->properties as $propName => $propValue) {
            $dom_property = $this->doc->createElement('property', str_replace('&', '&amp;', $propValue));
            $dom_property->setAttribute('name', $propName);
            if ($propName == 'title') {
                $dom_property->setAttribute('alt', $this->documentInfo['HtmlTitle']);
            }
            $dom_documentProperties->appendChild($dom_property);
        }
        $dom_root->appendChild($dom_documentProperties);

        $langProperty = $this->doc->createElement('property', $this->getLang());
        $langProperty ->setAttribute('name', 'lang');
        $langProperty ->setAttribute('abbr', $this->request->getLangSegment());
        $langProperty ->setAttribute('real_abbr', Language::getInstance()->getAbbrByID($this->getLang()));
        $dom_documentProperties->appendChild($langProperty);

        if (!empty($this->translations)) {
            $dom_translations = $this->doc->createElement('translations');
            foreach ($this->translations as $const => $value) {
            	$dom_translation = $this->doc->createElement('translation', $value);
            	$dom_translation->setAttribute('const', $const);
            	$dom_translations->appendChild($dom_translation);
            }
            $dom_root->appendChild($dom_translations);
        }


        $dom_layout = $this->doc->createElement('layout');
        $dom_layout->setAttribute('file', $this->documentInfo['layoutFileName']);
        $dom_content = $this->doc->createElement('content');
        $dom_content->setAttribute('file', $this->documentInfo['contentFileName']);

        $dom_root->appendChild($dom_layout);
        $dom_root->appendChild($dom_content);



        foreach ($this->componentManager->getComponents() as $componentInfo) {
            $component = $componentInfo['component'];

            $componentResult = false;
            $dom_errors = false;

            if ($this->getRights() >= $component->getMethodRights() && $component->enabled()) {
                try {
                        $componentResult = $component->build();
                }
                catch (DummyException $dummyException){}
            }

            if ($componentResult) {
                $componentResult = $this->doc->importNode(
                $componentResult->documentElement,
                true
                );

                if ($dom_errors) {
                    $componentResult->insertBefore($dom_errors, $componentResult->firstChild);
                }

                if ($componentInfo['file'] == $this->documentInfo['layoutFileName']) {
                	$dom_layout->appendChild($componentResult);
                }
                elseif ($componentInfo['file'] == $this->documentInfo['contentFileName']) {
                	$dom_content->appendChild($componentResult);
                }
                else {
                    $dom_root->appendChild($componentResult);
                }

            }
            elseif ($dom_errors) {
                $dom_root->appendChild($dom_errors);
            }
        }
    }

    /**
     * Определяет компоненты страницы и загружает их в менеджер компонентов.
     *
     * @access private
     * @param int $templateID идентификатор шаблона страницы
     * @return void
     * @todo Полный рефакторинг!
     */
    private function loadComponents($templateID) {
        // получаем информацию о шаблоне страницы
        $res = $this->dbh->select('share_templates', true, array('tmpl_id' => $templateID));
        if (!is_array($res)) {
            throw new SystemException('ERR_DEV_NO_TEMPLATE_INFO', SystemException::ERR_CRITICAL);
        }
        $templateInfo = $res[0];

        // определяем и загружаем описания content- и layout- частей страницы
        $this->documentInfo['layoutFileName'] = self::TEMPLATES_DIR.'layout/'.$templateInfo['tmpl_layout'];
        $this->documentInfo['contentFileName'] = self::TEMPLATES_DIR.'content/'.$templateInfo['tmpl_content'];

        // вызывается ли какой-либо компонент в single режиме?
        $actionParams = $this->request->getPath(Request::PATH_ACTION);
        if (sizeof($actionParams) > 1 && $actionParams[0] == self::SINGLE_SEGMENT) {
                /*
                * Устанавливаем смещение пути на количество существующих
                * сегментов + 1 зарезирвированный сегмент + 1 сегмент
                * имени компонента.
                */
                $this->request->setPathOffset($this->request->getPathOffset() + 2);
                $this->setProperty('single', 'single');
                if ($actionParams[1] == 'pageToolBar') {
                	$this->componentManager->addComponent($this->componentManager->createComponent('pageToolBar', 'share', 'DivisionEditor', array('action' => 'showPageToolbar')));
                }
                // существует ли запрошенный компонент среди компонентов страницы?
                elseif (
                    !$this->componentManager->loadComponentsFromFile($this->documentInfo['layoutFileName'], $actionParams[1])
                    && !$this->componentManager->loadComponentsFromFile($this->documentInfo['contentFileName'], $actionParams[1])
                ) {
                    throw new SystemException('ERR_NO_SINGLE_COMPONENT', SystemException::ERR_CRITICAL);
                }
        }
        else {
            $this->componentManager->loadComponentsFromFile($this->documentInfo['layoutFileName']);
            $this->componentManager->loadComponentsFromFile($this->documentInfo['contentFileName']);
            /*
            * Добавляем к набору компонентов страницы
            * обязательные стандартные компоненты:
            *     - ActionSet
            *     - BreadCrumbs
            */
            $this->componentManager->addComponent($this->componentManager->createComponent('pageToolBar', 'share', 'DivisionEditor', array('action' => 'showPageToolbar')));
            $this->componentManager->addComponent($this->componentManager->createComponent('breadCrumbs', 'share', 'BreadCrumbs'));
        }
    }

    /**
     * Запускает работу всех компонентов страницы.
     *
     * @access private
     * @return void
     */
    private function runComponents() {
        foreach ($this->componentManager->getComponents() as $componentInfo) {
            $component = $componentInfo['component'];
            /*
            * Запускаем определение текущего действия компонента
            * и загрузку конфигурационной информации.
            */
            //$component->getAction();

            // если у пользователя достаточно прав - запускаем работу компонента
            if ($this->getRights() >= $component->getMethodRights()) {
                $component->run();
            }
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
     * Возвращает абсолютный путь
     *
     * @return string
     * @access public
     */

    public function getSiteRoot() {
        return dirname($_SERVER['SCRIPT_FILENAME']);
    }

    /**
     * Добавляет константу перевода к документу
     *
     * @param string
     * @return void
     * @access public
     */

    public function addTranslation($const) {
        $this->translations[$const] = $this->translate($const);
    }
}