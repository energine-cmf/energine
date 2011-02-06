<?php
/**
 * Содержит класс DBDataSet
 *
 * @package energine
 * @subpackage share
 * @author dr.Pavka
 * @copyright Energine 2006
 */

/**
 * Класс позволяющий выводить  данные из БД
 *
 * @package energine
 * @subpackage share
 * @author dr.Pavka
 */
class DBDataSet extends DataSet {

    /**
     * Имя таблицы содержащей переводы
     *
     * @var string
     * @access private
     */
    private $translationTableName = false;

    /**
     * Имя поля первичного ключа
     *
     * @var string
     * @access private
     */
    private $pk = false;

    /**
     * Условия выборки
     *
     * @var array
     * @access private
     */
    private $filter = array();

    /**
     * Условие сортировки
     *
     * @var mixed
     * @access protected
     */
    private $order = null;

    /**
     * Ограничение количества записей
     *
     * @var array
     * @access private
     */
    private $limit = null;

    /**
     * Действие которое исполнялось на предыдущем шаге
     * используется в методе сохранения
     *
     * @var string
     * @access private
     */
    private $previousAction = false;

    /*
     * @var FileLibrary
     */
    private $fileLibrary;
    /*
     * @var ImageManager
     */
    private $imageManager;

    /**
     * Конструктор класса
     *
     * @return void
     */
    public function __construct($name, $module,   array $params = null) {
        parent::__construct($name, $module,  $params);
        $this->setType(self::COMPONENT_TYPE_LIST);
    }

    /**
     * Добавлены параметры tableName, onlyCurrentLang, editable
     *
     * @return type
     * @access protected
     */

    protected function defineParams() {
        return array_merge(
        parent::defineParams(),
        array(
        'tableName' => false,
        'onlyCurrentLang' => false,
        'editable' => false
        )
        );
    }

    /**
     * Переопределенный метод загрузки описания данных
     * Возвращает информацию о колонках в основной таблице и таблице переводов
     *
     * @return array
     * @access protected
     */
    protected function loadDataDescription() {
        $result = $this->dbh->getColumnsInfo($this->getTableName());
        if ($this->getTranslationTableName()) {
            $transColumnsDescription = $this->dbh->getColumnsInfo($this->getTranslationTableName());
            foreach (array_keys($transColumnsDescription) as $fieldName) {
                //для всех полей кроме идентификатора языка и первичного ключа выставляем дополнительное свойство isMultiLanguage
                if (!in_array($fieldName, array($this->getPK(), 'lang_id'))) {
                    $transColumnsDescription[$fieldName]['isMultilanguage'] = true;
                }
                elseif ($fieldName === 'lang_id' && $this->getPK() !== 'lang_id') {
                    $transColumnsDescription[$fieldName]['languageID'] = true;
                }
            }
            $result += $transColumnsDescription;
            if (isset($result['lang_id'])) {
                $result['lang_id']['key'] = false;
            }
            else {
                throw new SystemException('ERR_DEV_NO_LANG_ID', SystemException::ERR_DEVELOPER);
            }
        }

        return $result;
    }

