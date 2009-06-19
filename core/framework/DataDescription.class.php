<?php

/**
 * Класс DataDescription.
 *
 * @package energine
 * @subpackage core
 * @author dr.Pavka
 * @copyright Energine 2006
 * @version $Id$
 */

//require_once('core/framework/Object.class.php');
//require_once('core/framework/FieldDescription.class.php');

/**
 * Мета-данные.
 *
 * @package energine
 * @subpackage core
 * @author dr.Pavka
 */
class DataDescription extends Object implements Iterator {

    /**
     * @access private
     * @var array мета-данные полей
     */
    private $fieldDescriptions;

    /**
     * @access private
     * @var int количество полей данных
     */
    private $length;

    /**
     * @access private
     * @var int индекс текущего элемента (используется для итерации)
     */
    private $currentIndex = 0;

    /**
     * Конструктор класса.
     *
     * @access public
     * @return void
     */
    public function __construct() {
        parent::__construct();

        $this->fieldDescriptions = array();
        $this->length = 0;
    }

    /**
     * Загружает описание данных полученных из БД.
     *
     * @access public
     * @param array $columnsInfo
     * @return void
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
     * Загружает описание данных полученных из конфигурационного XML файла.
     *
     * @access public
     * @param SimpleXMLElement $xmlDescr
     * @return void
     */
    public function loadXML(SimpleXMLElement $xmlDescr) {
        if (!empty($xmlDescr))
        foreach ($xmlDescr->field as $fieldXmlDescr) {
            $fieldDescr = new FieldDescription();
            $fieldDescr->loadXML($fieldXmlDescr);
            $this->addFieldDescription($fieldDescr);
        }
    }

    /**
     * Добавляет описание поля данных.
     *
     * @access public
     * @param FieldDescription $fieldDescription
     * @return void
     */
    public function addFieldDescription(FieldDescription $fieldDescription) {
        $this->fieldDescriptions[$fieldDescription->getName()] = $fieldDescription;
        $this->length++;
    }

    /**
     * Удаляет описание поля данных.
     *
     * @access public
     * @param FieldDescription $fieldDescription
     * @return void
     */
    public function removeFieldDescription(FieldDescription $fieldDescription) {
        if (isset($this->fieldDescriptions[$fieldDescription->getName()])) {
        	unset($this->fieldDescriptions[$fieldDescription->getName()]);
        	$this->length--;
        }
    }

    /**
     * Возвращает описание поля данных по имени поля,
     * или false, если такого поля не существует.
     *
     * @access public
     * @param string $name
     * @return FieldDescription
     */
    public function getFieldDescriptionByName($name) {
        $fieldDescription = false;
        if (isset($this->fieldDescriptions[$name])) {
            $fieldDescription = $this->fieldDescriptions[$name];
        }
        return $fieldDescription;
    }

    /**
     * Возвращает описания полей данных.
     *
     * @access public
     * @return array
     */
    public function getFieldDescriptions() {
        return $this->fieldDescriptions;
    }

    /**
     * Возвращает список имён полей данных.
     *
     * @return array
     * @access public
     */
    public function getFieldDescriptionList() {
        return array_keys($this->fieldDescriptions);
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
     * Создаёт пересечение описания данных с другим описанием данных.
     *
     * @access public
     * @param DataDescription $otherDataDescr
     * @return DataDescription
     */
    public function intersect(DataDescription $otherDataDescr) {
        $result = false;
        if ($this->getLength() == 0) {
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
                    $this->getFieldDescriptionByName($fieldName)->addProperty('customField', 'customField');
                }
            }
            $result = $this;
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
        $fieldNames = $this->getFieldDescriptionList();
        return $this->fieldDescriptions[$fieldNames[$this->currentIndex]];
    }

    /**
     * Возвращает ключ текущего элемента.
     *
     * @access public
     * @return mixed
     */
    public function key() {
        $fieldNames = $this->getFieldDescriptionList();
        return $fieldNames[$this->currentIndex];
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
        return ($this->currentIndex < $this->length);
    }
}
