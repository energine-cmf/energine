<?php

/**
 * Класс DocumentController  и  интерфейс Transformer.
 *
 * @package energine
 * @subpackage kernel
 * @author 1m.dm
 * @copyright Energine 2006
 */

/**
 * Отвечает за подготовку среды и запуск работы объекта Document.
 *
 * @package energine
 * @subpackage kernel
 * @author 1m.dm
 */
class DocumentController extends Object {
    const TRANSFORM_HTML = 'html';
    const TRANSFORM_DEBUG_XML = 'debug';
    const TRANSFORM_STRUCTURE_XML = 'struct';
    const TRANSFORM_JSON = 'json';
    /**
     * ХЗ вообще что это такое?
     * Наверное была какая то идея
     * но в процессе разработки - потерялась
     *
     * @upd
     * Похоже это для случая когда нам нужно вернуть XML без накладывания XSLT Трансформации
     */
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
                $this->getConfigValue('site.asXML')
        ) {
            $result = self::TRANSFORM_DEBUG_XML;
        }
        elseif (isset($_GET[self::TRANSFORM_HTML])) {
            $result = self::TRANSFORM_HTML;
        }
        elseif (isset($_GET[self::TRANSFORM_STRUCTURE_XML])) {
            $result = self::TRANSFORM_STRUCTURE_XML;
        }
        elseif (isset($_GET[self::TRANSFORM_JSON]) ||
                (isset($_SERVER['HTTP_X_REQUEST']) &&
                        (strtolower($_SERVER['HTTP_X_REQUEST']) ==
                                self::TRANSFORM_JSON))
        ) {
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

            if(($p = sizeof($path = E()->getRequest()->getPath())) != ($o = E()->getRequest()->getUsedSegments())){
//                dump_log('URL: '.implode('/', $path). ' Path: '.$p.' Offset: '.$o, true);
                throw new SystemException('ERR_404', SystemException::ERR_404, (string)E()->getRequest()->getURI());
            }

            $document->build();
        }
        catch (IRQ $int) {
            $document = E()->PageStructureDocument;
            if ($l = $int->getLayoutBlock()) {
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
    public function getTransformer() {
        if (!isset(self::$transformer)) {
            $vm = $this->getViewMode();
            if ($vm == self::TRANSFORM_DEBUG_XML ||
                    $vm == self::TRANSFORM_STRUCTURE_XML
            ) {
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
    private function transform($document) {
        $this->getTransformer()->setDocument($document->getResult());
        $result = $this->getTransformer()->transform();
        return $result;
    }
}

interface ITransformer {
    public function transform();

    public function setDocument(DOMDocument $document);

    public function setFileName($transformerFilename, $isAbsolutePath = false);
}

interface IDocument {
    public function build();

    public function getResult();
}