    /**
      * Переопределенный метод загрузки данных
      *
      * @return array
      * @access protected
      */
    protected function loadData() {
        $data = false;
        if ($this->pager) {
            // pager существует -- загружаем только часть данных, текущую страницу
            $this->setLimit($this->pager->getLimit());
        }
        //Если не существует таблицы с переводами, то выбираем данные из основной таблицы
        if (!$this->getTranslationTableName()) {
            $dbFields = array();
            foreach ($this->getDataDescription() as $fieldName => $field) {
                if (is_null($field->getPropertyValue('customField'))) {
                    if(
                       ($field->getPropertyValue('origType') && ($field->getType() == FieldDescription::FIELD_TYPE_BOOL))
                    ){
                        $fieldName = ' IF(('.$fieldName.' IS NOT NULL) AND ('.$fieldName.' <> ""), 1, 0) AS '.$fieldName;
                    }
                    array_push($dbFields, $fieldName);   
                }
            }
            //Если не пустой массив полей для отбора
            if (!empty($dbFields)) {
                if ($this->getType() == self::COMPONENT_TYPE_FORM_ADD ) {
                    $dbFields = array_flip($dbFields);
                    foreach ($dbFields as $key => $value) {
                        $dbFields[$key] = '';
                    }
                    $res = array($dbFields);
                }
                else {
                    $res = $this->dbh->select($this->getTableName(), (($this->pager)?' SQL_CALC_FOUND_ROWS ':'').implode(',',$dbFields), $this->getFilter(), $this->getOrder(), $this->getLimit());
                }
                if (is_array($res)) {
                    $data = $res;
                    if($this->pager){
                        if(!($recordsCount = simplifyDBResult($this->dbh->selectRequest('SELECT FOUND_ROWS() as c'), 'c', true))){
                            $recordsCount = 0;
                        }
                        $this->pager->setRecordsCount($recordsCount);
                    }
                }
            }
        }
        else {
            //Для мультиязычной таблицы - дергаем отдельный метод загрузки данных
            $data = $this->multiLoadData();
        }
        return $data;
    }
    /**
     * Возвращает язык на которм берутся данные
     *
     * @return int
     * @access protected
     */

    protected function getDataLanguage() {
        $result = false;
        if ($this->getParam('onlyCurrentLang')) {
            $result = $this->document->getLang();
        }
        return $result;
    }

    /**
     * Загрузка мультиязычных данных
     *
     * @return array
     * @access private
     */

