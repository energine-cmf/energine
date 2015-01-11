<?php
/**
 * @file
 * Saver.
 *
 * It contains the definition to:
 * @code
class Saver;
@endcode
 *
 * @author dr.Pavka
 * @copyright Energine 2006
 *
 * @version 1.0.0
 */
namespace Energine\share\gears;
use Energine\share\components\DataSet;

/**
 * Data saver into data base.
 *
 * @code
class Saver;
@endcode
 */
class Saver extends DBWorker {
    /**
     * Field names where errors occurred.
     * @var array $errors
     */
    private $errors = array();

    /**
     * Condition for save SQL-request.
     * @var mixed $filter
     *
     * @see QAL::select()
     */
    private $filter = null;

    /**
     * Save mode.
     * @var string $mode
     *
     * @see QAL::INSERT
     * @see QAL::UPDATE
     */
    private $mode = QAL::INSERT;

    /**
     * Data description.
     * @var DataDescription $dataDescription
     */
    protected $dataDescription = false;

    /**
     * Data.
     * @var Data $data
     */
    protected $data = false;

    /**
     * Result of saving.
     * @var mixed $result
     */
    private $result = false;

    public function __construct() {
        parent::__construct();
    }

    /**
     * Set data description.
     *
     * @param DataDescription $dataDescription Data description.
     */
    public function setDataDescription(DataDescription $dataDescription) {
        $this->dataDescription = $dataDescription;
    }

    /**
     * Get data description.
     *
     * @return DataDescription
     */
    public function getDataDescription() {
        return $this->dataDescription;
    }

    /**
     * Get data.
     *
     * @return Data
     */
    public function getData() {
        return $this->data;
    }

    /**
     * Set data.
     *
     * @param Data $data Data.
     */
    public function setData(Data $data) {
        $this->data = $data;
    }

    /**
     * Set save mode.
     *
     * @param string $mode Mode.
     */
    public function setMode($mode) {
        $this->mode = $mode;
    }

    /**
     * Get save mode.
     *
     * @return string
     */
    public function getMode() {
        return $this->mode;
    }

    /**
     * Get condition for save SQL-request.
     *
     * @return mixed
     */
    public function getFilter() {
        return $this->filter;
    }

    /**
     * Set condition for save SQL-request.
     *
     * @param mixed $filter Condition for save SQL-request.
     */
    public function setFilter($filter) {
        $this->filter = $filter;
    }

