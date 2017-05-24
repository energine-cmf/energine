<?php
/**
 * @file
 * ErrorDocument.
 *
 * It contains the definition to:
 * @code
class ErrorDocument;
 * @endcode
 *
 * @author d.pavka
 * @copyright d.pavka@gmail.com
 *
 * @version 1.0.0
 */
namespace Energine\share\gears;
/**
 * Error document.
 *
 * @code
class ErrorDocument;
 * @endcode
 */
class ErrorDocument extends Primitive implements IDocument {
    /**
     * Document.
     * @var \DOMDocument $doc
     */
    private $doc;
    /**
     * Exception.
     * @var SystemException $e
     */
    private $e;

    /**
     * Attach exception.
     *
     * @param \Exception $e
     *
     * @see ErrorDocument::$e
     * @return ErrorDocument
     */
    public function attachException(\Exception $e) {
        $this->e = $e;
        return $this;
    }

    public function build() {
        $this->doc = new \DOMDocument('1.0', 'UTF-8');
        $dom_root = $this->doc->createElement('document');
        $dom_root->setAttribute('debug', $this->getConfigValue('site.debug'));
        $dom_root->setAttribute('url', (string)E()->getRequest()->getURI());
        $this->doc->appendChild($dom_root);
        $dom_documentProperties = $this->doc->createElement('properties');
        $dom_root->appendChild($dom_documentProperties);
        $prop =
            $this->doc->createElement('property', E()->getSiteManager()->getCurrentSite()->base);
        $prop->setAttribute('name', 'base');
        $prop->setAttribute('folder', E()->getSiteManager()->getCurrentSite()->folder);
        $dom_documentProperties->appendChild($prop);

        $prop = $this->doc->createElement('property',
            $langID = E()->getLanguage()->getCurrent());
        $prop->setAttribute('name', 'lang');
        $prop->setAttribute('abbr', E()->getRequest()->getLangSegment());
        $prop->setAttribute('real_abbr', E()->getLanguage()->getAbbrByID($langID));
        $dom_documentProperties->appendChild($prop);
        unset($prop);

        $result = $this->doc->createElement('errors');
        $result->setAttribute('xml:id', 'result');
        $dom_root->appendChild($result);

        $vm = E()->getController()->getViewMode();
        if ($vm == DocumentController::TRANSFORM_JSON) {
            $errors = [['message' => $this->e->getMessage()]];
            $data = [
                'result' => false,
                'errors' => $errors
            ];
            $result->appendChild(new \DOMText(json_encode($data)));
        } else {
            $error = $this->doc->createElement('error');
            $result->appendChild($error);

            $error->appendChild($this->doc->createElement('message', $this->e->getMessage()));
            $error->setAttribute('file', $this->e->getFile());
            $error->setAttribute('line', $this->e->getLine());

            $bktrace = $this->doc->createElement('backtrace');
            $dbgObj = debug_backtrace(!DEBUG_BACKTRACE_PROVIDE_OBJECT | DEBUG_BACKTRACE_IGNORE_ARGS, 2);
            array_walk($dbgObj, function ($callable) use ($bktrace) {
                $bktrace->appendChild($call = $this->doc->createElement('call'));
                array_walk($callable, function ($value, $key) use ($call) {
                    if (is_scalar($value))
                        $call->appendChild($this->doc->createElement($key, $value));
                    elseif (is_array($value)) {
                        foreach ($value as $k1 => $v1) {
                            if(!is_scalar($v1)) $v1 = '[...]';

                            $call->appendChild(
                                $this->doc->createElement(
                                    $key,
                                    $v1
                                )
                            );
                        }

                    }

                });
            });
            $result->appendChild($bktrace);

            if ($vm == DocumentController::TRANSFORM_HTML) {
                E()->getController()->getTransformer()->setFileName('error_page.xslt');
            }
        }
    }
    public function setProperties($name,$value) {
	if (is_null($this->doc)) return;
	$this->doc->getElementsByTagName('properties')[0]->setAttribute($name,$value);
    }
    public function getResult() {
        return $this->doc;
    }
}