    private function multiLoadData() {
        $data = false;
        $lang = E()->getLanguage();
        $lang = $lang->getLanguages();
        $dbFields = array();
        $filter = $order = $limit = '';
        //Создаем перечень полей  в формате array('имя основной таблицы' => array('имя поля'=>'имя таблицы.имя поля'), 'имя таблицыпереводов' => array('имя поля'=>'имя таблицы.имя поля'))
        foreach ($this->getDataDescription() as $fieldName => $field) {
            //Не включаем в набор идентификатор языка
            if (!$field->getPropertyValue('languageID') && $field->getPropertyValue('key') !== true) {
                //не включаем в набор поля полученные  из конфигурации
                if (is_null($field->getPropertyValue('customField'))) {
                	if(
                	   !($field->getPropertyValue('origType') && ($field->getType() == FieldDescription::FIELD_TYPE_BOOL))
                	){
                        $dbFields[$field->getPropertyValue('tableName')][$fieldName] = $field->getPropertyValue('tableName').'.'.$fieldName;
                	}
                	else{
                	   	$dbFields[$field->getPropertyValue('tableName')][$fieldName] = 
                	   	   ' IF(('.$field->getPropertyValue('tableName').'.'.$fieldName.' IS NOT NULL) AND ('.$field->getPropertyValue('tableName').'.'.$fieldName.' <> ""), 1, 0) AS '.$fieldName;
                	}
                }
            }
        }
        
        $filterCondition = $this->getFilter();
        if (!empty($filterCondition)) {
            $filter = $this->dbh->buildWhereCondition($filterCondition).($this->getParam('onlyCurrentLang')?' AND lang_id = '.$this->getDataLanguage():'');
        }
        elseif($this->getDataLanguage() &&  $this->getParam('onlyCurrentLang')) {
            $filter = ' WHERE lang_id = '.$this->getDataLanguage();
        }
        
        if ($this->getOrder()){
        	//inspect($this->getOrder());
            $order = $this->dbh->buildOrderCondition($this->getOrder());
        }
        
        if (!is_null($this->getLimit())) {
            $limit = $this->getLimit();
            $limit = $this->dbh->buildLimitStatement($limit);
        }

        //Если существует листалка указываем ей количество записей

        if ($this->getType() != self::COMPONENT_TYPE_FORM_ADD) {
            $request=sprintf(
            'SELECT '. (($this->pager)?' SQL_CALC_FOUND_ROWS ':'').
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
            (isset($dbFields[$this->getTableName()]))?implode(',', $dbFields[$this->getTableName()]):'',
            isset($dbFields[$this->getTranslationTableName()])?((isset($dbFields[$this->getTableName()]))?',':'').implode(',', $dbFields[$this->getTranslationTableName()]):'',
            $filter,
            $order,
            $limit
            );
            $data = $this->dbh->selectRequest($request);
            if($this->pager){
                if(!($recordsCount = simplifyDBResult($this->dbh->selectRequest('SELECT FOUND_ROWS() as c'), 'c', true))){
                    $recordsCount = 0;
                }
                $this->pager->setRecordsCount($recordsCount);
            }
            //Если данные не только для текущего языка
            if (is_array($data) &&(!$this->getDataLanguage() || $this->getDataLanguage()&&!$this->getParam('onlyCurrentLang') && isset($dbFields[$this->getTranslationTableName()]))) {

                //формируем матрицу
                foreach ($data as $row) {
                    $matrix[$row[$this->getPK()]][$row['lang_id']] = $row;
                }
                //формируем образец
                //в нем все языкозависимые поля заполнены nullами
                foreach (array_keys($dbFields[$this->getTranslationTableName()]) as $fieldName) {
                    $translationColumns[] = 'NULL as '.$fieldName;
                }
                $request = sprintf('
                    SELECT %s, %s %s
                    FROM %s
                    WHERE %s IN(%s)
                ',
                $this->getPK(), (isset($dbFields[$this->getTableName()]))?implode(',', $dbFields[$this->getTableName()]).',':'', implode(',', $translationColumns),
                $this->getTableName(),
                $this->getPK(), implode(',', array_keys($matrix)));
                $res = $this->dbh->selectRequest($request);

                foreach ($res as $row) {
                    $template[$row[$this->getPK()]] = $row;
                }

                $data = array();

                if ($this->getDataLanguage()&&!$this->getParam('onlyCurrentLang')) {
                    $lang = array($this->getDataLanguage() => $lang[$this->getDataLanguage()]);
                }

                foreach ($matrix as $ltagID => $langVersions) {
                    foreach (array_keys($lang) as $langID) {
                        if (isset($langVersions[$langID])) {
                            $data[] = $langVersions[$langID];
                        }
                        else {
                            $data[arrayPush($data, $template[$ltagID])]['lang_id'] = $langID;
                        }
                    }
                }
            }
        }
        else {
            $i=0;
            $dbFields = array_merge(
            (isset($dbFields[$this->getTableName()]))?array_keys($dbFields[$this->getTableName()]):array(),
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
     * Устанавливает имя таблицы
     *
     * @param string
     * @return type
     * @access protected
     */

    protected function setTableName($tableName) {
        $this->setParam('tableName', $tableName);
    }
    /**
     * Для параметра tableName устанавливаем еще и имя таблицы переводов
     *
     * @param string $name
     * @param mixed $value
     * @return void
     * @access protected
     */

    protected function setParam($name, $value) {
        if ($name == 'tableName') {
            $this->translationTableName = $this->dbh->getTranslationTablename($value);
        }

        parent::setParam($name, $value);
    }

    /**
     * Возвращает имя таблицы
     *
     * @return string
     * @access public
     * @final
     */
    final public function getTableName() {
        if (!$this->getParam('tableName')) {
            throw new SystemException('ERR_DEV_NO_TABLENAME', SystemException::ERR_DEVELOPER);
        }

        return $this->getParam('tableName');
    }

    /**
     * Возвращает значение фильтра
     *
     * @return mixed
     * @access protected
     * @final
     */

    final public function getFilter() {
        return $this->filter;
    }

    /**
     * Устанавливает значение фильтра
     *
     * @param mixed
     * @return void
     * @access protected
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
     * Добавляет условие к фильтру
     *
     * @return void
     * @access protected
     */

    protected function addFilterCondition($filter) {
        if (is_numeric($filter)) {
            $filter = array($this->getTableName().'.'.$this->getPK()=>$filter);
        }
        elseif (is_string($filter)) {
        	$filter = array($filter);
        }
        $this->filter = array_merge($this->filter, $filter);
    }

    /**
     * Сброс фильтра
     *
     * @return void
     * @access protected
     * @final
     */

    final protected function clearFilter() {
        $this->filter = array();
    }

    /**
     * Возвращает условия сортровки
     *
     * @return array
     * @access protected
     * @final
     */

    final protected function getOrder() {
        if(is_null($this->order)){
            $this->order = false;
            $columns = $this->dbh->getColumnsInfo($this->getTableName());
            foreach(array_keys($columns) as $columnName){
                if(strpos($columnName, '_order_num')){
                    $this->setOrder(array($columnName => QAL::ASC));
                    break;
                }
            }
        }

        return $this->order;
    }

    /**
     * Устанавливает условие сортровки
     *
     * @param array Параметры сортировки array($orderFieldName => $orderDirection)
     * @return void
     * @access protected
     * @final
     */

    final protected function setOrder(array $order) {
        /*$orderDirection = strtoupper($orderDirection);
        if (!in_array($orderDirection, array(QAL::ASC, QAL::DESC))) {
            $orderDirection = QAL::ASC;
        }
        */
        $this->order = $order;
    }

    /**
     * Возвращает ограничения по количеству записей
     *
     * @return array
     * @access protected
     * @final
     */

    final protected function getLimit() {
        return $this->limit;
    }

    /**
     * Устанавливает ограничения по количеству записей
     *
     * @param array
     * @return void
     * @access protected
     * @final
     */

    final protected function setLimit(array $limit) {
        $this->limit = $limit;
    }



    /**
     * Возвращает имя поля - первичного ключа
     *
     * @return string
     * @access protected
     * @final
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
            }
            else {
                throw new SystemException('ERR_DEV_NO_PK', SystemException::ERR_DEVELOPER);
            }

        }

        return $this->pk;
    }

    final protected function setPK($primaryColumnName){
    	$this->pk = $primaryColumnName;
    }

    /**
     * Для мультиязычного грида
     * подменяем построитель
     *
     * @return AbstractBuilder
     * @access protected
     */

    protected function createBuilder() {
        if (!$this->getTranslationTableName()) {
            $result = parent::createBuilder();
        }
        else {
            $result = new MultiLanguageBuilder();
        }
        return $result;
    }

    /**
     * добавлена обработка ключей
     *
     * @return DataDescription
     * @access protected
     */

    protected function createDataDescription() {
        $result = parent::createDataDescription();
        foreach ($result as $fieldName => $fieldMetaData) {
            $keyInfo = $fieldMetaData->getPropertyValue('key');
            //Если это внешний ключ и не в режиме списка
            if (is_array($keyInfo) && in_array($fieldMetaData->getType(), array(FieldDescription::FIELD_TYPE_SELECT, FieldDescription::FIELD_TYPE_MULTI))) {
                $fkTableName = $keyInfo['tableName'];
                $fkKeyName = $keyInfo['fieldName'];
                //загружаем информацию о возможных значениях
                $values = $this->getFKData($fkTableName, $fkKeyName);
                if(!empty($values))
                    call_user_func_array(array($fieldMetaData, 'loadAvailableValues'), $values);

            }
        }
        return $result;
    }

    /**
     * Возвращает данные о значения в связанной таблицы
     *
     * @return array
     * @access protected
     */

    protected function getFKData($fkTableName, $fkKeyName) {
        return $this->dbh->getForeignKeyData($fkTableName, $fkKeyName, $this->document->getLang());
    }

    /**
     * Возвращает имя таблицы переводов
     *
     * @return string
     * @access protected
     * @final
     */

    final protected function getTranslationTableName() {
        return $this->translationTableName;
    }

    /**
     * Метод выводит форму просмотра
     *
     * @return void
     * @access protected
     */

    protected function view() {
        $this->setType(self::COMPONENT_TYPE_FORM);
        //$this->addCrumb('TXT_VIEW_ITEM');
        $id = $this->getStateParams();
        list($id) = $id;
        if (!$this->recordExists($id)) {
            throw new SystemException('ERR_404', SystemException::ERR_404);
        }
        $this->addFilterCondition(array($this->getTableName().'.'.$this->getPK() => $id));

        $this->prepare();
        foreach ($this->getDataDescription() as $fieldDescription) {
            $fieldDescription->setMode(FieldDescription::FIELD_MODE_READ);
        }
    }
    
    /**
     * Определяет существует ли запись с идентификатором переданным в параметре
     * Вызывается из методов где нужно быть уверенным в наличии записи(view, edit,delete)
     *
     * @param string
     * @param mixed
     * @return void
     * @access protected
     * @final
     */

    final protected function recordExists($id, $fieldName = false) {
        if (!$fieldName) {
            $fieldName = $this->getPK();
        }

        $res = $this->dbh->select($this->getTableName(), array($fieldName), array($fieldName=>$id));
        return is_array($res);
    }
    /**
     * При редактировании выводим Js объек
     *
     * @return mixed
     * @access protected
     */
    protected function buildJS(){
        $result = false;
        if(($this->getState() == 'view') && $this->document->isEditable() && $this->getParam('editable')) {
            $this->addWYSIWYGTranslations();
            $this->setProperty('editable', 'editable');
        }
        $result = parent::buildJS();
        
        return $result;
    }    

    /**
     * Возвращает предыдущее действие
     *
     * @return string
     * @access protected
     * @final
     */

    final protected function getPreviousAction() {
        if (!$this->previousAction) {
            if (!isset($_POST['componentAction'])) {
                throw new SystemException('ERR_NO_COMPONENT_ACTION', SystemException::ERR_CRITICAL);
            }
            else {
                $this->previousAction = $_POST['componentAction'];
            }
        }

        return $this->previousAction;
    }
    /**
      * Сохранение текста статьи
      * 
      * @return void
      * @access protected
      */
    protected function saveText(){
        $result = '';
        if($this->getParam('editable') && isset($_POST['ID']) && isset($_POST['num']) && isset($_POST['data'])){
            $result = DataSet::cleanupHTML($_POST['data']);
            $langID = $this->document->getLang();
            $entityId = (int)$_POST['ID'];
            $field = $_POST['num'];
            $this->dbh->modify(QAL::UPDATE, $this->getTranslationTableName(), array($field => $result), array('lang_id' => $langID, $this->getPK() => $entityId));
            
        }
        
        $this->response->setHeader('Content-Type', 'application/xml; charset=utf-8');
        $this->response->write($result);
        $this->response->commit();      
    }
    /**
     * Выводит компонент: библиотека изображений
     *
     * @return void
     * @access protected
     */
    protected function fileLibrary() {
        $this->request->setPathOffset($this->request->getPathOffset() + 1);
        $this->fileLibrary = $this->document->componentManager->createComponent('filelibrary', 'share', 'FileLibrary', array('config' => 'core/modules/share/config/FileLibraryMin.component.xml'));
        //$this->fileLibrary->getState();
        $this->fileLibrary->run();
    }
    /**
     * Выводит компонент: менеджер изображений
     *
     * @return void
     * @access protected
     */
    protected function imageManager() {
        $this->imageManager  = $this->document->componentManager->createComponent('imagemanager', 'share', 'ImageManager', null);
        //$this->imageManager->getState();
        $this->imageManager->run();
    }

    public function build() {
        switch ($this->getState()) {
            case 'imageManager':
                return $this->imageManager->build();
                break;
            case 'fileLibrary':
                return $this->fileLibrary->build();
                break;
            default:
                // do nothing
        }

        $result = parent::build();
        return $result;
    }
}

