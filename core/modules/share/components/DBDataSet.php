<?php
/**
 * @file
 * DBDataSet
 * It contains the definition to:
 * @code
class DBDataSet;
 * @endcode
 * @author dr.Pavka
 * @copyright Energine 2006
 * @version 1.0.0
 */
namespace Energine\share\components;

use Energine\share\gears, Energine\share\gears\SystemException, Energine\share\gears\FieldDescription, Energine\share\gears\MultiLanguageBuilder, Energine\share\gears\QAL;

/**
 * Class that shows the data from data base.
 * @code
class DBDataSet;
 * @endcode
 */
class DBDataSet extends DataSet {
    /**
     * Table name with translations.
     * @var string $translationTableName
     */
    private $translationTableName = false;

    /**
     * Primary key.
     * @var string $pk
     */
    private $pk = false;

    /**
     * Filter.
     * @var array $filter
     */
    private $filter = [];

    /**
     * Sort order.
     * @var mixed $order
     */
    private $order = null;

    /**
     * Limit of the record's amount.
     * @var array $limit
     */
    private $limit = null;

    /**
     * Previous state.
     * @var string $previousState
     * @note This is used by saving.
     */
    private $previousState = false;

    /*
     * @var FileRepository
     */
    //private $fileLibrary;
    /*
     * @var ImageManager
     */
    //private $imageManager;
    /*
     * @var source
     */
    //private $source;

    /**
     * @copydoc DataSet::__construct
     */
    public function __construct($name,  array $params = null) {
        parent::__construct($name, $params);
        $this->setType(self::COMPONENT_TYPE_LIST);
    }

    /**
     * @copydoc DataSet::defineParams
     */
    protected function defineParams() {
        return array_merge(
            parent::defineParams(),
            [
                'tableName' => false,
                'onlyCurrentLang' => false,
                'editable' => false
            ]
        );
    }

    /**
     * @copydoc DataSet::loadDataDescription
     * @throws SystemException 'ERR_DEV_NO_LANG_ID'
     * It returns the information about columns in main table and table of translations.
     */
    protected function loadDataDescription() {
        $result = $this->dbh->getColumnsInfo($this->getTableName());
        if ($this->getTranslationTableName()) {
            $transColumnsDescription = $this->dbh->getColumnsInfo($this->getTranslationTableName());
            foreach (array_keys($transColumnsDescription) as $fieldName) {
                //для всех полей кроме идентификатора языка и первичного ключа выставляем дополнительное свойство isMultiLanguage
                if (!in_array($fieldName, [$this->getPK(), 'lang_id'])) {
                    $transColumnsDescription[$fieldName]['isMultilanguage'] = true;
                } elseif ($fieldName === 'lang_id' && $this->getPK() !== 'lang_id') {
                    $transColumnsDescription[$fieldName]['languageID'] = true;
                }
            }
            $result += $transColumnsDescription;
            if (isset($result['lang_id'])) {
                $result['lang_id']['key'] = false;
            } else {
                throw new SystemException('ERR_DEV_NO_LANG_ID', SystemException::ERR_DEVELOPER);
            }
        }

        return $result;
    }

    /**
     * @copydoc DataSet::loadData
     */
    protected function loadData() {
        if ($this->pager) {
            // pager существует -- загружаем только часть данных, текущую страницу
            $this->setLimit($this->pager->getLimit());
        }

        //Если не существует таблицы с переводами, то выбираем данные из основной таблицы
        //Для мультиязычной таблицы - дергаем отдельный хитрый(сложный) метод загрузки
        $data = $this->modify(
            (!$this->getTranslationTableName()) ?
                $this->commonLoadData() :
                $this->multiLoadData()
        );

        return $data;
    }

    /**
     * Get data language.
     * @return int|false
     */
    protected function getDataLanguage() {
        $result = false;
        if ($this->getParam('onlyCurrentLang')) {
            $result = E()->getLanguage()->getCurrent();
        }

        return $result;
    }

