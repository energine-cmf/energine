<?php
/**
 * @file
 * DocumentController, ITransformer, IDocument.
 *
 * It contains the definition to:
 * @code
class DocumentController;
 * interface ITransformer;
 * interface IDocument;
 * @endcode
 *
 * @author 1m.dm
 * @copyright Energine 2006
 *
 * @version 1.0.0
 */
namespace Energine\share\gears;
use Energine\share\components\ErrorComponent;

/**
 * Transformer interface.
 * @code
interface ITransformer;
 * @endcode
 */
interface ITransformer {
    /**
     * Run transforming.
     * @return mixed
     */
    public function transform();

    /**
     * Set document, that will be transformed.
     * @param \DOMDocument $document Document, that will be transformed.
     */
    public function setDocument(\DOMDocument $document);

    /**
     * Set filename, that will be transformed.
     * @param string $transformerFilename File name.
     * @param bool $isAbsolutePath Is the path absolute?
     */
    public function setFileName($transformerFilename, $isAbsolutePath = false);
}

/**
 * Document interface.
 * @code
interface IDocument;
 * @endcode
 */
interface IDocument {
    /**
     * Build.
     */
    public function build();

    /**
     * Get result.
     * @return \DOMDocument
     */
    public function getResult();
}

/**
 * DocumentController class is responsible for preparing the environment and running Document object.
 * @code
class DocumentController;
 * @endcode
 */
class DocumentController extends Primitive {

    const ERROR_PAGE_FILE = 'error.layout.xml';
    /**
     * Constant string for transforming into the HTML.
     * @var string TRANSFORM_HTML
     */
    const TRANSFORM_HTML = 'html';

    /**
     * Constant string for transforming into the XML debugging view format.
     * @var string TRANSFORM_HTML
     */
    const TRANSFORM_DEBUG_XML = 'debug';

    /**
     * Constant string for transforming into the XML structure view format.
     * @var string TRANSFORM_HTML
     */
    const TRANSFORM_STRUCTURE_XML = 'struct';

    /**
     * Constant string for transforming into the JSON.
     * @var string TRANSFORM_HTML
     */
    const TRANSFORM_JSON = 'json';

    /**
     * Empty transformation.
     * @var string TRANSFORM_EMPTY
     * @note The main idea of this constant was lost. Perhaps this is for the cases to return raw XML without XSLT transformation.
     */
    const TRANSFORM_EMPTY = 'empty';

    /**
     * XSLT transformer.
     * @var XSLTTransformer $transformer
     */
    private static $transformer;

    /**
     * Run processing.
     * -# Prepare the environment
     * -# Create Document object and give him control
     * -# Transform XML-document
     */
    public function run() {
        try {
            $language = E()->getLanguage();
            $language->setCurrent($language->getIDByAbbr(E()->getRequest()->getLang(), true));
            unset($language);

            try {
                $document = E()->getDocument();
                $document->loadComponents([$this, 'getXMLStructure']);
                $document->runComponents();

                if (($p = sizeof($path = E()->getRequest()->getPath())) != ($o = E()->getRequest()->getUsedSegments())) {
                    throw new SystemException('ERR_404', SystemException::ERR_404, (string)E()->getRequest()->getURI());
                }

                $document->build();
            } catch (IRQ $int) {
                /**
                 * @var PageStructureDocument $document
                 */
                $document = E()->PageStructureDocument;
                $document->setStructure($int->getStructure());
                $document->build();
            } catch (SystemException $e) {
                if (!in_array($e->getCode(), [SystemException::ERR_403, SystemException::ERR_404])) {
                    throw $e;
                }
                /**
                 * Errors 404 & 403 goes here
                 */
                $document =E()->getDocument();
                //$document = new Document();

                $document->loadComponents(function () {
                    if (
                        !file_exists($errorPageFile = Document::TEMPLATES_DIR . implode(DIRECTORY_SEPARATOR, [Document::TMPL_LAYOUT, E()->getSiteManager()->getCurrentSite()->folder, self::ERROR_PAGE_FILE]))
                        &&
                        !file_exists($errorPageFile = Document::TEMPLATES_DIR . implode(DIRECTORY_SEPARATOR, [Document::TMPL_LAYOUT, self::ERROR_PAGE_FILE]))
                    ) {
                        throw new \RuntimeException('ERR_NO_ERROR_FILE');
                    }

                    if (!($resultDoc = simplexml_load_file($errorPageFile))) {
                        throw new \RuntimeException('ERR_BAD_ERROR_DOCUMENT');
                    }
                    $resultDoc = trim(preg_replace('/<\?xml\s.+?>/sm', '', $resultDoc->asXML()));
                    $result = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><structure>' . $resultDoc . '</structure>');

                    return $result;

                });

                /**
                 * if there is no error component on page - add it
                 */
                /**
                 * @var ErrorComponent $ec
                 */
                if (!($ec = $document->componentManager->getBlockByName('error'))) {

                    $ec = $document->componentManager->createComponent(
                        'error',
                        'Energine\share\components\ErrorComponent'
                    );
                    $document->componentManager->add($ec);
                }
                $ec->setError($e);

                $document->runComponents();

                $this->getTransformer()->setFileName($this->getConfigValue('document.errorTransformer', $this->getConfigValue('document.transformer')));
                $document->build();
            }
        } catch (\Exception $e) {
            $document = E()->ErrorDocument;
            $document->attachException($e)->build();
        }
        E()->getResponse()->write($this->transform($document));
    }

