<?php
/**
 * @file
 * Control
 *
 * It contains the definition to:
 * @code
abstract class Control;
@endcode
 *
 * @author dr.Pavka
 * @copyright Energine 2006
 *
 * @version 1.0.0
 */
namespace Energine\share\gears;
//todo VZ: There should be Element class that is extended from Object and has all needed methods and members.
/**
 * Toolbar control.
 *
 * @code
abstract class Control;
@endcode
 *
 * @abstract
 */
abstract class Control extends Object {
    /**
     * Element tag name.
     * @var string TAG_NAME
     */
    const TAG_NAME = 'control';

    /**
     * Document.
     * @var \DOMDocument $doc
     */
    protected $doc;

    /**
     * Element type.
     * @var string $type
     */
    protected $type = false;

    /**
     * Is element disabled?
     * @var boolean $disabled
     */
    private $disabled = false;

    /**
     * Additional attributes.
     * @var array $attributes
     */
    private $attributes = array();

    /**
     * Toolbar.
     * @var ToolBar $toolbar
     */
    private $toolbar;

    /**
     * Element index.
     * It is assigned by toolbar after attaching.
     * @var int $index
     */
    private $index = false;

    /**
     * Constructor.
     *
     * @param string $id Control ID.
     */
    public function __construct($id) {
        $this->setAttribute('id', $id);
        $this->doc = new \DOMDocument('1.0', 'UTF-8');
    }

    /**
     * Attach control element to the toolbar.
     *
     * @param Toolbar $toolbar Toolbar.
     */
    public function attach($toolbar) {
        $this->toolbar = $toolbar;
    }

    /**
     * Get toolbar.
     *
     * @return Toolbar
     */
    protected function getToolbar() {
        return $this->toolbar;
    }

    /**
     * Set element index.
     *
     * @param int $index ID.
     *
     * @note This is called from Toolbar.
     */
    public function setIndex($index) {
        $this->index = $index;
    }

    /**
     * Get element index.
     *
     * @return int
     *
     * @note This is called from Toolbar.
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
     * @param \SimpleXMLElement $description Element description.
     *
     * @throws SystemException 'ERR_DEV_NO_CONTROL_TYPE'
     */
    public function loadFromXml(\SimpleXMLElement $description) {
        if (!isset($description['type'])) {
            throw new SystemException('ERR_DEV_NO_CONTROL_TYPE', SystemException::ERR_DEVELOPER);
        }

        $attr = $description->attributes();

        $this->setAttribute('mode',
            FieldDescription::computeRights(
                $this->getToolbar()->getComponent()->document->getRights(),
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
     * Disable element.
     */
    public function disable() {
        $this->disabled = true;
    }

    /**
     * Enable element.
     */
    public function enable() {
        $this->disabled = false;
    }

    /**
     * Get element type.
     *
     * @return string
     *
     * @throws SystemException 'ERR_DEV_NO_CONTROL_TYPE'
     */
    public function getType() {
        if (!$this->type) {
            throw new SystemException('ERR_DEV_NO_CONTROL_TYPE', SystemException::ERR_DEVELOPER);
        }
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
     * Get attribute value.
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
     * Get element ID.
     *
     * @return string
     */
    public function getID() {
        return $this->getAttribute('id');
    }

    /**
     * Build control element.
     *
     * @return DOMNode
     */
    public function build() {
        $controlElem = $this->doc->createElement(self::TAG_NAME);

        /*if (!isset($this->attributes['mode']) && ($this->type != 'separator')) {
            $this->attributes['mode'] = FieldDescription::computeRights($this->getToolbar()->getComponent()->document->getRights());
        }*/
        foreach ($this->attributes as $attrName => $attrValue) {
            $controlElem->setAttribute($attrName, $attrValue);
        }
        if ($this->disabled) {
            $controlElem->setAttribute('disabled', 'disabled');
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
    public function translate($attrs = array('title', 'tooltip')) {
        foreach ($attrs as $attrName) {
            $attrValue = (string)$this->getAttribute($attrName);
            if ($attrValue) {
                $this->setAttribute($attrName, DBWorker::_translate($attrValue));
            }
        }
    }
}
