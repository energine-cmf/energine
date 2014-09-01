<?php
/**
 * @file
 * DocumentController, ITransformer, IDocument.
 *
 * It contains the definition to:
 * @code
class DocumentController;
interface ITransformer;
interface IDocument;
@endcode
 *
 * @author 1m.dm
 * @copyright Energine 2006
 *
 * @version 1.0.0
 */
namespace Energine\share\gears;
/**
 * DocumentController class is responsible for preparing the environment and running Document object.
 *
 * @code
class DocumentController;
@endcode
 *
 */
class DocumentController extends Object {
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
     *
     * @note The main idea of this constant was lost. Perhaps this is for the cases to return raw XML without XSLT transformation.
     */
    const TRANSFORM_EMPTY = 'empty';

    /**
     * XSLT transformer.
     * @var XSLTTransformer $transformer
     */
    private static $transformer;

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
     * Run processing.
     *
     * -# Prepare the environment
     * -# Create Document object and give him control
     * -# Transform XML-document
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
     * Get transformer.
     * The transformer type depends on one of reserved parameters in the GET request.
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
     * Run transformation.
     * Transform XML-document in the output format and show the result to the client.
     *
     * @param Document $document XML-document.
     * @return mixed
     */
    private function transform($document) {
        $this->getTransformer()->setDocument($document->getResult());
        $result = $this->getTransformer()->transform();
        return $result;
    }
}

/**
 * Transformer interface.
 *
 * @code
interface ITransformer;
@endcode
 */
interface ITransformer {
    /**
     * Run transforming.
     * @return mixed
     */
    public function transform();

    /**
     * Set document, that will be transformed.
     *
     * @param \DOMDocument $document Document, that will be transformed.
     */
    public function setDocument(\DOMDocument $document);

    /**
     * Set filename, that will be transformed.
     *
     * @param string $transformerFilename File name.
     * @param bool $isAbsolutePath Is the path absolute?
     */
    public function setFileName($transformerFilename, $isAbsolutePath = false);
}

/**
 * Document interface.
 *
 * @code
interface IDocument;
@endcode
 */
interface IDocument {
    /**
     * Build.
     */
    public function build();

    /**
     * Get result.
     *
     * @return \DOMDocument
     */
    public function getResult();
}
