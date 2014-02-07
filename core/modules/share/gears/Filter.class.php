<?php
/**
 * @file
 * Filter
 *
 * It contains the definition to:
 * @code
class Filter;
 * @endcode
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
 * @endcode
 */
class Filter extends Object {
    /**
     * Filter tag name.
     * @var string TAG_NAME
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

    public function __construct() {
        $this->doc = new DOMDocument('1.0', 'UTF-8');
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
     * @param array $meta Info about table columns
     * @return mixed
     */
    public function load(SimpleXMLElement $filterDescription, array $meta = null) {
        if (!empty($filterDescription))
            foreach ($filterDescription->field as $fieldDescription) {
                if (!isset($fieldDescription['name'])) {
                    throw new SystemException('ERR_BAD_FILTER_XML', SystemException::ERR_DEVELOPER);
                }
                $name = (string)$fieldDescription['name'];
                $field = new FilterField($name);
                $this->attachField($field);
                $field->load($fieldDescription, (isset($meta[$name]) ? $meta[$name] : null));
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
        if (sizeof($this->fields)) {
            $this->translate();
            $filterElem = $this->doc->createElement(self::TAG_NAME);
            $filterElem->setAttribute('title', DBWorker::_translate('TXT_FILTER'));
            $filterElem->setAttribute('apply', DBWorker::_translate('BTN_APPLY_FILTER'));
            $filterElem->setAttribute('reset', DBWorker::_translate('TXT_RESET_FILTER'));

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
            //Добавляем информацию о доступных опреациях
            $operatorsNode = $this->doc->createElement('operators');
            /*
             * <option value="like"><xsl:value-of select="$TRANSLATION[@const='TXT_FILTER_SIGN_CONTAINS']"/></option>
                 <option value="notlike"><xsl:value-of select="$TRANSLATION[@const='TXT_FILTER_SIGN_NOT_CONTAINS']"/></option>
                 <option value="=">=</option>
                 <option value="!=">!=</option>
                 <option value="&lt;"><xsl:text>&lt;</xsl:text></option>
                 <option value="&gt;"><xsl:text>&gt;</xsl:text></option>
                 <option value="checked">checked</option>
                 <option value="unchecked">unchecked</option>
                 <option value="between"><xsl:value-of select="$TRANSLATION[@const='TXT_FILTER_SIGN_BETWEEN']"/></option>
             */
            $stringTypes = array(
                FieldDescription::FIELD_TYPE_STRING,
                FieldDescription::FIELD_TYPE_SELECT,
                FieldDescription::FIELD_TYPE_TEXT,
                FieldDescription::FIELD_TYPE_HTML_BLOCK,
                FieldDescription::FIELD_TYPE_VALUE,
                FieldDescription::FIELD_TYPE_PHONE,
                FieldDescription::FIELD_TYPE_EMAIL,
                FieldDescription::FIELD_TYPE_CODE,
            );
            $numericTypes = array(
                FieldDescription::FIELD_TYPE_INT,
                FieldDescription::FIELD_TYPE_FLOAT
            );
            $dateTypes = array(
                FieldDescription::FIELD_TYPE_DATETIME,
                FieldDescription::FIELD_TYPE_DATE
            );
            foreach (array(
                'like' => array(
                    'title' => DBWorker::_translate('TXT_FILTER_SIGN_CONTAINS'),
                    'type' => $stringTypes
                ),
                'notlike' => array(
                    'title' => DBWorker::_translate('TXT_FILTER_SIGN_NOT_CONTAINS'),
                    'type' => $stringTypes
                ),
                '=' => array(
                    'title' => '=',
                    'type' => array_merge($stringTypes, $numericTypes, $dateTypes)
                ),
                '!=' => array(
                    'title' => '!=',
                    'type' => array_merge($stringTypes, $numericTypes, $dateTypes)
                ),
                '<' => array(
                    'title' => '<',
                    'type' => array_merge($dateTypes, $numericTypes)
                ),
                '>' => array(
                    'title' => '>',
                    'type' => array_merge($dateTypes, $numericTypes)
                ),
                'between' => array(
                    'title' => DBWorker::_translate('TXT_FILTER_SIGN_BETWEEN'),
                    'type' => array_merge($dateTypes, $numericTypes)
                ),
                'checked' => array(
                    'title' => DBWorker::_translate('TXT_FILTER_SIGN_CHECKED'),
                    'type' => array(FieldDescription::FIELD_TYPE_BOOL)
                ),
                'unchecked' => array(
                    'title' => DBWorker::_translate('TXT_FILTER_SIGN_UNCHEKED'),
                    'type' => array(FieldDescription::FIELD_TYPE_BOOL)
                ),
            ) as $operatorName => $operator){
                $operatorNode = $this->doc->createElement('operator');
                $operatorNode->setAttribute('title', $operator['title']);
                $operatorNode->setAttribute('name', $operatorName);
                $operatorsNode->appendChild($operatorNode);
                $typesNode = $this->doc->createElement('types');
                foreach($operator['type'] as $typeName){
                    $typesNode->appendChild($this->doc->createElement('type', $typeName));
                }
                $operatorNode->appendChild($typesNode);
            }
            $filterElem->appendChild($operatorsNode);

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
