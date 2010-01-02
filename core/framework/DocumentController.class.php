<?php

/**
 * Класс DocumentController.
 *
 * @package energine
 * @subpackage core
 * @author 1m.dm
 * @copyright Energine 2006
 */

/**
 * Отвечает за подготовку среды и запуск работы объекта Document.
 *
 * @package energine
 * @subpackage core
 * @author 1m.dm
 * @final
 */
final class DocumentController extends Object {
    /**
     * 
     * @var Transformer
     */
	private static $transformer;
	/**
	 * 
	 * @var DocumentController
	 */
	private static $instance;

    /**
     * Конструктор класса.
     *
     * @access public
     * @return void
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Возвращает единый для всей системы экземпляр класса DocumentController
     *
     * @access public
     * @static
     * @return DocumentController
     */
    static public function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new DocumentController();
        }
        return self::$instance;
    }

    /**
     * Подготавливает среду для работы, создаёт объект Document и передаёт
     * ему управление. После отработки объекта Document, запускает трансформацию
     * XML-документа страницы в выходной формат.
     *
     * @access public
     * @return void
     */
    public function run() {
        $request = Request::getInstance();
        $language = Language::getInstance();
        $language->setCurrent($language->getIDByAbbr($request->getLang(), true));

        // уберём за собой
        unset($request);
        unset($language);
        //unset($sitemap);
        $document = new Document(Request::getInstance()->getPath());
       /*
        * Если в каком-либо компоненте происходит ошибка, не позволяющая ему
        * продолжить работу, генерируется фиктивное исключение, с помощью
        * которого прерывается работа компонента. В дальнейшем, при вызове
        * метода Document::build, происходит обработка всех возникших ошибок.
        */
        try {
            $document->runComponents();
        }
        catch (DummyException $dummyException) {}
        $document->build();
        
        $this->transform($document);
    }
    /**
     * Возвращает объект  - XSLT трансформатор
     *
     * @return Transformer
     */
    public function getTransformer(){
    	if(!isset(self::$transformer)){
    		self::$transformer = new Transformer();
    	}

        return self::$transformer;
    }


    /**
     * Трансформирует XML-документ страницы в выходной формат,
     * и выводит результат клиенту.
     *
     * @param $document Document
     * @access private
     * @return void
     */
    private function transform($document) {
        $response = Response::getInstance();
        $dom_document = $document->getResult();
        if (!($dom_document instanceof DOMDocument)) {
            throw new SystemException('ERR_BAD_DOCUMENT', SystemException::ERR_CRITICAL);
        }

        if ((isset($_GET['debug']) && $this->getConfigValue('site.debug')) || $this->getConfigValue('site.asXML')) {
            $result = trim($dom_document->saveXML());
            $response->setHeader('Content-Type', 'text/xml; charset=UTF-8');
        }
        else {
            $result = $this->getTransformer()->transform($dom_document);
          	$response->setHeader('Content-Type', 'text/html; charset=UTF-8', false);
        }

        $response->write($result);
        $response->commit();
    }
}