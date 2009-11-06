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

	private static $transformer;

	private static $instance;

    /**
     * @access private
     * @var Document экземпляр класса Document
     */
    private $document;

    /**
     * @access private
     * @var Response экземпляр класса Response (HTTP-ответ)
     */
    private $response;

    /**
     * Конструктор класса.
     *
     * @access public
     * @return void
     */
    public function __construct() {
        parent::__construct();
        $this->response = Response::getInstance();
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
        $this->document = new Document;
        $this->document->build();
        $this->transform();
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
     * @access private
     * @return void
     */
    private function transform() {
        $dom_document = $this->document->getResult();
        if (!($dom_document instanceof DOMDocument)) {
            throw new SystemException('ERR_BAD_DOCUMENT', SystemException::ERR_CRITICAL);
        }

        if ((isset($_GET['debug']) && $this->getConfigValue('site.debug')) || $this->getConfigValue('site.asXML')) {
            $result = trim($dom_document->saveXML());
            $this->response->setHeader('Content-Type', 'text/xml; charset=UTF-8');
        }
        else {
            $result = $this->getTransformer()->transform($dom_document);
          	$this->response->setHeader('Content-Type', 'text/html; charset=UTF-8', false);
        }

        $this->response->write($result);
        $this->response->commit();
    }
}