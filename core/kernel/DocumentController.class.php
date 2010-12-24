<?php

/**
 * Класс DocumentController  и  интерфейс Transformer.
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
 */
class DocumentController extends Object {
    const TRANSFORM_HTML = 'html';
    const TRANSFORM_DEBUG_XML = 'debug';
    const TRANSFORM_STRUCTURE_XML = 'struct';
    const TRANSFORM_JSON = 'json';
    const TRANSFORM_EMPTY = 'empty';
    /**
     * 
     * @var XSLTTransformer
     */
	private static $transformer;

    public function getViewMode() {
        $result = self::TRANSFORM_HTML;

        if (isset($_GET[self::TRANSFORM_DEBUG_XML]) &&
                $this->getConfigValue('site.debug') ||
                $this->getConfigValue('site.asXML')) {
            $result = self::TRANSFORM_DEBUG_XML;
        }
        elseif (isset($_GET[self::TRANSFORM_STRUCTURE_XML])) {
            $result = self::TRANSFORM_STRUCTURE_XML;
        }
        elseif (isset($_GET[self::TRANSFORM_JSON]) ||
                (isset($_SERVER['HTTP_X_REQUEST']) &&
                        (strtolower($_SERVER['HTTP_X_REQUEST']) ==
                                self::TRANSFORM_JSON))) {
            $result = self::TRANSFORM_JSON;
        }

        return $result;
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
        $language = E()->getLanguage();
        $language->setCurrent($language->getIDByAbbr(E()->getRequest()->getLang(), true));
        unset($language);

        try {
        $document = E()->getDocument();
        $document->loadComponents();
            $document->runComponents();
            $document->build();
        }
        catch (IRQ $int) {
            $document = E()->PageStructureDocument;
            if($l = $int->getLayoutBlock()){
                $document->setLayout($l);
            }
            $document->setContent($int->getContentBlock());
        $document->build();
        }
        catch (SystemException $e) {
            $document = E()->ErrorDocument;
            $document->attachException($e);
            $document->build();
        }
        E()->getResponse()->write($this->transform($document));
    }

    /**
     * Возвращает объект  - трансформатор
     * Тип трасформатора зависит от переданного в GET одного из зарезервированных параметров
     *
     * @return ITransformer
     */
    public function getTransformer(){
    	if(!isset(self::$transformer)){
            $vm = $this->getViewMode();
            if ($vm == self::TRANSFORM_DEBUG_XML ||
                    $vm == self::TRANSFORM_STRUCTURE_XML) {
                self::$transformer = new XMLTransformer();
    	}
            elseif ($vm == self::TRANSFORM_JSON) {
                self::$transformer = new JSONTransformer();
            }
            else {
                self::$transformer = new XSLTTransformer();
            }
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
    private
    function transform($document) {
        $this->getTransformer()->setDocument($document->getResult());
        $result = $this->getTransformer()->transform();
        return $result;
        }
}

interface ITransformer {
    public function transform();

    public function setDocument(DOMDocument $document);
        }

interface IDocument {
    public function build();

    public function getResult();
    }
