<?php

/**
 * Класс Saver.
 *
 * @package energine
 * @subpackage core
 * @author dr.Pavka
 * @copyright Energine 2006
 * @version $Id$
 */


/**
 * Сохранитель данных в БД.
 *
 * @package energine
 * @subpackage core
 * @author dr.Pavka
 */
class Saver extends DBWorker {
    /**
     * @access private
     * @var array имена полей, в которых произошли ошибки
     */
    private $errors = array();

    /**
     * @access private
     * @var mixed условие SQL-запроса сохранения
     * @see QAL::select()
     */
    private $filter = null;

    /**
     * @access private
     * @var string режим сохранения
     * @see QAL::INSERT
     * @see QAL::UPDATE
     */
    private $mode = QAL::INSERT;

    /**
     * @access protected
     * @var DataDescription описание данных
     */
    protected $dataDescription = false;

    /**
     * @access protected
     * @var Data данные
     */
    protected $data = false;

   /**
    * @access private
    * @var mixed результат сохранения
    */
    private $result = false;

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
     * Устанавливает описание данных.
     *
     * @access public
     * @param DataDescription $dataDescription
     * @return void
     */
    public function setDataDescription(DataDescription $dataDescription) {
        $this->dataDescription = $dataDescription;
    }

    /**
     * Возвращает описание данных.
     *
     * @access public
     * @return DataDescription
     */
    public function getDataDescription() {
        return $this->dataDescription;
    }

    /**
     * Возвращает данные
     *
     * @return Data
     * @access public
     */

    public function getData() {
        return $this->data;
    }

    /**
     * Устанавливает данные.
     *
     * @access public
     * @param Data $data
     * @return void
     */
    public function setData(Data $data) {
        $this->data = $data;
    }

    /**
     * Устанавливает режим сохранения данных.
     *
     * @access public
     * @param string
     * @return void
     */
    public function setMode($mode) {
        $this->mode = $mode;
    }

    /**
     * Возвращает режим сохранения данных.
     *
     * @access public
     * @return string
     */
    public function getMode() {
        return $this->mode;
    }

    /**
     * Возвращает условие SQL-запроса сохранения.
     *
     * @access public
     * @return mixed
     */
    public function getFilter() {
        return $this->filter;
    }

    /**
     * Устанавливает условие SQL-запроса сохранения.
     *
     * @access public
     * @param mixed $filter
     * @return void
     */
    public function setFilter($filter) {
        $this->filter = $filter;
    }

    /**
     * Валидация сохраняемых данных.
     *
     * @access public
     * @return boolean
     * @todo возможность передачи в объект callback функции для пользовательской валидации
     */
    public function validate() {
        $result = false;

        if (!$this->data || !$this->dataDescription) {
            throw new SystemException('ERR_DEV_BAD_DATA', SystemException::ERR_DEVELOPER);
        }

        foreach ($this->dataDescription as $fieldName => $fieldDescription) {
            $fieldData = $this->data->getFieldByName($fieldName);
            if ($fieldDescription->getType() == FieldDescription::FIELD_TYPE_BOOL ||
                $fieldDescription->getType() == FieldDescription::FIELD_TYPE_PFILE ||
                $fieldName == 'lang_id' ||
                !is_null($fieldDescription->getPropertyValue('customField'))
                || $fieldDescription->getPropertyValue('nullable')
                ){
                continue;
            }
            // если нет данных в POST-запросе для какого-либо из полей
            elseif ($fieldData == false && $fieldName != 'lang_id') {
                $this->addError($fieldName);
                $result = false;
                break;
            }
            else {
                for ($i = 0; $i < $fieldData->getRowCount(); $i++) {
                    if (!$fieldDescription->validate($fieldData->getRowData($i))) {
                        $this->addError($fieldName);
                        $result = false;
                        break 2;
                    }
                }
                $result = true;
            }
        }
        return $result;
    }

    /**
     * Возвращает имена полей, в которых произошли ошибки.
     *
     * @access public
     * @return array
     */
    public function getErrors() {
        return $this->errors;
    }

    /**
     * Добавляет имя поле в набор ошибочных имён полей.
     *
     * @access public
     * @param string $fieldName
     * @return void
     */
    public function addError($fieldName) {
        array_push($this->errors, $fieldName);
    }

    /**
     * Сохранение данных.
     *
     * @access public
     * @return void
     */
    public function save() {
        $data = array();

        for ($i = 0; $i < $this->data->getRowCount(); $i++) {
            foreach ($this->dataDescription as $fieldName => $fieldInfo) {
            	// исключаем поля, которым нет соответствия в БД
                if (is_null($fieldInfo->getPropertyValue('customField')) && $this->data->getFieldByName($fieldName)) {
                    $fieldValue = $this->data->getFieldByName($fieldName)->getRowData($i);
                    if($fieldInfo->getType() == FieldDescription::FIELD_TYPE_HTML_BLOCK){
                    	$fieldValue = DataSet::cleanupHTML($fieldValue);
                    }    
            		// сохраняем поля из основной таблицы
            		if ($fieldInfo->isMultilanguage() == false && $fieldInfo->getPropertyValue('key') !== true && $fieldInfo->getPropertyValue('languageID') == false) {
            		    //Для типа флоат меняем запятые на точки
            		    if ($fieldInfo->getType() == FieldDescription::FIELD_TYPE_FLOAT) {
                            $fieldValue = str_replace(',', '.', $fieldValue);
            		    }
            			$data[$fieldInfo->getPropertyValue('tableName')][$fieldName] = $fieldValue;
            		}
            		elseif ($fieldInfo->isMultilanguage() || $fieldInfo->getPropertyValue('languageID')) {
            		    $data[$fieldInfo->getPropertyValue('tableName')][$this->data->getFieldByName('lang_id')->getRowData($i)][$fieldName] = $fieldValue;
            		}
            		elseif ($fieldInfo->getPropertyValue('key') === true) {
            		    $pkName = $fieldName; // имя первичного ключа
            		    $mainTableName = $fieldInfo->getPropertyValue('tableName'); // имя основной таблицы
            		}
            	}
            }
        }

        if ($this->getMode() == QAL::INSERT) {
            $data[$mainTableName] = (!isset($data[$mainTableName]))?array():$data[$mainTableName];
            $id = $this->dbh->modify(QAL::INSERT, $mainTableName, $data[$mainTableName]);
            unset($data[$mainTableName]);
        	foreach ($data as $tableName => $langRow) {
                foreach ($langRow as $row) {
                    $row[$pkName] = $id;
                	$result = $this->dbh->modify(QAL::INSERT, $tableName, $row);

                }
        	}
            $result = $id;
        }
        else {
            if (isset($data[$mainTableName])) {
                $result = $this->dbh->modify(QAL::UPDATE, $mainTableName, $data[$mainTableName], $this->getFilter());
            	unset($data[$mainTableName]);
            }
            foreach ($data as $tableName => $langRow) {
            	foreach ($langRow as $langID => $row) {
            	    try {
                        $result = $this->dbh->modify(QAL::INSERT, $tableName, array_merge($row, $this->getFilter()));
            	    }
            	    catch (Exception $e) {
                        $result = $this->dbh->modify(QAL::UPDATE , $tableName, $row, array_merge($this->getFilter(), array('lang_id' => $langID)));
            	    }
            	}
            }
            $result = true;
        }

        return ($this->result = $result);
    }

    /**
     * Возвращает результат сохранения данных.
     *
     * @access public
     * @return mixed
     */
    public function getResult() {
        return $this->result;
    }
}