    /**
     * Modify data set by adding the values from "m2m" table.
     * @param array $data Data.
     * @return array|false
     */
    protected function modify($data) {
        if (!is_array($data)) {
            return $data;
        }

        //Перечень мультиполей
        $multiFields = $this->getDataDescription()->getFieldDescriptionsByType(FieldDescription::FIELD_TYPE_MULTI);
        //Loading m2m data
        if (!empty($multiFields)) {
            $m2mData = [];
            $primaryKeyName = $this->getPK();
            $pks = simplifyDBResult($data, $primaryKeyName);

            //create storage array
            //array($MultiFieldName => array($pk => $values))
            foreach ($multiFields as $mfd) {
                $relInfo = $mfd->getPropertyValue('key');
                if (is_array($relInfo) && $this->dbh->tableExists($relInfo['tableName'])) {
                    $res = $this->dbh->select(
                        $relInfo['tableName'],
                        true,
                        [
                            $primaryKeyName => $pks
                        ]
                    );
                    if ($res) {
                        foreach ($res as $row) {
                            $pk = $row[$relInfo['fieldName']];
                            unset($row[$relInfo['fieldName']]);
                            $m2mData[$mfd->getName()][$pk][] = current($row);
                        }
                    }
                } else {
                    foreach (array_keys($data) as $key) {
                        if (isset($data[$key][$mfd->getName()]) && $data[$key][$mfd->getName()]) {
                            $data[$key][$mfd->getName()] = explode(',', $data[$key][$mfd->getName()]);
                        }
                    }
                }
            }

            //Проходимся по всем данным
            foreach ($data as $key => $row) {
                //потом по multi полям
                foreach ($m2mData as $fieldName => $m2mValues) {
                    //Если в списке полей данных существует мультиполе с этим именем
                    if (array_key_exists($fieldName, $row)) {
                        //
                        foreach ($m2mValues as $pk => $values) {
                            if ($row[$primaryKeyName] == $pk) {
                                $data[$key][$fieldName] = $values;
                            }
                        }
                    }
                }
            }
        }

        $lookupField =
            $this->getDataDescription()->getFieldDescriptionsByType(FieldDescription::FIELD_TYPE_LOOKUP);

        if (!empty($lookupField)) {
            //Готовим инфу для получения данных их связанных таблиц
            foreach ($lookupField as $valueFieldName => $valueField) {
                $relInfo = $valueField->getPropertyValue('key');

                if (is_array($relInfo)) {
                    $langTable = $this->dbh->getTranslationTablename($relInfo['tableName']);
                    $relations[$valueFieldName] = [
                        'table' => (!$langTable) ? $relInfo['tableName'] : $langTable,
                        'field' => $relInfo['fieldName'],
                        'lang' => ($langTable) ? E()->getLanguage()->getCurrent() : false,
                        'valueField' => substr($relInfo['fieldName'], 0, strrpos($relInfo['fieldName'], '_')) . '_name'
                    ];

                    $cond = [
                        $relations[$valueFieldName]['field'] => simplifyDBResult($data, $valueFieldName)
                    ];


                    if ($relations[$valueFieldName]['lang']) {
                        $cond['lang_id'] = $relations[$valueFieldName]['lang'];
                    }
                    $values[$valueFieldName] = convertDBResult($this->dbh->select($relations[$valueFieldName]['table'],
                        [$relations[$valueFieldName]['field'], $relations[$valueFieldName]['valueField']], $cond),
                        $relations[$valueFieldName]['field'], true);

                }

            }

            unset($lookupField, $langTable, $relInfo);
            foreach ($data as $key => $row) {
                foreach ($row as $name => $value) {
                    if (in_array($name, array_keys($relations)) && is_array($values[$name]) && array_key_exists($value,
                            $values[$name])
                    ) {
                        $data[$key][$name] = [
                            'id' => $value,
                            'value' => $values[$name][$value][$relations[$name]['valueField']]
                        ];
                    }
                }
            }
        }

        return $data;
    }

