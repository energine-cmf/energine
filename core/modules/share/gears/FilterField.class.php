<?php
/**
 * @file
 * FilterField
 *
 * It contains the definition to:
 * @code
class FilterField;
@endcode
 *
 * @author andy.karpov
 * @copyright Energine 2013
 *
 * @version 1.0.0
 */


//todo VZ: This is very similar to Control class.
//todo Pavka: But this is not control .... hmm .... but why not? :)
/**
 * Filter control.
 *
 * @code
class FilterField;
@endcode
 */
class FilterField extends Object {
    /**
     * Element tag name.
     * @var string TAG_NAME
     */
    const TAG_NAME = 'field';

    /**
     * Document.
     * @var DOMDocument $doc
     */
    protected $doc;

    /**
     * Element type.
     *
     * @var string $type
     */
    protected $type = FieldDescription::FIELD_TYPE_STRING;

    /**
     * Additional attributes.
     * @var array $attributes
     */
    private $attributes = array();

    /**
     * Filter that holds this control element.
     * @var Filter $filter
     */
    private $filter;

    /**
     * Element ID.
     * @var int $index
     */
    private $index = false;

    /**
     * @param string $name Name.
     */
    public function __construct($name, $type = FieldDescription::FIELD_TYPE_STRING) {
        $this->setAttribute('name', $name);
        $this->type = $type;
        $this->doc = new DOMDocument('1.0', 'UTF-8');
    }

    /**
     * Attach filter.
     *
     * @param Filter $filter Filter.
     */
    public function attach($filter) {
        $this->filter = $filter;
    }

    /**
     * Get attached filter.
     *
     * @return Filter
     */
    protected function getFilter() {
        return $this->filter;
    }

    /**
     * Set element ID.
     *
     * @param int $index ID.
     */
    public function setIndex($index) {
        $this->index = $index;
    }

    /**
     * Get element ID.
     *
     * @return int
     *
     * @throws SystemException 'ERR_DEV_NO_CONTROL_INDEX'
     */
    public function getIndex() {
        if ($this->index === false) {
            throw new SystemException('ERR_DEV_NO_CONTROL_INDEX', SystemException::ERR_DEVELOPER);
        }
        return $this->index;
    }

    /**
     * Load element from XML description.
     *
     * @param SimpleXMLElement $description Element description.
     *
     * @throws SystemException 'ERR_DEV_NO_CONTROL_TYPE'
     */
    public function loadFromXml(SimpleXMLElement $description) {
        $attr = $description->attributes();

        $this->setAttribute('mode',
            FieldDescription::computeRights(
                $this->getFilter()->getComponent()->document->getRights(),
                !is_null($attr['ro_rights']) ? (int)$attr['ro_rights'] : null,
                !is_null($attr['fc_rights']) ? (int)$attr['fc_rights'] : null
            )
        );
        unset($attr['ro_rights']);
        unset($attr['fc_rights']);
        foreach ($attr as $key => $value) {
            if (isset($this->$key)) {
                $this->$key = (string)$value;
            } else {
                $this->setAttribute($key, (string)$value);
            }
        }
    }

    /**
     * Get element type.
     *
     * @return string
     *
     * @throws SystemException 'ERR_DEV_NO_CONTROL_TYPE'
     */
    public function getType() {
        return $this->type;
    }

    /**
     * Set attribute.
     *
     * @param string $attrName Attribute name.
     * @param mixed $attrValue Attribute value.
     */
    public function setAttribute($attrName, $attrValue) {
        $this->attributes[$attrName] = $attrValue;
    }

    /**
     * Get attribute.
     *
     * @param string $attrName Attribute name.
     * @return mixed
     */
    public function getAttribute($attrName) {
        if (isset($this->attributes[$attrName])) {
            return $this->attributes[$attrName];
        }
        return false;
    }

    /**
     * Build element.
     *
     * @return DOMNode
     */
    public function build() {

        $controlElem = $this->doc->createElement(self::TAG_NAME);

        foreach ($this->attributes as $attrName => $attrValue) {
            $controlElem->setAttribute($attrName, $attrValue);
        }
        $controlElem->setAttribute('type', $this->getType());
        $this->doc->appendChild($controlElem);

        return $this->doc->documentElement;
    }

    /**
     * Translate language-dependent attributes.
     *
     * @param array $attrs Set of attributes for translation.
     */
    public function translate($attrs = array('title')) {
        foreach ($attrs as $attrName) {
            $attrValue = (string)$this->getAttribute($attrName);
            if ($attrValue) {
                $this->setAttribute($attrName, DBWorker::_translate($attrValue));
            }
        }
    }
}
