<?php
/**
 * @file
 * FilterField
 * It contains the definition to:
 * @code
class FilterField;
 * @endcode
 * @author andy.karpov
 * @copyright Energine 2013
 * @version 1.0.0
 */
namespace Energine\share\gears;

/**
 * Filter control.
 * @code
class FilterField;
 * @endcode
 */
class FilterField extends Primitive {
    /**
     * Element tag name.
     * @var string TAG_NAME
     */
    const TAG_NAME = 'field';

    /**
     * Document.
     * @var \DOMDocument $doc
     */
    protected $doc;

    /**
     * Element type.
     * @var string $type
     */
    protected $type = FieldDescription::FIELD_TYPE_STRING;

    /**
     * Additional attributes.
     * @var array $attributes
     */
    private $attributes = [];

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
    private $condition;
    private $value;
    private $name;
    private $operator = '';


    /**
     * @param string $name Name.
     * @param string
     */
    public function __construct($name, $type = FieldDescription::FIELD_TYPE_STRING) {
        $this->name = $name;
        $this->type = $type;
    }

    /**
     * Attach filter.
     * @param Filter $filter Filter.
     */
    public function attach($filter) {
        $this->filter = $filter;
    }

    /**
     * Get attached filter.
     * @return Filter
     */
    protected function getFilter() {
        return $this->filter;
    }

    /**
     * Set element ID.
     * @param int $index ID.
     */
    public function setIndex($index) {
        $this->index = $index;
    }

    /**
     * Get element ID.
     * @return int
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
     * @param \SimpleXMLElement $description Element description.
     * @param array $meta DB column meta data
     * @throws SystemException 'ERR_DEV_NO_CONTROL_TYPE'
     */
    public function load(\SimpleXMLElement $description, array $meta = null) {
        //Получили список аттрибутов заданных в филдах фильтра
        //Get the attributes list form filter fields
        $attrs = (array)$description->attributes();
        $attrs = $attrs['@attributes'];
        //we do no need name attribute
        unset($attrs['name']);

        if ($meta) {
            if (!isset($attrs['title'])) {
                $attrs['title'] = 'FIELD_' . $this->name;
            }
            $attrs['type'] = FieldDescription::convertType($meta['type'], $this->getAttribute('name'), $meta['length'],
                $meta);
            $attrs['tableName'] = $meta['tableName'];
        }

        foreach ($attrs as $key => $value) {
            if (isset($this->$key)) {
                $this->$key = $value;
            } else {
                $this->setAttribute($key, $value);
            }
        }
    }

    /**
     * Get element type.
     * @return string
     * @throws SystemException 'ERR_DEV_NO_CONTROL_TYPE'
     */
    public function getType() {
        return $this->type;
    }

    /**
     * Set attribute.
     * @param string $attrName Attribute name.
     * @param mixed $attrValue Attribute value.
     *
     * @return FilterField
     */
    public function setAttribute($attrName, $attrValue) {
        $this->attributes[$attrName] = $attrValue;
        return $this;
    }

    /**
     * Get attribute.
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
     * @param $data
     * @return FilterField
     */
    public static function createFrom($data) {
        if (isset($data['field']) && preg_match('/^\[([a-z_]+)\]\[([a-z_]+)\]$/', $data['field'], $matches)) {
            $result = new FilterField($matches[2]);
            $result->setAttribute('tableName', $matches[1]);
            if (isset($data['type'])) {
                $result->setAttribute('type', $data['type']);
                if (isset($data['condition'])) {
                    $result->condition = $data['condition'];
                }
                if (isset($data['value'])) {
                    $result->value = $data['value'];
                }
                if (isset($data['operator'])) {
                    $result->operator = $data['operator'];
                }
            }
        }


        return $result;
    }

    public function setValue($value) {
        $this->value = $value;
        return $this;
    }

