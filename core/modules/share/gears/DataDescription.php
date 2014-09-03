<?php
/**
 * @file
 * DataDescription.
 *
 * It contains the definition to:
 * @code
class DataDescription;
@endcode
 *
 * @author dr.Pavka
 * @copyright Energine 2006
 *
 * @version 1.0.0
 */
namespace Energine\share\gears;
//todo VZ: Why the Iterator is multiple times have the same implementation?
/**
 * Meta data.
 *
 * @code
class DataDescription
@endcode
 *
 * Data description.
 */
class DataDescription extends Object implements \Iterator {
    /**
     * Field position: @c 'after'
     * @var string FIELD_POSITION_AFTER
     */
    const FIELD_POSITION_AFTER = 'after';
    /**
     * Field position: @c 'before'
     * @var string FIELD_POSITION_BEFORE
     */
    const FIELD_POSITION_BEFORE = 'before';

    /**
     * Meta data for fields.
     * @var array $fieldDescriptions
     */
    private $fieldDescriptions;

    /**
     * Index of the current element (used for iteration).
     * @var int $currentIndex
     */
    private $currentIndex = 0;

    public function __construct() {
        $this->fieldDescriptions = array();
    }

    //todo VZ: I recommend to create method for loading single data description and use it for this and next load methods.
    /**
     * Load the data descriptions received from the data base.
     *
     * @param array $columnsInfo Data description.
     *
     * @see DBA::getColumnsInfo()
     */
	//TODO Добавить возможность загрузки обычного массива создавая FieldDescription с параметрами по умолчанию
    public function load(array $columnsInfo) {
        foreach ($columnsInfo as $columnName => $columnInfo) {
            $fieldDescr = new FieldDescription($columnName);
            $fieldDescr->loadArray($columnInfo);
            $this->addFieldDescription($fieldDescr);
        }
    }

    /**
     * Load data descriptions from XML file.
     *
     * @param \SimpleXMLElement $xmlDescr XML file with descriptions.
     */
    public function loadXML(\SimpleXMLElement $xmlDescr) {
        if (!empty($xmlDescr))
        foreach ($xmlDescr->field as $fieldXmlDescr) {
            $fieldDescr = new FieldDescription();
            $fieldDescr->loadXML($fieldXmlDescr);
            $this->addFieldDescription($fieldDescr);
        }
    }

    /**
     * Add new field description.
     *
     * @param FieldDescription $fieldDescription New field description.
     * @param string $location Description location.
     * @param string $targetFDName Target field description name.
     */
    public function addFieldDescription(FieldDescription $fieldDescription, $location = 'bottom', $targetFDName = null) {
        if($location == self::FIELD_POSITION_AFTER && $targetFDName && array_key_exists($targetFDName, $this->fieldDescriptions)){
            $this->fieldDescriptions = array_push_after($this->fieldDescriptions, array($fieldDescription->getName() => $fieldDescription), $targetFDName);
        }
       /* elseif($location == 'before' ){

        }*/
        else {
            $this->fieldDescriptions[$fieldDescription->getName()] = $fieldDescription;    
        }

    }

    /**
     * Remove field description.
     *
     * @param FieldDescription $fieldDescription Field description.
     */
    public function removeFieldDescription(FieldDescription $fieldDescription) {
        	unset($this->fieldDescriptions[$fieldDescription->getName()]);
    }

    //todo VZ: Why it returns bool instead of null?
    /**
     * Get field description by field name.
     *
     * @param string $name Field name
     * @return FieldDescription|bool
     */
    public function getFieldDescriptionByName($name) {
        $fieldDescription = false;
        if (isset($this->fieldDescriptions[$name])) {
            $fieldDescription = $this->fieldDescriptions[$name];
        }
        return $fieldDescription;
    }

    /**
     * Get field descriptions by type.
     *
     * @param $types string|array
     * @return FieldDescription[]
     */
    public function getFieldDescriptionsByType($types){
        $result = array();
        if(!is_array($types)) $types = array($types);
        foreach($this->fieldDescriptions as $name => $fieldDescription){
            if(in_array($fieldDescription->getType(), $types)){
                $result[$name] = $fieldDescription;
            }
        }
        return $result;
    }


    /**
     * Get the list of field description names.
     *
     * @return array
     * @todo Не очень красивый метод, нужно бы как то без него обойтись
     */
    public function getFieldDescriptionList() {
        return array_keys($this->fieldDescriptions);
    }

    /**
     * Check whether the @link DataDescription::$fieldDescriptions field description@endlink array is empty.
     *
     * @return bool
     */
    public function isEmpty() {
        return !(bool)sizeof($this->fieldDescriptions);
    }

    /**
     * Intersect this data description with other object data description.
     *
     * @param DataDescription $otherDataDescr Other data description.
     * @return DataDescription
     */
    public function intersect(DataDescription $otherDataDescr) {
        $result = false;
        if ($this->isEmpty()) {
        	$result = $otherDataDescr;
        }
        else {
            // проходимся по описаниям полей текущего объекта
            foreach ($this->fieldDescriptions as $fieldName => $fieldDescription) {
                // если существует описание из БД - пересекаем с ним
                if ($otherDataDescr->getFieldDescriptionByName($fieldName)) {
                    $this->fieldDescriptions[$fieldName] = FieldDescription::intersect(
                        $this->fieldDescriptions[$fieldName],
                        $otherDataDescr->getFieldDescriptionByName($fieldName)
                    );
                }
                /*
                 * Если описания из БД отсутствует, устанавливаем дополнительное свойство customField,
                 * которое указывает на то, что данные этого поля сохранять в БД не нужно.
                 */
                else {
                    $this->getFieldDescriptionByName($fieldName)->setProperty('customField', 'customField');
                }
            }
            $result = $this;
        }
        return $result;
    }

    public function rewind() {
        $this->currentIndex = 0;
    }

    public function current() {
        $fieldNames = $this->getFieldDescriptionList();
        return $this->fieldDescriptions[$fieldNames[$this->currentIndex]];
    }

    public function key() {
        $fieldNames = $this->getFieldDescriptionList();
        return $fieldNames[$this->currentIndex];
    }

    public function next() {
        $this->currentIndex++;
    }

    public function valid() {
        $fieldNames = $this->getFieldDescriptionList();
        return isset($fieldNames[$this->currentIndex]);
    }
}
