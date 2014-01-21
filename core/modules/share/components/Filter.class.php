<?php
/**
 * @file
 * Filter
 *
 * It contains the definition to:
 * @code
class Filter;
@endcode
 *
 * @author andy.karpov
 * @copyright Energine 2013
 *
 * @version 1.0.0
 */

/**
 * Filters.
 *
 * @code
class Filter;
@endcode
 */
class Filter extends Object {
    /**
     * Filter tag name.
     * @ver string TAG_NAME
     */
    const TAG_NAME = 'filter';

    /**
     * Document.
     * @var DOMDocument $doc
     */
    private $doc;

    /**
     * Set of FilterField's.
     * @var array $fields
     */
    private $fields = array();

    /**
     * Additional properties.
     * @var array $properties
     */
    private $properties = array();

    /**
     * Component of the filter.
     * @var Component $component
     */
    private $component;

    public function __construct() {
        $this->doc = new DOMDocument('1.0', 'UTF-8');
    }

    /**
     * Attach filter to the component.
     *
     * @param Component $component Component.
     */
    public function attachToComponent(Component $component) {
        $this->component = $component;
    }

    /**
     * Get attached component.
     *
     * @return Component
     */
    public function getComponent() {
        return $this->component;
    }

    /**
     * Attach new filed.
     *
     * @param FilterField $field New filter field.
     */
    public function attachField(FilterField $field) {
        $field->setIndex(arrayPush($this->fields, $field));
        $field->attach($this);
    }

    /**
     * Detach field.
     *
     * @throws SystemException 'ERR_DEV_NO_CONTROL_TO_DETACH'
     *
     * @param FilterField $field filter field.
     */
    public function detachField(FilterField $field) {
        if (!isset($this->fields[$field->getIndex()])) {
            throw new SystemException('ERR_DEV_NO_CONTROL_TO_DETACH', SystemException::ERR_DEVELOPER);
        }
        unset($this->fields[$field->getIndex()]);
    }

    /**
     * Build filter from XML description.
     *
     * @throws SystemException 'ERR_DEV_NO_CONTROL_TYPE'
     *
     * @param SimpleXMLElement $filterDescription Filter description.
     * @return mixed
     */
    public function loadXML(SimpleXMLElement $filterDescription) {
        if(!empty($filterDescription))
            foreach ($filterDescription->field as $fieldDescription) {
                if (!isset($fieldDescription['type'])) {
                    throw new SystemException('ERR_DEV_NO_CONTROL_TYPE', SystemException::ERR_DEVELOPER);
                }

                $field = new FilterField(
                    isset($fieldDescription['name']) ? (string)$fieldDescription['name'] : null
                );

                $this->attachField($field);
                $field->loadFromXml($fieldDescription);
            }
    }

    /**
     * Get filter fields.
     *
     * @return array
     */
    public function getFields() {
        return $this->fields;
    }

    /**
     * Set filter property.
     *
     * @param string $name Property name.
     * @param mixed $value Property value.
     */
    public function setProperty($name, $value) {
        $this->properties[$name] = $value;
    }

    /**
     * Get property.
     *
     * @param string $name Property name.
     * @return array|null
     */
    public function getProperty($name) {
        if (isset($this->properties[$name])) {
            return $this->properties[$name];
        }
        return null;
    }

    /**
     * Build filter.
     *
     * @return DOMNode
     */
    public function build() {
        $result = false;

        if (count($this->fields) > 0) {
            $filterElem = $this->doc->createElement(self::TAG_NAME);
            if (!empty($this->properties)) {
                $props = $this->doc->createElement('properties');
                foreach ($this->properties as $propName => $propValue) {
                    $prop = $this->doc->createElement('property');
                    $prop->setAttribute('name', $propName);
                    $prop->appendChild($this->doc->createTextNode($propValue));
                    $props->appendChild($prop);
                }
                $filterElem->appendChild($props);
            }
            foreach ($this->fields as $field) {
                $filterElem->appendChild($this->doc->importNode($field->build(), true));
            }
            $this->doc->appendChild($filterElem);
            $result = $this->doc->documentElement;
        }

        return $result;
    }

    /**
     * Translate all filter fields.
     */
    public function translate() {
        foreach ($this->fields as $field) {
            $field->translate();
        }
    }
}
