<?php

/**
 * Класс Data.
 *
 * @package energine
 * @subpackage core
 * @author dr.Pavka
 * @copyright Energine 2006
 * @version $Id$
 */

//require_once('core/framework/Object.class.php');
//require_once('core/framework/Field.class.php');

/**
 * Данные.
 *
 * @package energine
 * @subpackage core
 * @author dr.Pavka
 */
class Data extends Object {

    /**
     * @access private
     * @var array поля данных
     */
    private $fields = array();

    /**
     * @access private
     * @var int количество полей данных
     */
    private $length = 0;

    /**
     * @access private
     * @var int количество строк данных
     */
    private $rows = 0;

    /**
     * Конструктор класса.
     *
     * @access public
     * @return void
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Загружает набор данных, полученных из БД.
     *
     * @access public
     * @param array $data
     * @return void
     * @see DBA::selectRequest()
     */
    public function load(array $data) {
        $data = inverseDBResult($data);
        foreach ($data as $fieldName => $fieldValues) {
            $fieldObject = new Field($fieldName);
            $fieldObject->setData($fieldValues);
            $this->addField($fieldObject);
        }
    }

    /**
     * Добавляет строку данных ко всем полям.
     *
     * @access public
     * @param array $rowData
     * @return void
     */
    public function addRow(array $rowData) {
        foreach ($rowData as $fieldName => $fieldValue) {
            $field = $this->getFieldByName($fieldName);
            if ($field) {
                $field->addRowData($fieldValue);
            }
        }
    }

    /**
     * Удаляет строку данных из всех полей.
     *
     * @access public
     * @param int $rowIndex
     * @return void
     */
    public function removeRow($rowIndex) {
        foreach ($this->fields as $field) {
            $field->removeRowData($rowIndex);
        }
    }

    /**
     * Изменяет строку данных для всех полей.
     *
     * @access public
     * @param int $rowIndex
     * @param array $rowData
     * @return boolean
     */
    public function changeRow($rowIndex, array $rowData) {
        $result = false;
        foreach ($rowData as $fieldName => $fieldValue) {
        	$field = $this->getFieldByName($fieldName);
        	if ($field) {
        		$result = $field->setRowData($rowIndex, $fieldValue);
        	}
        }
        return $result;
    }

    /**
     * Добавляет поле данных.
     *
     * @access public
     * @param Field $field
     * @return void
     */
    public function addField(Field $field) {
        $this->fields[$field->getName()] = $field;
        $this->length++;
    }

    /**
     * Удаляет поле данных.
     *
     * @access public
     * @param Field $field
     * @return void
     */
    public function removeField(Field $field) {
        if (isset($this->fields[$field->getName()])) {
        	unset($this->fields[$field->getName()]);
        	$this->length--;
        }
    }

    /**
     * Возвращает поле с указанным именем.
     *
     * @access public
     * @param string $name
     * @return Field
     */
    public function getFieldByName($name) {
        $field = false;
        if (isset($this->fields[$name])) {
            $field = $this->fields[$name];
        }
        return $field;
    }

    /**
     * Возвращает набор полей данных.
     *
     * @access public
     * @return array
     */
    public function getFields() {
        return $this->fields;
    }

    /**
     * Возвращает количество полей данных.
     *
     * @access public
     * @return int
     */
    public function getLength() {
        return $this->length;
    }
    /**
     * Возвращает флаг указывающий на то является ли объект данных пустым 
     * 
     * @return bool
     */
    public function isEmpty(){
    	return empty($this->fields);
    }

    /**
     * Возвращает количество строк данных.
     *
     * @return int
     * @access public
     */
    public function getRowCount() {
        if ($this->length > 0) {
            $fieldNames = array_keys($this->fields);
            $firstFieldName = $fieldNames[0];
            $this->rows = $this->getFieldByName($firstFieldName)->getRowCount();
        }
        return $this->rows;
    }
}