    public function getXMLStructure($documentID) {
        $result = [];
        $loadDataFromFile = function ($fileName, $type) {
            if (!($result = file_get_contents(
                Document::TEMPLATES_DIR .
                constant(
                    '\\Energine\\share\\gears\\Document::TMPL_' .
                    strtoupper($type)) .
                '/' . $fileName))
            ) {
                throw new SystemException('ERR_WRONG_' . strtoupper($type));
            }

            return $result;
        };
        $docData = E()->getMap()->getDocumentInfo($documentID);
        libxml_use_internal_errors(true);
        foreach ([Document::TMPL_LAYOUT, Document::TMPL_CONTENT] as $type) {
            //Если нет данных поле
            if (!$docData[ucfirst($type) . 'Xml']) {
                //Берем из файла
                $result[$type] = $loadDataFromFile($docData[ucfirst($type)], $type);
            } else {
                $result[$type] = $docData[ucfirst($type) . 'Xml'];
            }
            $result[$type] = trim(preg_replace('/<\?xml\s.+?>/sm', '', $result[$type]));
        }

        if ($result[Document::TMPL_LAYOUT] == ($resultDoc = preg_replace('/<content((?!\<).)*\/>/sm',
                $result[Document::TMPL_CONTENT], $result[Document::TMPL_LAYOUT]))
        ) {
            $resultDoc = $result[Document::TMPL_LAYOUT] . $result[Document::TMPL_CONTENT];
        }

        if (!$resultDoc = simplexml_load_string('<?xml version="1.0" encoding="UTF-8"?><structure>' . $resultDoc . '</structure>')) {
            list($simpleXMLError) = libxml_get_errors();
            throw new SystemException('ERR_BAD_STRUCTURE', SystemException::ERR_CRITICAL, $simpleXMLError->message);
        }

        return $resultDoc;
    }

    /**
     * Run transformation.
     * Transform XML-document in the output format and show the result to the client.
     * @param Document $document XML-document.
     * @return mixed
     */
    private function transform($document) {
        $this->getTransformer()->setDocument($document->getResult());
        $result = $this->getTransformer()->transform();

        return $result;
    }

    /**
     * Get transformer.
     * The transformer type depends on one of reserved parameters in the GET request.
     * @return ITransformer
     */
    public function getTransformer() {
        if (!isset(self::$transformer)) {
            $vm = $this->getViewMode();
            if ($vm == self::TRANSFORM_DEBUG_XML ||
                $vm == self::TRANSFORM_STRUCTURE_XML
            ) {
                self::$transformer = new XMLTransformer();
            } elseif ($vm == self::TRANSFORM_JSON) {
                self::$transformer = new JSONTransformer();
            } else {
                self::$transformer = new XSLTTransformer();
            }
        }

        return self::$transformer;
    }

    /**
     * Get the view mode.
     * @return string
     */
    public function getViewMode() {
        $result = self::TRANSFORM_HTML;

        if (isset($_GET[self::TRANSFORM_DEBUG_XML]) &&
            $this->getConfigValue('site.debug') ||
            $this->getConfigValue('site.asXML')
        ) {
            $result = self::TRANSFORM_DEBUG_XML;
        } elseif (isset($_GET[self::TRANSFORM_HTML])) {
            $result = self::TRANSFORM_HTML;
        } elseif (isset($_GET[self::TRANSFORM_STRUCTURE_XML])) {
            $result = self::TRANSFORM_STRUCTURE_XML;
        } elseif (isset($_GET[self::TRANSFORM_JSON]) ||
            (isset($_SERVER['HTTP_X_REQUEST']) &&
                (strtolower($_SERVER['HTTP_X_REQUEST']) ==
                    self::TRANSFORM_JSON))
        ) {
            $result = self::TRANSFORM_JSON;
        }

        return $result;
    }
}