    /**
     * Validate date before saving.
     *
     * @return boolean
     *
     * @throws SystemException 'ERR_DEV_BAD_DATA'
     *
     * @todo возможность передачи в объект callback функции для пользовательской валидации
     */
    public function validate() {
        $result = false;

        if (!$this->getData() || !$this->getDataDescription()) {
            throw new SystemException('ERR_DEV_BAD_DATA', SystemException::ERR_DEVELOPER);
        }

        foreach ($this->getDataDescription() as $fieldName => $fieldDescription) {
            $fieldData = $this->getData()->getFieldByName($fieldName);
            if ($fieldDescription->getType() == FieldDescription::FIELD_TYPE_BOOL ||
                //$fieldDescription->getType() == FieldDescription::FIELD_TYPE_PFILE ||
                $fieldDescription->getType() == FieldDescription::FIELD_TYPE_FILE ||
                $fieldDescription->getType() == FieldDescription::FIELD_TYPE_CAPTCHA ||
                $fieldName == 'lang_id' ||
                !is_null($fieldDescription->getPropertyValue('customField'))
                || $fieldDescription->getPropertyValue('nullable')
            ) {
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
     * Get fields with errors.
     *
     * @return array
     */
    public function getErrors() {
        return $this->errors;
    }

    /**
     * Add field name where an error occurred.
     *
     * @param string $fieldName Field name.
     */
    public function addError($fieldName) {
        array_push($this->errors, $this->translate('FIELD_' . $fieldName));
    }

    /**
     * Save data.
     */
    public function save() {
        //Основные данные для сохранения
        $data = array();
        //Данные для сохранения в связанные таблицы
        //Начальное значение false, поскольку пустой массив будет говорить о том что поле существует. но ничего не выбрано
        $m2mData = false;
        $m2mFDs = $this->getDataDescription()->getFieldDescriptionsByType(FieldDescription::FIELD_TYPE_MULTI);
        if (!empty($m2mFDs)) {
            foreach ($m2mFDs as $fieldInfo) {
                if (is_null($fieldInfo->getPropertyValue('customField'))) {
                    //Определяем имя m2m таблицы
                    list($m2mTableName, $m2mPKName) = array_values($fieldInfo->getPropertyValue('key'));
                    //Определяем имя поля
                    $m2mInfo = $this->dbh->getColumnsInfo($m2mTableName);
                    unset($m2mInfo[$m2mPKName]);
                    $m2mData[$m2mTableName]['pk'] = $m2mPKName;
                }
            }
        }

        for ($i = 0; $i < $this->getData()->getRowCount(); $i++) {
            foreach ($this->getDataDescription() as $fieldName => $fieldInfo) {
                // исключаем поля, которым нет соответствия в БД
                if (is_null($fieldInfo->getPropertyValue('customField')) && $this->getData()->getFieldByName($fieldName)) {
                    $fieldValue = $this->getData()->getFieldByName($fieldName)->getRowData($i);
                    if ($fieldInfo->getType() == FieldDescription::FIELD_TYPE_HTML_BLOCK) {
                        $fieldValue = DataSet::cleanupHTML($fieldValue);
                    }
                    // сохраняем поля из основной таблицы
                    if ($fieldInfo->isMultilanguage() == false && $fieldInfo->getPropertyValue('key') !== true && $fieldInfo->getPropertyValue('languageID') == false) {
                        switch ($fieldInfo->getType()) {
                            case FieldDescription::FIELD_TYPE_FLOAT:
                                $fieldValue = str_replace(',', '.', $fieldValue);
                                break;
                            case FieldDescription::FIELD_TYPE_MULTI:
                                $m2mValues = $fieldValue;
                                //Поскольку мультиполе реально фейковое
                                //записываем в него NULL
                                $fieldValue = '';

                                /**
                                 * @todo необходимо оптимизировать алгоритм, а то произошло дублирование кода
                                 *
                                 */
                                //Определяем имя m2m таблицы
                                list($m2mTableName, $m2mPKName) = array_values($fieldInfo->getPropertyValue('key'));
                                //Определяем имя поля
                                $m2mInfo = $this->dbh->getColumnsInfo($m2mTableName);
                                unset($m2mInfo[$m2mPKName]);
                                foreach ($m2mValues as $val) {
                                    $m2mData[$m2mTableName]['pk'] = $m2mPKName;
                                    $m2mData[$m2mTableName][key($m2mInfo)][] = $val;
                                }
                                unset($m2mValues, $m2mPKName, $m2mInfo, $m2mTableName);
                                break;
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
            $data[$mainTableName] = (!isset($data[$mainTableName])) ? array() : $data[$mainTableName];
            $id = $this->dbh->modify(QAL::INSERT, $mainTableName, $data[$mainTableName]);
            unset($data[$mainTableName]);
            foreach ($data as $tableName => $langRow) {
                foreach ($langRow as $row) {
                    $row[$pkName] = $id;
                    /*$result = */
                    $this->dbh->modify(QAL::INSERT, $tableName, $row);

                }
            }
            $result = $id;
        }
        else {
            if (isset($data[$mainTableName])) {
                /*$result = */
                $this->dbh->modify(QAL::UPDATE, $mainTableName, $data[$mainTableName], $this->getFilter());
                unset($data[$mainTableName]);
            }
            foreach ($data as $tableName => $langRow) {
                foreach ($langRow as $langID => $row) {
                    try {
                        /*$result = */
                        $this->dbh->modify(QAL::INSERT, $tableName, array_merge($row, $this->getFilter()));
                    }
                    catch (\Exception $e) {
                        /*$result = */
                        $this->dbh->modify(QAL::UPDATE, $tableName, $row, array_merge($this->getFilter(), array('lang_id' => $langID)));
                    }
                }
            }
            if ($pkName)
                $result = $this->getData()->getFieldByName($pkName)->getRowData(0);
            else $result = true;
        }

        if (is_array($m2mData) && is_numeric($result)) {
            foreach ($m2mData as $tableName => $m2mInfo) {
                $this->dbh->modify(QAL::DELETE, $tableName, null, array($m2mInfo['pk'] => $result));
            }

            foreach ($m2mData as $tableName => $m2mInfo) {
                $pk = $m2mInfo['pk'];
                unset($m2mInfo['pk']);

                if ($m2mInfo)
                    foreach (current($m2mInfo) as $fieldValue) {
                        $this->dbh->modify(QAL::INSERT_IGNORE, $tableName, array(key($m2mInfo) => $fieldValue, $pk => $result));
                    }
            }

        }


        return ($this->result = $result);
    }

    /**
     * Get result of saving.
     *
     * @return mixed
     */
    public function getResult() {
        return $this->result;
    }
}

