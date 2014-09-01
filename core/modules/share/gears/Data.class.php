<?php
namespace Energine\share\gears;
/**
 * @file
 * Data.
 *
 * It contains the definition to:
 * @code
class Data;
@endcode
 *
 * @author dr.Pavka
 * @copyright Energine 2006
 *
 * @version 1.0.0
 */


/**
 * Holds data.
 *
 * @code
class Data
@endcode
 *
 */
class Data extends Object {
    /**
     * Data fields.
     * @var array $fields
     */
    private $fields = array();

    /**
     * Amount of data fields.
     * @var int $length
     */
    private $length = 0;

    /**
     * Amount of data rows.
     * @var int $rows
     */
    private $rows = 0;

    /**
     * Load the dataset, received from the data base.
     *
     * @param array $data Dataset.
     *
     * @see DBA::selectRequest()
     */
    public function load($data) {
        if (is_array($data) && ! empty($data)) {
            $data = inverseDBResult($data);
            foreach ($data as $fieldName => $fieldValues) {
                //Если такого поля не существует еще, то создаем
                if (!($fieldObject = $this->getFieldByName($fieldName))) {
                    $fieldObject = new Field($fieldName);
                    $this->addField($fieldObject);
                }
                //и заносим в него данные
                $fieldObject->setData($fieldValues);
            }
        }
    }

    /**
     * Add data row to all data fields.
     *
     * @param array $rowData Data row.
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
     * Remove data row from all data fields.
     *
     * @param int $rowIndex Row ID.
     */
    public function removeRow($rowIndex) {
        foreach ($this->fields as $field) {
            $field->removeRowData($rowIndex);
        }
    }

    //todo VZ: Why bool is returned?
    /**
     * Change the data row for all data fields.
     *
     * @param int $rowIndex Row ID.
     * @param array $rowData Row data.
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
     * Add data field.
     *
     * @param Field $field New data field.
     */
    public function addField(Field $field) {
        $this->fields[$field->getName()] = $field;
        $this->length++;
    }

    /**
     * Remove data field.
     *
     * @param Field $field Data field.
     */
    public function removeField(Field $field) {
        if (isset($this->fields[$field->getName()])) {
            unset($this->fields[$field->getName()]);
            $this->length--;
        }
    }

    /**
     * Get field by his name.
     *
     * @param string $name Field name.
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
     * Get all @link Data::$fields data fields@endlink.
     *
     * @return array
     */
    public function getFields() {
        return $this->fields;
    }

    /**
     * Get the @link Data::$length total amount of data fields@endlink.
     *
     * @return int
     */
    public function getLength() {
        return $this->length;
    }

    /**
     * Check if @link Data::$fields data fields array@endlink is empty.
     *
     * @return bool
     */
    public function isEmpty() {
        return empty($this->fields);
    }

    /**
     * Get the total amount of data rows.
     *
     * @return int
     */
    public function getRowCount() {
        if ($this->length > 0) {
            $fieldNames = array_keys($this->fields);
            $firstFieldName = $fieldNames[0];
            $this->rows = $this->getFieldByName($firstFieldName)->getRowCount();
        }
        return $this->rows;
    }

    /**
     * Get all data fields as an array.
     *
     * @param bool $groupedByFields Group by fields?
     * @return array
     */
    public function asArray($groupedByFields = false) {
        $result = array();
        $res = array();

        foreach ($this->fields as $fieldName => $field) {
            $result[$fieldName] = $field->getData();
        }
        if ($groupedByFields) {
            return $result;
        }

        $fieldNames = array_keys($this->fields);
        $rows = $this->getRowCount();
        for ($i = 0; $i < $rows; $i++) {
            foreach ($fieldNames as $fieldName) {
                $res[$i][$fieldName] = $result[$fieldName][$i];
            }
        }
        return $res;
    }
}