    public function setOperator($operator) {
        $this->operator = $operator;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getValue() {
        return $this->value;
    }

    /**
     * @param mixed $condition
     * @return FilterField
     */
    public function setCondition($condition) {
        $this->condition = $condition;
        return $this;
    }


    function __toString() {
        $dbh = E()->getDB();
        $tableName = $this->getAttribute('tableName');
        $fieldName = $this->name;

        $values = $this->value;
        if (!is_array($values)) {
            $values = [$values];
        }

        if (
            !$dbh->tableExists($tableName)
            ||
            !($tableInfo = $dbh->getColumnsInfo($tableName))
            ||
            !isset($tableInfo[$fieldName])
        ) {
            throw new SystemException('ERR_BAD_FILTER_DATA', SystemException::ERR_CRITICAL, $tableName);
        }

        if (is_array($tableInfo[$fieldName]['key'])) {
            $fkTranslationTableName =
                $dbh->getTranslationTablename($tableInfo[$fieldName]['key']['tableName']);
            $fkTableName =
                ($fkTranslationTableName) ? $fkTranslationTableName
                    : $tableInfo[$fieldName]['key']['tableName'];
            $fkValueField = substr($fkKeyName =
                    $tableInfo[$fieldName]['key']['fieldName'], 0, strrpos($fkKeyName, '_')) .
                '_name';
            $fkTableInfo = $dbh->getColumnsInfo($fkTableName);
            if (!isset($fkTableInfo[$fkValueField])) {
                $fkValueField = $fkKeyName;
            }

            if ($res =
                $dbh->getColumn($fkTableName, $fkKeyName,
                    $fkTableName . '.' . $fkValueField . ' ' .
                    call_user_func_array('sprintf',
                        array_merge([FilterConditionConverter::getInstance()[$this->condition]['condition']], $values)) .
                    ' ')
            ) {
                return $this->operator.' '.$tableName . '.' . $fieldName . ' IN (' . implode(',', $res) . ')';
            } else {
                return $this->operator.' '.' FALSE ';
            }
        } else {

            $fieldType = FieldDescription::convertType($tableInfo[$fieldName]['type'], $fieldName,
                $tableInfo[$fieldName]['length'], $tableInfo[$fieldName]);
            if ($fieldType == FieldDescription::FIELD_TYPE_BOOL) $this->value = '';
            //modbysd bug with 0 elseif (!$this->value) {
            elseif (!isset($this->value)) {
                return '';
            }
            if (in_array($this->condition, ['like', 'notlike']) && in_array($fieldType,
                    [FieldDescription::FIELD_TYPE_DATE, FieldDescription::FIELD_TYPE_DATETIME])
            ) {
                if ($this->condition == 'like') {
                    $this->condition = '=';
                } else {
                    $this->condition = '!=';
                }
            }

            $fieldName = (($tableName) ? $tableName . '.' : '') . $fieldName;
            if ($fieldType == FieldDescription::FIELD_TYPE_DATETIME) {
                $fieldName = 'DATE(' . $fieldName . ')';
            }

            $conditionPatterns = FilterConditionConverter::getInstance()[$this->condition]['condition'];
            if (in_array($fieldType, [FieldDescription::FIELD_TYPE_DATETIME, FieldDescription::FIELD_TYPE_DATE])) {
                $conditionPatterns = str_replace('\'%s\'', 'DATE(\'%s\')', $conditionPatterns);
            }
            $r = $this->operator.' '.$fieldName . ' ' . call_user_func_array('sprintf', array_merge([$conditionPatterns], $values)) . ' ';
            return $r;
        }
    }


    /**
     * Build element.
     * @return DOMNode
     */
    public function build() {
        $this->doc = new \DOMDocument('1.0', 'UTF-8');

        $controlElem = $this->doc->createElement(self::TAG_NAME);
        $controlElem->setAttribute('name', $this->name);
        foreach ($this->attributes as $attrName => $attrValue) {
            $controlElem->setAttribute($attrName, $attrValue);
        }

        $controlElem->setAttribute('type', $this->getType());
        $this->doc->appendChild($controlElem);

        return $this->doc->documentElement;
    }

    /**
     * Translate language-dependent attributes.
     * @param array $attrs Set of attributes for translation.
     */
    public function translate($attrs = ['title']) {
        foreach ($attrs as $attrName) {
            $attrValue = (string)$this->getAttribute($attrName);
            if ($attrValue) {
                $this->setAttribute($attrName, translate($attrValue));
            }
        }
    }
}
