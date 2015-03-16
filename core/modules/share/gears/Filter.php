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
     * @var array $fields
     */
    private $fields = [];

    /**
     * Additional properties.
     * @var array $properties
     */
    private $properties = [];
    /**
     * Filter map
     * Contains all info about filters
     * @var array
     */
    private $map = [];
    /**
     * Filter data
     * @var array
     */
    private $data = null;
    /**
     * Current filter condition
     * @var string
     */
    private $condition = false;

    public function __construct() {
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
            'like'      => [
                'title'     => DBWorker::_translate('TXT_FILTER_SIGN_CONTAINS'),
                'type'      => $stringTypes,
                'condition' => 'LIKE \'%%%s%%\'',
            ],
            'notlike'   => [
                'title'     => DBWorker::_translate('TXT_FILTER_SIGN_NOT_CONTAINS'),
                'type'      => $stringTypes,
                'condition' => 'NOT LIKE \'%%%s%%\'',
            ],
            '='         => [
                'title'     => '=',
                'type'      => array_merge($stringTypes, $numericTypes, $dateTypes),
                'condition' => '= \'%s\'',
            ],
            '!='        => [
                'title'     => '!=',
                'type'      => array_merge($stringTypes, $numericTypes, $dateTypes),
                'condition' => '!= \'%s\'',
            ],
            '<'         => [
                'title'     => '<',
                'type'      => array_merge($dateTypes, $numericTypes),
                'condition' => '<\'%s\'',
            ],
            '>'         => [
                'title'     => '>',
                'type'      => array_merge($dateTypes, $numericTypes),
                'condition' => '>\'%s\'',
            ],
            'between'   => [
                'title'     => DBWorker::_translate('TXT_FILTER_SIGN_BETWEEN'),
                'type'      => array_merge($dateTypes, $numericTypes),
                'condition' => 'BETWEEN \'%s\' AND \'%s\'',
            ],
            'checked'   => [
                'title'     => DBWorker::_translate('TXT_FILTER_SIGN_CHECKED'),
                'type'      => [FieldDescription::FIELD_TYPE_BOOL],
                'condition' => '= 1',
            ],
            'unchecked' => [
                'title'     => DBWorker::_translate('TXT_FILTER_SIGN_UNCHEKED'),
                'type'      => [FieldDescription::FIELD_TYPE_BOOL],
                'condition' => '!=1'
            ],
        ];
        if (isset($_POST[self::TAG_NAME]) && !empty($_POST[self::TAG_NAME])) {
            if (!($this->data = json_decode($_POST[self::TAG_NAME], true))) {
                throw new SystemException('ERR_BAD_FILTER_DATA', SystemException::ERR_CRITICAL, $_POST[self::TAG_NAME]);
            }

            $clear = function ($data) use (&$clear) {
                $result = [];
                foreach ($data as $key => $value) {
                    if (!is_null($value)) {
                        if (is_array($value)) {
                            $value = $clear($value);
                        }
                        $result[$key] = $value;
                    }
                }

                return $result;
            };
            $this->data =  FilterFieldGroup::createFrom($clear($this->data));
        }

    }

    /**
     * Apply filter to the grid
     * @param Grid $grid
     * @throws SystemException
     */
    public function apply(Grid $grid) {
        if ($this->data) {
            $dbh = E()->getDB();
            inspect($this->data);
            $tableName = key($this->data);
            $fieldName = key($this->data[$tableName]);
            $values = $this->data[$tableName][$fieldName];
            if (
                !$dbh->tableExists($tableName)
                ||
                !($tableInfo = $dbh->getColumnsInfo($tableName))
                ||
                !isset($tableInfo[$fieldName])
            ) {
                throw new SystemException('ERR_BAD_FILTER_DATA', SystemException::ERR_CRITICAL, $tableName);
            }
            if (
            is_array($tableInfo[$fieldName]['key'])
            ) {
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
                            array_merge([$this->map[$this->condition]['condition']], $values)) .
                        ' ')
                ) {
                    $grid->addFilterCondition([$tableName . '.' . $fieldName => $res]);
                } else {
                    $grid->addFilterCondition(' FALSE');
                }
            } else {

                $fieldType = FieldDescription::convertType($tableInfo[$fieldName]['type'], $fieldName,
                    $tableInfo[$fieldName]['length'], $tableInfo[$fieldName]);

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


                if (in_array($fieldType, [FieldDescription::FIELD_TYPE_DATETIME, FieldDescription::FIELD_TYPE_DATE])) {
                    array_walk($this->map, function (&$row) {
                        $row['condition'] = str_replace('\'%s\'', 'DATE(\'%s\')', $row['condition']);
                    });

                }
                $conditionPatterns = $this->map[$this->condition]['condition'];
                $grid->addFilterCondition(
                    $fieldName . ' ' .
                    call_user_func_array('sprintf', array_merge([$conditionPatterns], $values)) .
                    ' '
                );
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

            foreach ($this->map as $operatorName => $operator) {
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

class FilterFieldGroup {
    private $children = [];
    private $operator = 'OR';

    private function __construct() {

    }
    public function add($child){
        array_push($this->children, $child);
    }

    public static function createFrom($data){
        $result = new FilterFieldGroup();
        if (isset($data['children']) && is_array($data['children'])) {
            foreach ($data['children'] as $child) {
                if (array_key_exists('children', $child)) {
                    $child = new FilterFieldGroup($child);
                } else {
                    $child = FilterField::createFrom($child);
                }
                $child->add($child);


            }
        }
        if (isset($data['operator'])) {
            $result->setOperator($data['operator']);
        }
        return $result;
    }

    public function setOperator($operator) {
        $this->operator = $operator;
    }

    public function __toString() {
        $result = '';
        array_reduce($this->children, function ($result, $filter) {
            return $result . ' (' . (string)$filter . ') ' . $this->operator;
        });

        return substr($result, 0, -sizeof($this->operator));
    }
}
