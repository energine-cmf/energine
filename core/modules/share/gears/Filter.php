<?php
/**
 * @file
 * Filter
 * It contains the definition to:
 * @code
class Filter;
 * @endcode
 * @author andy.karpov
 * @copyright Energine 2013
 * @version 1.0.0
 */
namespace Energine\share\gears;

use Energine\share\components\Grid;

/**
 * Filters.
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
     * @var \DOMDocument $doc
     */
    private $doc;

    /**
     * Set of FilterField's.
     * @var FilterField[] $fields
     */
    private $fields = [];

    /**
     * Additional properties.
     * @var array $properties
     */
    private $properties = [];
    /**
     * Filter data
     * @var FilterData
     */
    private $data = null;

    public function __construct(FilterData $data = null) {
        if (!is_null($data)) {
            $this->data = $data;
        }
    }

    /**
     * Apply filter to the grid
     * @param Grid $grid
     * @throws SystemException
     */
    public function apply(Grid $grid) {
        if ($this->data) {
            foreach ($this->data as $v) {
                $grid->addFilterCondition((string)$v);
            }
        }
    }

    /**
     * Attach new filed.
     * @param FilterField $field New filter field.
     */
    public function attachField(FilterField $field) {
        $field->setIndex(arrayPush($this->fields, $field));
        $field->attach($this);
    }

    /**
     * Detach field.
     * @throws SystemException 'ERR_DEV_NO_CONTROL_TO_DETACH'
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
     * @throws SystemException 'ERR_DEV_NO_CONTROL_TYPE'
     * @param \SimpleXMLElement $filterDescription Filter description.
     * @param array $meta Info about table columns
     * @return mixed
     */
    public function load(\SimpleXMLElement $filterDescription, array $meta = null) {
        if (!empty($filterDescription)) {
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
    }

    /**
     * Get filter fields.
     * @return array
     */
    public function getFields() {
        return $this->fields;
    }

    /**
     * Set filter property.
     * @param string $name Property name.
     * @param mixed $value Property value.
     */
    public function setProperty($name, $value) {
        $this->properties[$name] = $value;
    }

    /**
     * Get property.
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
     * @return \DOMNode
     */
    public function build() {
        $result = false;
        if (sizeof($this->fields)) {
            $this->translate();
            $this->doc = new \DOMDocument('1.0', 'UTF-8');
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
            foreach (FilterConditionConverter::getInstance() as $operatorName => $operator) {
                $operatorNode = $this->doc->createElement('operator');
                $operatorNode->setAttribute('title', $operator['title']);
                $operatorNode->setAttribute('name', $operatorName);
                $operatorsNode->appendChild($operatorNode);
                $typesNode = $this->doc->createElement('types');
                foreach ($operator['type'] as $typeName) {
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
     * Translate attributes for filter fields
     */
    private function translate() {
        foreach ($this->fields as $field) {
            $field->translate();
        }
    }
}

/**
 * Class FilterConditionConverter
 * Mapper for types and corresponded condition
 *
 * @package Energine\share\gears
 */
class FilterConditionConverter implements \ArrayAccess, \Iterator {
    /**
     * @var FilterConditionConverter
     */
    private static $instance = null;
    /**
     * @var array
     */
    private $map;
    /**
     * @var int
     */
    private $index;
    /**
     * @var array
     */
    private $indexedMap;

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Return the current element
     * @link http://php.net/manual/en/iterator.current.php
     * @return mixed Can return any type.
     */
    public function current() {
        return $this->map[$this->indexedMap[$this->index]];
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Move forward to next element
     * @link http://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     */
    public function next() {
        $this->index++;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Return the key of the current element
     * @link http://php.net/manual/en/iterator.key.php
     * @return mixed scalar on success, or null on failure.
     */
    public function key() {
        return $this->indexedMap[$this->index];
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Checks if current position is valid
     * @link http://php.net/manual/en/iterator.valid.php
     * @return boolean The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     */
    public function valid() {
        return array_key_exists($this->index, $this->indexedMap);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Rewind the Iterator to the first element
     * @link http://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     */
    public function rewind() {
        $this->index = 0;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Whether a offset exists
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset <p>
     * An offset to check for.
     * </p>
     * @return boolean true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     */
    public function offsetExists($offset) {
        return array_key_exists($offset, $this->map);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to retrieve
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset <p>
     * The offset to retrieve.
     * </p>
     * @return mixed Can return all value types.
     */
    public function offsetGet($offset) {
        return $this->map[$offset];
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to set
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset <p>
     * The offset to assign the value to.
     * </p>
     * @param mixed $value <p>
     * The value to set.
     * </p>
     * @return void
     * @throws SystemException
     */
    public function offsetSet($offset, $value) {
        throw new SystemException('ERR_NO_MODIFICATION');
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to unset
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset <p>
     * The offset to unset.
     * </p>
     * @return void
     * @throws SystemException
     */
    public function offsetUnset($offset) {
        throw new SystemException('ERR_NO_MODIFICATION');
    }

    /**
     * @return FilterConditionConverter
     */
    public static function getInstance() {
        if (is_null(self::$instance)) {
            self::$instance = new static;
        }
        return self::$instance;
    }

    /**
     *
     */
    private function __construct() {
        $stringTypes = [
            FieldDescription::FIELD_TYPE_STRING,
            FieldDescription::FIELD_TYPE_SELECT,
            FieldDescription::FIELD_TYPE_TEXT,
            FieldDescription::FIELD_TYPE_HTML_BLOCK,
            FieldDescription::FIELD_TYPE_PHONE,
            FieldDescription::FIELD_TYPE_EMAIL,
            FieldDescription::FIELD_TYPE_CODE,
        ];
        $numericTypes = [
            FieldDescription::FIELD_TYPE_INT,
            FieldDescription::FIELD_TYPE_FLOAT
        ];
        $dateTypes = [
            FieldDescription::FIELD_TYPE_DATETIME,
            FieldDescription::FIELD_TYPE_DATE
        ];
        $this->map = [
            'like' => [
                'title' => DBWorker::_translate('TXT_FILTER_SIGN_CONTAINS'),
                'type' => $stringTypes,
                'condition' => 'LIKE \'%%%s%%\'',
            ],
            'notlike' => [
                'title' => DBWorker::_translate('TXT_FILTER_SIGN_NOT_CONTAINS'),
                'type' => $stringTypes,
                'condition' => 'NOT LIKE \'%%%s%%\'',
            ],
            '=' => [
                'title' => '=',
                'type' => array_merge($stringTypes, $numericTypes, $dateTypes),
                'condition' => '= \'%s\'',
            ],
            '!=' => [
                'title' => '!=',
                'type' => array_merge($stringTypes, $numericTypes, $dateTypes),
                'condition' => '!= \'%s\'',
            ],
            '<' => [
                'title' => '<',
                'type' => array_merge($dateTypes, $numericTypes),
                'condition' => '<\'%s\'',
            ],
            '>' => [
                'title' => '>',
                'type' => array_merge($dateTypes, $numericTypes),
                'condition' => '>\'%s\'',
            ],
            'between' => [
                'title' => DBWorker::_translate('TXT_FILTER_SIGN_BETWEEN'),
                'type' => array_merge($dateTypes, $numericTypes),
                'condition' => 'BETWEEN \'%s\' AND \'%s\'',
            ],
            'checked' => [
                'title' => DBWorker::_translate('TXT_FILTER_SIGN_CHECKED'),
                'type' => [FieldDescription::FIELD_TYPE_BOOL],
                'condition' => '= 1',
            ],
            'unchecked' => [
                'title' => DBWorker::_translate('TXT_FILTER_SIGN_UNCHEKED'),
                'type' => [FieldDescription::FIELD_TYPE_BOOL],
                'condition' => '!=1'
            ],
        ];
        $this->indexedMap = array_keys($this->map);
    }
}
