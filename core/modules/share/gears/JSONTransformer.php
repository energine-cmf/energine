<?php
/**
 * @file
 * JSONTransformer.
 *
 * It contains the definition to:
 * @code
class JSONTransformer;
@endcode
 *
 * @author pavka
 * @copyright Energine 2010
 *
 * @version 1.0.0
 */
namespace Energine\share\gears;
/**
 * JSON Transformer.
 *
 * @code
class JSONTransformer;
@endcode
 */
class JSONTransformer implements ITransformer {
    /**
     * DOM document.
     * @var \DOMDocument $document
     */
    private $document;

    public function setDocument(\DOMDocument $document) {
        $this->document = $document;
    }

    public function transform() {
        E()->getResponse()->setHeader('Content-Type', 'text/javascript; charset=utf-8');
        $component = $this->document->getElementById('result');
        if (!$component) {
            throw new SystemException('ERR_BAD_OPERATION_RESULT', SystemException::ERR_CRITICAL, $this->document->saveHTML());
        }
        return $component->nodeValue;
    }

    /**
     * @copydoc ITransformer::setFileName
     *
     * @throws SystemException 'ERR_UNIMPLEMENTED'
     *
     * @note This is used for compatibility with debug mode.
     */
    public function setFileName($transformerFilename, $isAbsolutePath = false) {
        throw new SystemException('ERR_UNIMPLEMENTED', SystemException::ERR_DEVELOPER);
    }
}