    /**
     * Load data from the common table.
     * @return array | bool
     */
    private function commonLoadData() {
        $dbFields = [];
        $data = false;

        foreach ($this->getDataDescription() as $fieldName => $field) {
            if (is_null($field->getPropertyValue('customField')) && ($field->getType() != FieldDescription::FIELD_TYPE_TAB)) {
                if (
                ($field->getPropertyValue('origType') && ($field->getType() == FieldDescription::FIELD_TYPE_BOOL))
                ) {
                    $fieldName = ' IF((' . $fieldName . ' IS NOT NULL) AND (' . $fieldName . ' <> ""), 1, 0) AS ' . $fieldName;
                }
                array_push($dbFields, $fieldName);
            }
        }
        //Если не пустой массив полей для отбора
        if (!empty($dbFields)) {
            if ($this->getType() == self::COMPONENT_TYPE_FORM_ADD) {
                $dbFields = array_flip($dbFields);
                foreach ($dbFields as $key => $value) {
                    $dbFields[$key] = '';
                }
                $res = [$dbFields];
            } else {
                $res = $this->dbh->select($this->getTableName(), (($this->pager) ? ' SQL_CALC_FOUND_ROWS '
                        : '') . implode(',', $dbFields), $this->getFilter(), $this->getOrder(), $this->getLimit());
            }

            if ($res) {
                $data = $res;
                if ($this->pager) {
                    $this->pager->setRecordsCount($this->dbh->getScalar('SELECT FOUND_ROWS() as c'));
                }
            }
        }
        return $data;
    }

    /**
     * Load multilingual data.
     * @return array|bool|mixed
     */
    private function multiLoadData() {
        $data = false;
        $lang = E()->getLanguage();
        $lang = $lang->getLanguages();
        $dbFields = [];
        $filter = $order = $limit = '';
        //Создаем перечень полей  в формате array('имя основной таблицы' => array('имя поля'=>'имя таблицы.имя поля'), 'имя таблицыпереводов' => array('имя поля'=>'имя таблицы.имя поля'))
        foreach ($this->getDataDescription() as $fieldName => $field) {
            //Не включаем в набор идентификатор языка и PK
            if (!$field->getPropertyValue('languageID') && $field->getPropertyValue('key') !== true) {

                //не включаем в набор поля полученные  из конфигурации
                if (is_null($field->getPropertyValue('customField'))) {
                    //поля не приведенные к булеану
                    if (
                    !($field->getPropertyValue('origType') && ($field->getType() == FieldDescription::FIELD_TYPE_BOOL))
                    ) {
                        $dbFields[$field->getPropertyValue('tableName')][$fieldName] = $field->getPropertyValue('tableName') . '.' . $fieldName;
                    } //поля приведенные к булеану из родного типа данных
                    else {
                        $dbFields[$field->getPropertyValue('tableName')][$fieldName] =
                            ' IF((' . $field->getPropertyValue('tableName') . '.' . $fieldName . ' IS NOT NULL) AND (' . $field->getPropertyValue('tableName') . '.' . $fieldName . ' <> ""), 1, 0) AS ' . $fieldName;
                    }
                }
            }
        }

        $filterCondition = $this->getFilter();
        if (!empty($filterCondition)) {
            $filter = $this->dbh->buildWhereCondition($filterCondition) . ($this->getParam('onlyCurrentLang')
                    ? ' AND lang_id = ' . $this->getDataLanguage() : '');
        } elseif ($this->getDataLanguage() && $this->getParam('onlyCurrentLang')) {
            $filter = ' WHERE lang_id = ' . $this->getDataLanguage();
        }
        if ($this->getOrder()) {
            $order = $this->dbh->buildOrderCondition($this->getOrder());
        }

        if (!is_null($this->getLimit())) {
            $limit = $this->getLimit();
            $limit = $this->dbh->buildLimitStatement($limit);
        }

        //Если существует листалка указываем ей количество записей

        if ($this->getType() != self::COMPONENT_TYPE_FORM_ADD) {
            $request = sprintf(
                'SELECT ' . (($this->pager) ? ' SQL_CALC_FOUND_ROWS ' : '') .
                ' %s.%s, %s.lang_id,
        	       %s
        	       %s
        	       FROM %1$s
        	       LEFT JOIN %3$s ON %3$s.%2$s = %1$s.%2$s
        	       %s
        	       %s
        	       %s
        	       ',
                $this->getTableName(), $this->getPK(), $this->getTranslationTableName(),
                (isset($dbFields[$this->getTableName()])) ? implode(',', $dbFields[$this->getTableName()]) : '',
                isset($dbFields[$this->getTranslationTableName()]) ? ((isset($dbFields[$this->getTableName()])) ? ','
                        : '') . implode(',', $dbFields[$this->getTranslationTableName()]) : '',
                $filter,
                $order,
                $limit
            );
            $data = $this->dbh->select($request);
            if ($this->pager) {
                $this->pager->setRecordsCount($this->dbh->getScalar('SELECT FOUND_ROWS() as c'));
            }
            //Если данные не только для текущего языка
            if ($data && (!$this->getDataLanguage() || $this->getDataLanguage() && !$this->getParam('onlyCurrentLang') && isset($dbFields[$this->getTranslationTableName()]))) {

                //формируем матрицу
                foreach ($data as $row) {
                    $matrix[$row[$this->getPK()]][$row['lang_id']] = $row;
                }
                //формируем образец
                //в нем все языкозависимые поля заполнены nullами
                foreach (array_keys($dbFields[$this->getTranslationTableName()]) as $fieldName) {
                    $translationColumns[] = 'NULL as ' . $fieldName;
                }
                $request = sprintf('
                    SELECT %s, %s %s
                    FROM %s
                    WHERE %s IN(%s)
                ',
                    $this->getPK(), (isset($dbFields[$this->getTableName()]))
                        ? implode(',', $dbFields[$this->getTableName()]) . ','
                        : '', implode(',', $translationColumns),
                    $this->getTableName(),
                    $this->getPK(), implode(',', array_keys($matrix)));
                $res = $this->dbh->select($request);

                foreach ($res as $row) {
                    $template[$row[$this->getPK()]] = $row;
                }

                $data = [];

                if ($this->getDataLanguage() && !$this->getParam('onlyCurrentLang')) {
                    $lang = [$this->getDataLanguage() => $lang[$this->getDataLanguage()]];
                }

                foreach ($matrix as $ltagID => $langVersions) {
                    foreach (array_keys($lang) as $langID) {
                        if (isset($langVersions[$langID])) {
                            $data[] = $langVersions[$langID];
                        } else {
                            $data[arrayPush($data, $template[$ltagID])]['lang_id'] = $langID;
                        }
                    }
                }
            }
        } else {
            $i = 0;
            $dbFields = array_merge(
                (isset($dbFields[$this->getTableName()])) ? array_keys($dbFields[$this->getTableName()]) : [],
                array_keys($dbFields[$this->getTranslationTableName()])
            );
            $dbFields = array_flip($dbFields);
            foreach ($dbFields as $key => $value) {
                $dbFields[$key] = '';
            }
            foreach (array_keys($lang) as $langID) {
                $data[$i][$this->getPK()] = null;
                $data[$i]['lang_id'] = $langID;
                $data[$i] = array_merge($data[$i], $dbFields);
                $i++;
            }
        }

        return $data;
    }

