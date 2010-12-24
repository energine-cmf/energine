<?php

/**
 * Класс Field.
 *
 * @package energine
 * @subpackage core
 * @author dr.Pavka
 * @copyright Energine 2006
 */


/**
 * Поле данных.
 *
 * @package energine
 * @subpackage core
 * @author dr.Pavka
 */
class Field extends Object implements Iterator{

    /**
     * @access private
     * @var array набор дополнительных свойств
     */
    private $properties = array();

    /**
     * @access private
     * @var string имя поля
     */
    private $name;

    /**
     * @access private
     * @var array данные поля
     */
    private $data = array();

    /**
     * Если права не указаны - используются права из FieldDescription (наследуются).
     *
     * @access private
     * @var int права пользователя на поле
     */
    private $rights;

    /**
     * @access private
     * @var int индекс текущего элемента (используется для итерации)
     */
    private $currentIndex = 0;


    /**
     * Конструктор класса.
     *
     * @access public
     * @param string $name иям поля
     */
    public function __construct($name) {
        parent::__construct();

        $this->name = $name;
    }

    /**
     * Возвращает имя поля.
     *
     * @access public
     * @return string
     */
    public function getName() {
        return $this->name;
    }

    /**
     * Устанавливает данные поля.
     *
     * @access public
     * @param mixed $data
     * @param bool $setForAll - установить для всех строчек
     *  только для перепределения данных
     *  в случае если поле только создано то нужно заполнять через итератор 
     * @return void
     */
    public function setData($data, $setForAll = false) {
        if ($setForAll && $this->getRowCount()) {
        	$data = array_fill(0, $this->getRowCount(), $data);
        }
        elseif (!is_array($data)) {
            $data = array($data);
        }
        $this->data = $data;
    }

    /**
     * Возвращает данные поля.
     *
     * @access public
     * @return array
     */
    public function getData() {
        return $this->data;
    }

    /**
     * Возвращает данные указанной строки.
     *
     * @access public
     * @param int $rowIndex индекс строки
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
     * Удаляет данные указанной строки.
     *
     * @access public
     * @param int $rowIndex индекс строки
     * @return mixed
     */
    public function removeRowData($rowIndex) {
        $result = false;
        if (isset($this->data[$rowIndex])) {
        	unset($this->data[$rowIndex]);
        	$result = true;
        }
        return $result;
    }

    /**
     * Добавляет строку данных.
     *
     * @access public
     * @param mixed $data
     * @return void
     */
    public function addRowData($data) {
        $this->data[] = $data;
    }

    /**
     * Устанавливает данные в указанной строке.
     *
     * @access public
     * @param int $rowIndex индекс строки
     * @param mixed $newData новые данные
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
     * Устанавливает уровень прав на поле.
     *
     * @access public
     * @param int $rights уровень прав
     * @return void
     */
    public function setRights($rights) {
        $this->rights = $rights;
    }

    /**
     * Возвращает уровень прав на поле.
     *
     * @access public
     * @return int
     */
    public function getRights() {
        return $this->rights;
    }

    /**
     * Возвращает количество строк данных.
     *
     * @access public
     * @return int
     */
    public function getRowCount() {
        return sizeof($this->getData());
    }

    /**
     * Устанавливает дополнтельное свойство строки.
     *
     * @access public
     * @param int $index индекс строки
     * @param string $propertyName имя свойства
     * @param mixed $propertyValue значение свойства
     * @return void
     */
    public function setRowProperty($index, $propertyName, $propertyValue) {
        $this->properties[$index][$propertyName] = $propertyValue;
    }

    /**
     * Возвращает значение дополнительного свойства строки.
     *
     * @access public
     * @param int $index индекс строки
     * @param string $propertyName имя свойства
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
     * Возвращает все дополнительные свойства строки.
     *
     * @access public
     * @param int $index индекс строки
     * @return array
     */
    public function getRowProperties($index) {
        $result = false;
        if (isset($this->properties[$index]) && !empty($this->properties[$index])) {
            $result = $this->properties[$index];
        }
        return $result;
    }

    /**
     * Перемещает итератор на первый элемент.
     *
     * @access public
     * @return void
     */
    public function rewind() {
        $this->currentIndex = 0;
    }

    /**
     * Возвращает текущий элемент.
     *
     * @access public
     * @return mixed
     */
    public function current() {
        return $this->data[$this->currentIndex];
    }

    /**
     * Возвращает ключ текущего элемента.
     *
     * @access public
     * @return mixed
     */
    public function key() {
        return $this->currentIndex;
    }

    /**
     * Перемещает итератор на следующий элемент.
     *
     * @access public
     * @return void
     */
    public function next() {
        $this->currentIndex++;
    }

    /**
     * Проверяет, существует ли текущий элемент.
     *
     * @access public
     * @return boolean
     */
    public function valid() {
        return ($this->currentIndex < $this->getRowCount());
    }
}
