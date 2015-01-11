<?php
/**
 * @file
 * Field.
 *
 * It contains the definition to:
 * @code
class Field;
@endcode
 *
 * @author dr.Pavka
 * @copyright Energine 2006
 *
 * @version 1.0.0
 */
namespace Energine\share\gears;

/**
 * Data field.
 *
 * @code
class Field;
@endcode
 */
class Field extends Object implements \Iterator {
    /**
     * Set of additional properties.
     * @var array $properties
     */
    private $properties = array();

    /**
     * Field name.
     * @var string $name
     */
    private $name;

    /**
     * Data of the field.
     * @var array $data
     */
    private $data = array();

    /**
     * User rights for the field.
     * If the rights are not set, then the derived rights from FieldDescription will be used.
     *
     * @var int $rights
     */
    private $rights;

    /**
     * Index of the current element.
     * Used for iteration.
     *
     * @var int $currentIndex
     */
    private $currentIndex = 0;


    /**
     * @param string $name Field name.
     */
    public function __construct($name) {
        $this->name = $name;
    }

    /**
     * Get the name.
     *
     * @return string
     */
    public function getName() {
        return $this->name;
    }

    /**
     * Set data for the field.
     *
     * @param mixed $data Data.
     * @param bool $setForAll Defines whether the data should be set for all rows (only for data overwriting). In the case that the field is newly created - fill it by using iterator.
     * @return Field
     */
    public function setData($data, $setForAll = false) {
        if ($setForAll && $this->getRowCount()) {
            $data = array_fill(0, $this->getRowCount(), $data);
        }
        elseif ($setForAll && !$this->getRowCount() && !is_array($data)) {
            $rowData = $data;
            $data = array();
            for ($i = 0; $i < sizeof(E()->getLanguage()->getLanguages()); $i++) {
                $data[$i] = $rowData;
            }
        }
        elseif (!is_array($data)) {
            $data = array($data);
        }
        $this->data = $data;
        return $this;
    }

    /**
     * Get field data.
     *
     * @return array
     */
    public function getData() {
        return $this->data;
    }

    /**
     * Get data from specific row.
     *
     * @param int $rowIndex Row index.
     * @return mixed
     */
    public function getRowData($rowIndex) {
        $result = null;
        if (isset($this->data[$rowIndex])) {
            $result = $this->data[$rowIndex];
        }
        return $result;
    }

    /**
     * Remove data from specific row.
     *
     * @param int $rowIndex Row index
     * @return bool
     */
    public function removeRowData($rowIndex) {
        unset($this->data[$rowIndex]);
        //$this->data = array_values($this->data);
    }

    /**
     * Add new data row.
     *
     * @param mixed $data Data.
     * @param bool $toEnd Defines, whether the data should be appended to the end. Otherwise they will be appended to the beginning.
     */
    public function addRowData($data, $toEnd = true) {
        if ($toEnd)
            $this->data[] = $data;
        else {
            array_unshift($this->data, $data);
        }
    }

    //todo VZ: Why bool is returned?
    /**
     * Set the data for specific row.
     *
     * @param int $rowIndex Row index
     * @param mixed $newData New data.
     * @return boolean
     */
    public function setRowData($rowIndex, $newData) {
        $result = false;
        //if (isset($this->data[$rowIndex])) {
        $this->data[$rowIndex] = $newData;
        $result = true;
        //}
        return $result;
    }

    /**
     * Set rights for the field.
     *
     * @param int $rights Rights.
     */
    public function setRights($rights) {
        $this->rights = $rights;
    }

    /**
     * Get rights for the field.
     *
     * @return int
     */
    public function getRights() {
        return $this->rights;
    }

    /**
     * Get the total amount of the rows.
     *
     * @return int
     */
    public function getRowCount() {
        return sizeof($this->getData());
    }

    /**
     * Set additional property for the specific row.
     *
     * @param int $index Row index
     * @param string $propertyName Property name.
     * @param mixed $propertyValue Property value.
     */
    public function setRowProperty($index, $propertyName, $propertyValue) {
        $this->properties[$index][$propertyName] = $propertyValue;
    }

    /**
     * Get the additional property value of the specific row.
     *
     * @param int $index Row index
     * @param string $propertyName Property value.
     * @return mixed
     */
    public function getRowProperty($index, $propertyName) {
        $result = false;
        if (isset($this->properties[$index][$propertyName])) {
            $result = $this->properties[$index][$propertyName];
        }
        return $result;
    }

    /**
     * Get all additional properties of the specific row.
     *
     * @param int $index Row index.
     * @return array
     */
    public function getRowProperties($index) {
        $result = false;
        if (isset($this->properties[$index]) && !empty($this->properties[$index])) {
            $result = $this->properties[$index];
        }
        return $result;
    }

    public function rewind() {
        $this->currentIndex = 0;
    }

    public function current() {
        return $this->data[$this->currentIndex];
    }

    public function key() {
        return $this->currentIndex;
    }

    public function next() {
        $this->currentIndex++;
    }

    public function valid() {
        return ($this->currentIndex < $this->getRowCount());
    }
}
