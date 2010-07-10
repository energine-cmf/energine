<?php

/**
 * Класс JSONBuilder
 *
 * @package energine
 * @subpackage core
 * @author dr.Pavka
 * @copyright Energine 2006
 * @version $Id$
 */

//require_once('core/framework/Builder.class.php');

/**
 * Построитель данных в формат JSON (JavaScript Object Notation).
 *
 * @package energine
 * @subpackage core
 * @author dr.Pavka
 */
class JSONBuilder extends Builder {
    /**
     * Листалка
     *
     * @var Pager
     * @access private
     */
    private $pager = null;

    /**
     * @access private
     * @var array список ошибок
     * @todo зачем это!?
     */
    private $errors = array();

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
     * Создает результирующий JSON-объект.
     *
     * @access public
     * @return bool
     */
    public function build() {
        $result = false;

        if ($this->dataDescription == false) {
            throw new SystemException('ERR_DEV_NO_DATA_DESCRIPTION', SystemException::ERR_DEVELOPER);
        }

        $fields = $this->dataDescription->getFieldDescriptions();
        foreach ($fields as $fieldName => $fieldInfo) {
            $result['meta'][$fieldName] = array(
                'title' => $fieldInfo->getPropertyValue('title'),
                'type' => $fieldInfo->getType(),
                'key' => $fieldInfo->getPropertyValue('key')&&$fieldInfo->getPropertyValue('index')=='PRI'? true: false,
                'visible' => $fieldInfo->getPropertyValue('key')&&$fieldInfo->getPropertyValue('index')=='PRI' ? false : true,
                'name' => $fieldInfo->getPropertyValue('tableName')."[$fieldName]",
                'rights' => $fieldInfo->getRights(),
                'field' => $fieldName,
                'sort' => $fieldInfo->getPropertyValue('sort')
            );
            
        }

        if (!$this->data->isEmpty()) {
            for ($i = 0; $i < $this->data->getRowCount(); $i++) {
                foreach ($fields as $fieldName => $fieldInfo) {
                    $fieldType = $fieldInfo->getType();
                    $fieldValue = $this->data->getFieldByName($fieldName)->getRowData($i);
                    switch ($fieldType) {
                        case FieldDescription::FIELD_TYPE_DATETIME:
                        case FieldDescription::FIELD_TYPE_DATE:
                            if (!empty($fieldValue)) {
                                $fieldValue = self::enFormatDate($fieldValue, $fieldInfo->getPropertyValue('outputFormat'));
                            }
                            break;
                        case FieldDescription::FIELD_TYPE_IMAGE:
                            if ($fieldValue) {
                                if(file_exists(dirname($fieldValue).'/.'.basename($fieldValue)))
                                    $fieldValue = dirname($fieldValue).'/.'.basename($fieldValue);    
                            }
                        	break;    
                        case FieldDescription::FIELD_TYPE_SELECT:
                            $value = $fieldInfo->getAvailableValues();
                            if (isset($value[$fieldValue])) {
                                $fieldValue = $value[$fieldValue]['value'];
                            }
                            break;
                        default: // not used
                    }
                    if (is_null($fieldValue)) {
                        $fieldValue = '';
                    }
                    $result['data'][$i][$fieldName] = $fieldValue;
                }
            }
        }

        $result['result'] = true;
        $result['mode'] = 'select';

        $this->result = $result;

        return true;
    }

    /**
     * Возвращает результат работы построителя.
     *
     * @access public
     * @return string
     */
    public function getResult() {
        $result = $this->result;
        if (!is_null($this->pager)) {
        	$result['pager'] = array(
        	   'current' => $this->pager->getCurrentPage(),
        	   'count' => $this->pager->getNumPages()
        	);
        }
        $result = json_encode($result);
        return $result;
    }

    /**
     * Возвращает список ошибок.
     *
     * @return string
     * @access public
     * @todo зачем это!?
     */
    public function getErrors() {
        return json_encode($this->errors);
    }

    /**
     * Устанавливает кооличество страниц для листлки
     *
     * @param int
     * @return void
     * @access public
     */

    public function setPager($pager) {
        $this->pager = $pager;
    }
}
