<?php 

/**
 * Содержит класс CalendarBuilder
 *
 * @package energine
 * @subpackage calendar
 * @author d.pavka
 * @copyright d.pavka@gmail.com
 */

/**
 * Построитель календаря
 *
 * @package energine
 * @subpackage calendar
 * @author d.pavka@gmail.com
 */
class CalendarBuilder extends Builder {
    /**
     * Конструктор класса
     *
     * @access public
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Создаёт XML-описание поля данных.
     *
     * @access protected
     * @param string $fieldName
     * @param FieldDescription $fieldInfo
     * @param mixed $fieldValue
     * @param mixed $fieldProperties
     * @return DOMNode
     */
    protected function createField($fieldName, FieldDescription $fieldInfo, $fieldValue = false, $fieldProperties = false) {
        $result = $this->result->createElement('field');

        foreach ($fieldInfo as $propName => $propValue) {
            if(!in_array($propName, array('pattern', 'message', 'sort', 'outputFormat', 'tabName'))) {
                $result->setAttribute($propName, $propValue);
            }
        }

        if ($fieldProperties) {
            foreach ($fieldProperties as $propName => $propValue) {
                $result->setAttribute($propName, $propValue);
            }
        }

        if (!empty($fieldValue)) {
            $result->nodeValue = $fieldValue;
        }

        return $result;
    }
}