    /**
     * Set table name.
     * @param string $tableName Table name.
     */
    protected function setTableName($tableName) {
        $this->setParam('tableName', $tableName);
    }

    /**
     * @copydoc Component::setParam
     */
    protected function setParam($name, $value) {
        // Для параметра tableName устанавливаем еще и имя таблицы переводов
        if ($name == 'tableName') {
            $this->translationTableName = $this->dbh->getTranslationTablename($value);
        }

        parent::setParam($name, $value);
    }

    /**
     * Get table name.
     * @return string
     * @final
     * @throws SystemException 'ERR_DEV_NO_TABLENAME'
     */
    final public function getTableName() {
        if (!$this->getParam('tableName')) {
            throw new SystemException('ERR_DEV_NO_TABLENAME', SystemException::ERR_DEVELOPER);
        }

        return $this->getParam('tableName');
    }

    /**
     * Get filter.
     * @return mixed
     * @final
     */
    final public function getFilter() {
        return $this->filter;
    }

    /**
     * Set filter.
     * @param mixed $filter Filter.
     * @final
     * @see QAL::select()
     */
    final protected function setFilter($filter) {
        $this->clearFilter();
        if (!empty($filter)) {
            $this->addFilterCondition($filter);
        }
    }

    /**
     * Add filter condition.
     */
    public function addFilterCondition($filter) {
        if (is_numeric($filter)) {
            $filter = [$this->getTableName() . '.' . $this->getPK() => $filter];
        } elseif (is_string($filter)) {
            $filter = [$filter];
        }
        $this->filter = array_merge($this->filter, $filter);
    }

    /**
     * Reset filter.
     * @final
     */
    final protected function clearFilter() {
        $this->filter = [];
    }

    /**
     * Get sort order.
     * @return mixed
     * @final
     */
    final public function getOrder() {
        if (is_null($this->order)) {
            $this->order = false;
            $columns = $this->dbh->getColumnsInfo($this->getTableName());
            foreach (array_keys($columns) as $columnName) {
                if (strpos($columnName, '_order_num')) {
                    $this->setOrder([$columnName => QAL::ASC]);
                    break;
                }
            }
        }

        return $this->order;
    }

