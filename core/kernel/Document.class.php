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
    public function __construct($segments) {
        parent::__construct();
        $this->user = AuthUser::getInstance();
        $this->language = Language::getInstance();
        $this->lang = $this->language->getCurrent();
        $this->sitemap = Sitemap::getInstance();
        $this->request = Request::getInstance();
        $this->componentManager = new ComponentManager($this);
        // получаем идентификатор документа
        if (isset($segments[0]) && $segments[0] == self::SINGLE_SEGMENT) $segments = array();
        $this->id = $this->sitemap->getIDByURI($segments);
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
        //$this->loadComponents($this->documentInfo['templateID']);

        // устанавливаем свойства документа
        $this->setProperty('keywords', $this->documentInfo['MetaKeywords']);
        $this->setProperty('description', $this->documentInfo['MetaDescription']);
        $this->setProperty('ID', $this->getID());
	    $this->setProperty('default', $this->sitemap->getDefault()==$this->getID());
	    if(($verifyCode = $this->getConfigValue('google.verify')) && !empty($verifyCode)){
	    	$this->setProperty('google_verify', $verifyCode);
	    }
        if(($this->getRights() != ACCESS_FULL) && ($analyticsCode = $this->getConfigValue('google.analytics')) && !empty($analyticsCode)){
	    	if ($analyticsCode instanceof SimpleXMLElement){
                $analyticsCode = $analyticsCode->children();
                $analyticsCode = $analyticsCode[0]->asXML();
            }
            $this->setProperty('google_analytics', $analyticsCode);
	    }
	    unset($verifyCode);
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
        
        //Дополнительные свойства, имеющие параметры
        $prop = $this->doc->createElement('property', SiteManager::getInstance()->getCurrentSite()->base);
        $prop ->setAttribute('name', 'base');
        $prop ->setAttribute('folder', SiteManager::getInstance()->getCurrentSite()->folder);
        $dom_documentProperties->appendChild($prop);
        
        $prop = $this->doc->createElement('property', $this->getLang());
        $prop ->setAttribute('name', 'lang');
        $prop ->setAttribute('abbr', $this->request->getLangSegment());
        $prop ->setAttribute('real_abbr', Language::getInstance()->getAbbrByID($this->getLang()));
        $dom_documentProperties->appendChild($prop);
        unset($prop);
        /*
        $dom_layout = $this->doc->createElement('layout');
        $dom_layout->setAttribute('file', $this->documentInfo['layoutFileName']);
        $dom_content = $this->doc->createElement('content');
        $dom_content->setAttribute('file', $this->documentInfo['contentFileName']);

        $dom_root->appendChild($dom_layout);
        $dom_root->appendChild($dom_content);
*/


        foreach ($this->componentManager as $component) {
            $componentResult = false;
            $dom_errors = false;
            try {
                if($component instanceof Component) {
                    if($component->enabled() && $this->getRights()>= $component->getMethodRights()){
                        $componentResult = $component->build();
                    }
                }
                else {
                    $componentResult = $component->build();
                }
            }
            catch (DummyException $dummyException){
            }

            if (!empty($componentResult)) {
                try{
                $componentResult = $this->doc->importNode(
                $componentResult->documentElement,
                true
                );
                }
                        catch(Exception $e){
                            stop($e->getTraceAsString());
                }
                if ($dom_errors) {
                    $componentResult->insertBefore($dom_errors, $componentResult->firstChild);
                }
                $dom_root->appendChild($componentResult);
            }
            elseif ($dom_errors) {
                $dom_root->appendChild($dom_errors);
            }
        }
        
            if (!empty($this->translations)) {
            $dom_translations = $this->doc->createElement('translations');
            $dom_root->appendChild($dom_translations);
        
            foreach ($this->translations as $const => $componentName) {
                $dom_translation = $this->doc->createElement('translation', $this->translate($const));
                $dom_translation->setAttribute('const', $const);
                if(!is_null($componentName)){
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
        $this->documentInfo['layoutFileName'] = self::TEMPLATES_DIR.'layout/'.$this->documentInfo['Layout'];
        $this->documentInfo['contentFileName'] = self::TEMPLATES_DIR.'content/'.$this->documentInfo['Content'];
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
                	$this->componentManager->addComponent(
                        $this->componentManager->createComponent(
                            'pageToolBar',
                            'share',
                            'DivisionEditor',
                            array('action' => 'showPageToolbar')
                        )
                    );
                }
                // существует ли запрошенный компонент среди компонентов страницы?
                else {
                    if(
                        !(
                            $blockDescription = ComponentManager::findBlockByName(
                                ComponentManager::getDescriptionFromFile($this->documentInfo['layoutFileName']),
                                $actionParams[1]
                            )
                        )
                    &&
                        !(
                            $blockDescription = ComponentManager::findBlockByName(
                                ComponentManager::getDescriptionFromFile($this->documentInfo['contentFileName']),
                                $actionParams[1]
                            )
                        )
                    ){
                        throw new SystemException('ERR_NO_SINGLE_COMPONENT', SystemException::ERR_CRITICAL, $actionParams[1]);
                    }
                    $this->componentManager->add(
                        ComponentManager::createBlockFromDescription($blockDescription)
                    );
                }
        }
        else {
            foreach(array(
                $this->documentInfo['layoutFileName'],
                $this->documentInfo['contentFileName']
            ) as $fileName){
                $this->componentManager->add(
                    ComponentManager::createBlockFromDescription(
                        ComponentManager::getDescriptionFromFile($fileName)
                    )
                );
            }
            /*
            * Добавляем к набору компонентов страницы
            * обязательные стандартные компоненты:
            *     - BreadCrumbs
            */
            $this->componentManager->addComponent($this->componentManager->createComponent('breadCrumbs', 'share', 'BreadCrumbs'));
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
                $block->run();
            /*
            * Запускаем определение текущего действия компонента
            * и загрузку конфигурационной информации.
            */
            //$component->getAction();

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
        $this->translations[$const] = (!is_null($component))?$component->getName():null;
    }
    
    public function isEditable(){
        return ($this->getConfigValue('site.debug'))?isset($_REQUEST['editMode']):isset($_POST['editMode']);	
    }
}