    /**
     * Set sort order.
     * @param string | array $order Sort order in the form @code array($orderFieldName => $orderDirection) @endcode
     * @final
     */
    final protected function setOrder($order) {
        $this->order = $order;
    }

    /**
     * Get limit.
     * @return array
     * @final
     */
    final protected function getLimit() {
        return $this->limit;
    }

    /**
     * Set limit.
     * @param array $limit Limit.
     * @final
     */
    final protected function setLimit(array $limit) {
        $this->limit = $limit;
    }


    /**
     * Get filed name of primary key.
     * @return string
     * @final
     * @throws SystemException 'ERR_DEV_NO_PK'
     */
    final public function getPK() {
        if (!$this->pk) {
            $res = $this->dbh->getColumnsInfo($this->getTableName());
            if (is_array($res)) {
                foreach ($res as $fieldName => $fieldInfo) {
                    if ($fieldInfo['key'] === true) {
                        $this->pk = $fieldName;
                    }
                }
                if (!isset($this->pk)) {
                    throw new SystemException('ERR_DEV_NO_PK', SystemException::ERR_DEVELOPER);
                }
            } else {
                throw new SystemException('ERR_DEV_NO_PK', SystemException::ERR_DEVELOPER);
            }

        }

        return $this->pk;
    }

    /**
     * Set primary key.
     * @param string $primaryColumnName Primary column name.
     */
    final protected function setPK($primaryColumnName) {
        $this->pk = $primaryColumnName;
    }

    /**
     * @copydoc DataSet::createBuilder
     */
    protected function createBuilder() {
        if (!$this->getTranslationTableName()) {
            $result = parent::createBuilder();
        } else {
            $result = new MultiLanguageBuilder();
        }

        return $result;
    }

    /**
     * @copydoc DataSet::createDataDescription
     */
    protected function createDataDescription() {
        $result = parent::createDataDescription();
        foreach ($result as $fieldMetaData) {
            $keyInfo = $fieldMetaData->getPropertyValue('key');
            $values = false;
            //Если это внешний ключ и не в режиме списка
            if (is_array($keyInfo)) {
                switch ($fieldMetaData->getType()) {
                    case FieldDescription::FIELD_TYPE_SELECT:
                        $fkTableName = $keyInfo['tableName'];
                        $fkKeyName = $keyInfo['fieldName'];
                        //загружаем информацию о возможных значениях
                        $values = $this->getFKData($fkTableName, $fkKeyName);
                        break;
                    case FieldDescription::FIELD_TYPE_MULTI:
                        $m2mTableName = $keyInfo['tableName'];
                        $m2mPKName = $keyInfo['fieldName'];
                        //Если существует таблица связанная
                        if ($this->dbh->tableExists($m2mTableName)) {
                            $tableInfo = $this->dbh->getColumnsInfo($m2mTableName);
                            unset($tableInfo[$m2mPKName]);
                            $m2mValueFieldInfo = current($tableInfo);
                            if (isset($m2mValueFieldInfo['key']) && is_array($m2mValueFieldInfo)) {
                                $values = $this->getFKData($m2mValueFieldInfo['key']['tableName'],
                                    $m2mValueFieldInfo['key']['fieldName']);
                            }
                        }
                        //если нет значит это забота программиста наполнить значениями
                        break;
                    case FieldDescription::FIELD_TYPE_LOOKUP:
                        if ($editor = $fieldMetaData->getPropertyValue('editor')) {
                            $url = $fieldMetaData->getName() . '-' . $editor;
                        } else {
                            $url = $fieldMetaData->getName();
                        }
                        //Problem with safari where urlencoded backslash(%5C) converted into backslash
                        $fieldMetaData->setProperty('url', '/' . str_replace('\\', '.', $url) . '/lookup/');
                        $table = $keyInfo['tableName'];
                        if ($this->dbh->tableExists($table)) {
                            if ($t = $this->dbh->getTranslationTablename($table)) {
                                $table = $t;
                                unset($t);
                            }
                            $valueFieldName = array_reduce(array_keys($this->dbh->getColumnsInfo($table)),
                                function ($str, $row) {

                                    if (strpos($row, '_name')) {
                                        return $row;
                                    }

                                    return $str;
                                });
                            $fieldMetaData->setProperty('value_field', $valueFieldName);
                            $fieldMetaData->setProperty('value_table', $table);
                            $fieldMetaData->setProperty('key_field', $keyInfo['fieldName']);
                        }
                        break;
                }
                if (!empty($values)) {
                    call_user_func_array([$fieldMetaData, 'loadAvailableValues'], $values);
                }
            }
        }

        return $result;
    }

    /**
     * Get data from linked table.
     * @param string $fkTableName Linked table name.
     * @param string $fkKeyName Key name.
     * @return array
     */
    protected function getFKData($fkTableName, $fkKeyName) {
        return $this->dbh->getForeignKeyData($fkTableName, $fkKeyName, E()->getLanguage()->getCurrent());
    }

    /**
     * Get table name with translations.
     * @return string
     */
    protected function getTranslationTableName() {
        return $this->translationTableName;
    }

    /**
     * Show view form.
     * @throws SystemException 'ERR_404'
     */
    protected function view() {
        $this->setType(self::COMPONENT_TYPE_FORM);
        //$this->addCrumb('TXT_VIEW_ITEM');
        $id = $this->getStateParams();
        list($id) = $id;
        if (!$this->recordExists($id)) {
            throw new SystemException('ERR_404', SystemException::ERR_404);
        }
        $this->addFilterCondition([$this->getTableName() . '.' . $this->getPK() => $id]);

        $this->prepare();
        foreach ($this->getDataDescription() as $fieldDescription) {
            $fieldDescription->setMode(FieldDescription::FIELD_MODE_READ);
        }
    }

    /**
     * Detect if the specific record exist.
     * @param string $id ID.
     * @param mixed $fieldName Field name.
     * @return bool
     */
    protected function recordExists($id, $fieldName = false) {
        if (!$fieldName) {
            $fieldName = $this->getPK();
        }

        $res = $this->dbh->select($this->getTableName(), [$fieldName], [$fieldName => $id]);
        return !empty($res);
    }

    /**
     * @copydoc DataSet::buildJS
     */
    protected function buildJS() {
        $result = parent::buildJS();
        if ((($this->getState() == 'view') && $this->document->isEditable() && $this->getParam('editable')) || in_array($this->getState(),
                ['add', 'edit'])
        ) {

            if ($this->document->isEditable()) {
                $this->setProperty('editable', 'editable');
            }

            $this->addWYSIWYGTranslations();
            if ($config = E()->getConfigValue('wysiwyg.styles')) {
                if (!$result) {
                    $result = $this->doc->createElement('javascript');
                }
                $JSObjectXML = $this->doc->createElement('variable');
                $JSObjectXML->setAttribute('name', 'wysiwyg_styles');
                $JSObjectXML->setAttribute('type', 'json');
                foreach ($config as $key => $value) {
                    if (isset($value['caption'])) {
                        $config[$key]['caption'] = $this->translate($value['caption']);
                    }
                }
                $JSObjectXML->appendChild(new \DomText(json_encode($config)));
                $result->appendChild($JSObjectXML);
            }
        }


        return $result;
    }

    /**
     * Get previous state.
     * @return string
     * @throws SystemException ERR_NO_COMPONENT_ACTION
     * @final
     */
    final protected function getPreviousState() {
        if (!$this->previousState) {
            if (!isset($_POST['componentAction'])) {
                return self::DEFAULT_STATE_NAME;
            } else {
                $this->previousState = $_POST['componentAction'];
            }
        }

        return $this->previousState;
    }

    /**
     * Save text.
     */
    protected function saveText() {
        $result = '';
        if ($this->getParam('editable') && isset($_POST['ID']) && isset($_POST['num']) && isset($_POST['data'])) {
            $result = DataSet::cleanupHTML($_POST['data']);
            $langID = E()->getLanguage()->getCurrent();
            $entityId = (int)$_POST['ID'];
            $field = $_POST['num'];
            $this->dbh->modify(gears\QAL::UPDATE, $this->getTranslationTableName(), [$field => $result],
                ['lang_id' => $langID, $this->getPK() => $entityId]);

        }

        $this->response->setHeader('Content-Type', 'application/xml; charset=utf-8');
        $this->response->write($result);
        $this->response->commit();
    }
}

