<?php

/**
 * Содержит класс Grid
 *
 * @package energine
 * @subpackage share
 * @author dr.Pavka
 * @copyright Energine 2006
 * @version $Id$
 */

/**
 * Сетка
 *
 * @package energine
 * @subpackage share
 * @author dr.Pavka
 */
class Grid extends DBDataSet {
    /**
     * Направление вверх
     *
     */
    const DIR_UP = '<';
    /**
     * Направление вниз
     *
     */
    const DIR_DOWN = '>';
    /**
     * Количество записей в гриде по умолчанию
     *
     */
    const RECORD_PER_PAGE = 50;

    /**
     * Компонент: менеджер изображений
     *
     * @var ImageManager
     * @access private
     */
    private $imageManager;

    /**
     * Компонент: библиотека изображений
     *
     * @var FileLibrary
     * @access protected
     */
    protected $fileLibrary;

    /**
     * сейвер
     *
     * @var Saver
     * @access protected
     */
    protected $saver;

    /**
     * Имя колонки для определения порядка пользовательскорй сортировки
     *
     * @var string
     * @access private
     */
    private $orderColumn = null;


    /**
     * Конструктор класса
     *
     * @param string $name
     * @param string $module
     * @param Document $document
     * @param array $params
     * @access public
     */
    public function __construct($name, $module, Document $document,  array $params = null) {
        parent::__construct($name, $module, $document,  $params);
        
        $this->setProperty('exttype', 'grid');
        if (!$this->getParam('recordsPerPage')) {
            $this->setParam('recordsPerPage', self::RECORD_PER_PAGE);
        }
        $this->setTitle($this->translate('TXT_'.strtoupper($this->getName())));
    }
    /**
	 * Переопределен параметр configFilename
	 *
	 * @return array
	 * @access protected
	 */

    protected function defineParams() {
        $params = array();
        if (!$this->params['configFilename']) {
        	$fileName = get_class($this).'.component.xml';
            $fileConf = sprintf(ComponentConfig::SITE_CONFIG_DIR, SiteManager::getInstance()->getCurrentSite()->folder).$fileName;
            $coreConf = sprintf(ComponentConfig::CORE_CONFIG_DIR, $this->module).$fileName;
            if (file_exists($fileConf)) {
                $params['configFilename'] = $fileConf;
            }
            elseif (file_exists($coreConf)) {
                $params['configFilename'] = $coreConf;
            }
            else {
                $params['configFilename'] = sprintf(ComponentConfig::CORE_CONFIG_DIR, 'share/').'Grid.component.xml';
            }
        }
        $params['active'] = true;
        $params['thumbnail'] = array($this->getConfigValue('thumbnail.width'), $this->getConfigValue('thumbnail.height'));
        
        return array_merge(parent::defineParams(),$params);
    }
    /**
     * Метод выводящий форму добавления
     *
     * @return void
     * @access protected
     */

    protected function add() {
        $this->setType(self::COMPONENT_TYPE_FORM_ADD);
        $this->prepare();
        $this->addToolbarTranslations();
        //$this->addCrumb('TXT_ADD_ITEM');
    }

    /**
     * Метод выводящий форму редактирования
     *
     * @return void
     * @access protected
     */

    protected function edit() {
        $this->setType(self::COMPONENT_TYPE_FORM_ALTER);

        $id = $this->getActionParams();
        list($id) = $id;
        if (!$this->recordExists($id)) {
            throw new SystemException('ERR_404', SystemException::ERR_404);
        }
        $this->setFilter($id);

        $this->prepare();
        
        foreach ($this->getDataDescription() as $fieldName => $fieldDescription) {
            //@todo Тут можно упростить
            if (($fieldDescription->getType() == FieldDescription::FIELD_TYPE_PFILE) && ($fieldData = $this->getData()->getFieldByName($fieldName)->getData())) {
                $fieldData = $fieldData[0];
                if (file_exists($fieldData) && @getimagesize($fieldData)) {
                    $this->getDataDescription()->getFieldDescriptionByName($fieldName)->setProperty('is_image', 'is_image');
                }
            }
        }
		$this->addToolbarTranslations();
    }

    /**
     * Добавлены переводы для фильтра
     *
     * @return void
     * @access protected
     */

    protected function main() {
        parent::main();
        $this->addTranslation('TXT_FILTER', 'BTN_APPLY_FILTER', 'TXT_RESET_FILTER');
    }

    /**
     * Внешний метод удаления
     *
     * @return mixed
     * @access protected
     * @see Grid::save()
     */

    protected function delete() {
        $transactionStarted = $this->dbh->beginTransaction();
        try {
            list($id) = $this->getActionParams();
            if (!$this->recordExists($id)) {
                throw new SystemException('ERR_404', SystemException::ERR_404);
            }

            $this->deleteData($id);

            $JSONResponse = array(
            'result'=>true
            );
            $this->dbh->commit();
        }
        catch (SystemException $e){
            if ($transactionStarted) {
                $this->dbh->rollback();
            }
            $JSONResponse = $this->generateError($e->getCode(), $e->getMessage());

        }
        $this->response->setHeader('Content-Type', 'text/javascript; charset=utf-8');
        $this->response->write(json_encode($JSONResponse));
        $this->response->commit();
    }

    /**
     * Внутренний метод удаления записи
     *
     * @param int идентификаотр записи
     * @return void
     * @access protected
     */

    protected function deleteData($id) {
        if ($orderColumn = $this->getOrderColumn()) {
            $deletedOrderNum = simplifyDBResult($this->dbh->select($this->getTableName(), $this->getOrderColumn(), array($this->getPK()=>$id)), $this->getOrderColumn(), true);

            $ids = simplifyDBResult($this->dbh->select($this->getTableName(), array($this->getPK()), array_merge($this->getFilter(), array($orderColumn.' > '.$deletedOrderNum)), array($orderColumn=>QAL::ASC)), $this->getPK());

        }
        $this->dbh->modify(QAL::DELETE , $this->getTableName(), null, array($this->getPK()=>$id));

        //если определен порядок следования перестраиваем индекс сортировки
        if ($orderColumn && $ids) {
            $this->addFilterCondition(array($this->getPK()=>$ids));
            $request = 'UPDATE '.$this->getTableName().' SET '.$orderColumn.' = '.$orderColumn.' - 1 '.$this->dbh->buildWhereCondition($this->getFilter());

            $this->dbh->modifyRequest($request);
        }
    }

    /**
     * переписан родительский метод
     *
     * @return int
     * @access protected
     */

    protected function getDataLanguage() {
        if (isset($_POST['languageID']) && $this->getAction()=='getRawData') {
            $langID = $_POST['languageID'];
            if (!Language::getInstance()->isValidLangID($langID)) {
                throw new SystemException('ERR_BAD_LANG_ID', SystemException::ERR_WARNING);
            }
            $result = $langID;
        }
        else $result = parent::getDataLanguage();

        return $result;
    }

    /**
     * Выводит данные в JSON формате для AJAX
     *
     * @return void
     * @access protected
     */

    protected function getRawData($baseMethod = self::DEFAULT_ACTION_NAME) {
        try {
            $this->setParam('onlyCurrentLang', true);
            $this->config->setCurrentMethod($baseMethod);
            $this->setBuilder(new JSONBuilder());

            $this->setDataDescription($this->createDataDescription());
            $this->createPager();
            $this->getBuilder()->setDataDescription($this->getDataDescription());
            $data = $this->createData();
            if ($data instanceof Data) {
                $this->setData($data);
                $this->getBuilder()->setData($this->getData());
            }

            if ($this->getBuilder()->build()) {
                if ($this->pager) $this->getBuilder()->setPager($this->pager);
                $result = $this->getBuilder()->getResult();
            }
            else {
                $result = $this->getBuilder()->getErrors();
            }

        }
        catch (Exception $e){
            $message['errors'][] = array('message'=>$e->getMessage().current($e->getCustomMessage()));
            $result = json_encode(array_merge(array('result'=>false, 'header'=>$this->translate('TXT_SHIT_HAPPENS')), $message));
        }
        $this->response->setHeader('Content-Type', 'text/javascript; charset=utf-8');
        $this->response->write($result);
        $this->response->commit();
    }

    /**
     * Внешний метод сохранения
     * Вызывает внутренний метод сохранения saveData(), который и производит собственно все действия
     *
     * @return void
     * @access protected
     */

    protected function save() {
        $transactionStarted = $this->dbh->beginTransaction();
        try {
            $result = $this->saveData();

            $transactionStarted = !($this->dbh->commit());
            $JSONResponse = array(
            'data'=>json_encode((is_int($result))?$result:(int)$_POST[$this->getTableName()][$this->getPK()]),
            'result' => true,
            'mode' => (is_int($result))?'insert':'update'
            );
        }
        catch (FormException $formError) {
            $this->dbh->rollback();
            //Формируем JS массив ошибок который будет разбираться на клиенте
            $errors = $this->saver->getErrors();
            foreach ($errors as $errorFieldName) {
                $message['errors'][] = array(
                'field'=>$this->translate('FIELD_'.strtoupper($errorFieldName)),
                'message'=>$this->translate($this->saver->getDataDescription()->getFieldDescriptionByName($errorFieldName)->getPropertyValue('message'))
                );
            }
            $JSONResponse = array_merge(array('result'=>false, 'header'=>$this->translate('TXT_SHIT_HAPPENS')), $message);
        }
        catch (SystemException $e){
            if ($transactionStarted) {
                $this->dbh->rollback();
            }
            $message['errors'][] = array('message'=>$e->getMessage().current($e->getCustomMessage()));
            $JSONResponse = array_merge(array('result'=>false, 'header'=>$this->translate('TXT_SHIT_HAPPENS')), $message);

        }

        $this->response->setHeader('Content-Type', 'text/javascript; charset=utf-8');
        $this->response->write(json_encode($JSONResponse));
        $this->response->commit();
    }

    /**
     * Если поле OrderColumn присутствует в списке, убираем его
     *
     * @return DataDescription
     * @access protected
     */

    protected function createDataDescription() {
        if (in_array($this->getAction(), array('printData', 'exportCSV'))) {
            $previousAction = $this->getAction();
            $this->config->setCurrentMethod(self::DEFAULT_ACTION_NAME);
            $result = parent::createDataDescription();
            $this->config->setCurrentMethod($previousAction);
        }
        else {
            $result = parent::createDataDescription();
            if (in_array($this->getAction(), array('main', 'edit', 'add', 'save', 'getRawData')) && ($col = $this->getOrderColumn()) && ($field = $result->getFieldDescriptionByName($col))) {
                $result->removeFieldDescription($field);
            }
        }
        return $result;
    }

    /**
     * Переписан родительский метод генерации ошибки, поскольку для AJAX такая не подходит
     *
     * @param string тип ошибки
     * @param string сообщение об ошибке
     * @param mixed  необязательная дополнительная информация об ошибке
     *
     * @return void
     * @access protected
     */
    protected function generateError($errorType, $errorMessage, $errorCustomInfo = false) {
        $message['errors'][] = array('message'=>$errorMessage);
        $response = array_merge(array('result'=>false, 'header'=>$this->translate('TXT_SHIT_HAPPENS')), $message);
        return $response;
    }
    /**
     * Возвращает объект Saver
     * Есть смысл вызывать эту функцию только внутри save/saveSata
     * во всех остальных случаях она возвращает false
     * 
     * @return Saver
     * @access protected
     * @final
     */
    final protected function getSaver(){
    	if(is_null($this->saver)){
			$this->saver = new Saver();
    	}

    	return $this->saver;
    }

    final protected function setSaver(Saver $saver){
    	$this->saver = $saver;
    }

    /**
     * Внутренний метод сохранения
     *
     * @return mixed
     * @access protected
     */

    protected function saveData () {
        $result = false;
        //если в POST не пустое значение значение первичного ключа - значит мы находимся в режиме редактирования
        if (isset($_POST[$this->getTableName()][$this->getPK()]) && !empty($_POST[$this->getTableName()][$this->getPK()])) {
            $mode = self::COMPONENT_TYPE_FORM_ALTER;
            $this->setFilter(array($this->getPK()=>$_POST[$this->getTableName()][$this->getPK()]));
        }
        else {
            $mode = self::COMPONENT_TYPE_FORM_ADD;
        }

        //создаем объект описания данных
        $dataDescriptionObject = new DataDescription();

        if (!method_exists($this, $this->getPreviousAction())) {
            throw new SystemException('ERR_NO_ACTION', SystemException::ERR_CRITICAL);
        }

        //получаем описание полей для метода
        $configDataDescription = $this->config->getMethodConfig($this->getPreviousAction());
        //если в конфиге есть описание полей для метода - загружаем их
        if (isset($configDataDescription->fields)) {
            $dataDescriptionObject->loadXML($configDataDescription->fields);
        }

        //Создаем объект описания данных взятых из БД
        $DBDataDescription = new DataDescription();
        //Загружаем в него инфу о колонках
        $DBDataDescription->load($this->loadDataDescription());
        $this->setDataDescription($dataDescriptionObject->intersect($DBDataDescription));
        //Поле с порядком следования убираем из списка
        /**
         * @todo  Надо бы это как то переделать, потому что разбросано получилось
         * часть кода относящаяся к обработке колонки с нумерацией здесь, часть
         * @see Grid::createDataDescription
         */
        if (($col = $this->getOrderColumn()) && ($field = $this->getDataDescription()->getFieldDescriptionByName($col))) {
            $this->getDataDescription()->removeFieldDescription($field);
        }

        $dataObject = new Data();
        $dataObject->load($this->loadData());
        $this->setData($dataObject);

        //Создаем сейвер
        $saver = $this->getSaver();

        //Устанавливаем его режим
        $saver->setMode($mode);
        $saver->setDataDescription($this->getDataDescription());
        $saver->setData($this->getData());

        if($saver->validate() === true) {
            $saver->setFilter($this->getFilter());
            $saver->save();
            $result = $saver->getResult();

        }
        else {
            //выдвигается пустой exception который перехватывается в методе save
            throw new FormException();
        }

        //Если у нас режим вставки и определена колонка для порядка следования, изменяем порядок следования
        if (($orderColumn = $this->getOrderColumn()) && ($mode == self::COMPONENT_TYPE_FORM_ADD)) {
            $this->addFilterCondition(array($this->getPK().'!='.$result));
            $request = 'UPDATE '.$this->getTableName().' SET '.$orderColumn.'='.$orderColumn.'+1 '.$this->dbh->buildWhereCondition($this->getFilter());
            $this->dbh->modifyRequest($request);
        }

        return $result;
    }


    /**
     * Переопределенный метод построения
     * Перед построением - добавляется перевод
     * После построения добавляется информация о закладках
     *
     * @return void
     * @access public
     */

    public function build() {
        switch ($this->getAction()) {
            case 'imageManager':
                return $this->imageManager->build();
                break;
            case 'fileLibrary':
                return $this->fileLibrary->build();
                break;
            default:
                // do nothing
        }

        if ($this->getType() == self::COMPONENT_TYPE_LIST) {
            $this->addTranslation('MSG_CONFIRM_DELETE');
        }

        $result = parent::build();
        return $result;
    }
    /**
     * Для действия main не выводим данные
     * Для действия save определяем другой формат данных
     *
     * @return mixed
     * @access protected
     */

    protected function loadData() {
        if ($this->getAction() == self::DEFAULT_ACTION_NAME) {
            $result = false;
        }
        elseif ($this->getAction() == 'save') {
            if (!isset($_POST[$this->getTableName()])) {
                throw new SystemException('ERR_NO_DATA', SystemException::ERR_CRITICAL);
            }

            $data = $_POST[$this->getTableName()];
            //Приводим данные к стандартному виду
            $result = array($data);
            if ($this->getTranslationTableName()) {
                if (!isset($_POST[$this->getTranslationTableName()])) {
                    throw new SystemException('ERR_NO_DATA', SystemException::ERR_CRITICAL);
                }
                $result = array();
                $multidata = $_POST[$this->getTranslationTableName()];
                foreach ($multidata as $langID => $langValues) {
                    $idx = arrayPush($result, $data);
                    $result[$idx]['lang_id'] = $langID;
                    foreach ($langValues as $fieldName => $fieldValue) {
                        $result[$idx][$fieldName] = $fieldValue;
                    }
                }
            }

        }
        elseif ($this->getAction() =='getRawData') {
            $this->applyUserFilter();
            $actionParams = $this->getActionParams(true);
            
            if(isset($actionParams['sortField']) && isset($actionParams['sortDir'])){
                //подразумевается что sortDir - тоже существует
                $this->setOrder(array($actionParams['sortField'] => $actionParams['sortDir']));
            }
            $result = parent::loadData();
        }
        else {
            $result = parent::loadData();
        }

        return $result;
    }

    /**
     * Выводит компонент: менеджер изображений
     *
     * @return void
     * @access protected
     */
    protected function imageManager() {
        $this->imageManager  = $this->document->componentManager->createComponent('imagemanager', 'share', 'ImageManager', null);
        //$this->imageManager->getAction();
        $this->imageManager->run();
    }

    /**
     * Выводит компонент: библиотека изображений
     *
     * @return void
     * @access protected
     */
    protected function fileLibrary() {
        $this->request->setPathOffset($this->request->getPathOffset() + 1);
        $this->fileLibrary = $this->document->componentManager->createComponent('filelibrary', 'share', 'FileLibrary', array('configFilename' => 'core/modules/share/config/FileLibraryMin.component.xml'));
        //$this->fileLibrary->getAction();
        $this->fileLibrary->run();
    }

    /**
     * Метод генерящий thumbnail и сохраняющий его в БД
     *
     * @param $sourceFileName string имя исходного файла
     * @param $destFieldName string имя поля
     * @param $width int ширина
     * @param $height int высота
     * @param $filter array фильтр
     * @param $rewrite boolean переписывать ли если уже существует
     * @return имя файла - превьюхи
     * @access protected
     */

    protected function generateThumbnail($sourceFileName, $destFieldName, $width, $height, $filter, $rewrite = true) {
        $destFileName = false;
        if (!empty($sourceFileName)) {
        	list($dirname, $basename, $extension, $filename) = array_values(pathinfo($sourceFileName));
            $destFileName = $dirname.'/'.'.'.$filename.'.'.$width.'-'.$height.'.'.$extension;
            if (
            (
            file_exists($fullDestFileName = dirname($_SERVER['SCRIPT_FILENAME']).'/'.$destFileName)
            && $rewrite
            )
            || !file_exists($fullDestFileName)
            ) {
                $image = new Image();
                $image->loadFromFile($sourceFileName);
                $image->resize($width,$height);
                $image->saveToFile($destFileName);
                
                //Сохраняем в БД
                $this->dbh->modify(QAL::UPDATE, $this->getTableName(), array($destFieldName=>$destFileName), $filter);
            }
        }

        return $destFileName;
    }

    /**
     * Устанавливает имя колонки для пользовательской сортировки
     *
     * @return void
     * @access protected
     */

    protected function setOrderColumn($columnName) {
        $this->orderColumn = $columnName;
        $this->setOrder(array($columnName => QAL::ASC));
    }

    /**
      * Возвращает имя колонки для пользовательской сортировки
      *
      * @return string
      * @access protected
      */

    protected function getOrderColumn() {
        if(is_null($this->orderColumn)) {
            $this->orderColumn = false;
            $columns = $this->dbh->getColumnsInfo($this->getTableName());
            foreach(array_keys($columns) as $columnName){
                if(strpos($columnName, '_order_num')){
                    $this->setOrderColumn($columnName);
                    break;
                }
            }
        }
        return $this->orderColumn;
    }

    /**
	 * Метод для изменения порядка следования  - вверх
	 *
	 * @return void
	 * @access protected
	 */

    protected function up() {
        $this->response->setHeader('Content-Type', 'text/javascript; charset=utf-8');
        $this->response->write($this->changeOrder(Grid::DIR_UP));
        $this->response->commit();
    }

    /**
	 * Метод для изменения порядка следования  - вниз
	 *
	 * @return void
	 * @access protected
	 */

    protected function down() {
        $this->response->setHeader('Content-Type', 'text/javascript; charset=utf-8');
        $this->response->write($this->changeOrder(Grid::DIR_DOWN));
        $this->response->commit();
    }

    /**
     * Изменяет порядок следования
     *
     * @param string  - направление
     * @return JSON String
     * @access protected
     */

    protected function changeOrder($direction) {

        try {

            if (!$this->getOrderColumn()) {
                //Если не задана колонка для пользовательской сортировки то на выход
                throw new SystemException('ERR_NO_ORDER_COLUMN', SystemException::ERR_DEVELOPER);
            }

            $currentID = $this->getActionParams();
            list($currentID) = $currentID;

            //Определяем order_num текущей страницы
            $currentOrderNum = simplifyDBResult(
            	$this->dbh->selectRequest(
            		'SELECT '.$this->getOrderColumn().' '.
            		'FROM '.$this->getTableName().' '.
            		'WHERE '.$this->getPK().' = %s',
            		$currentID
            	),
            	$this->getOrderColumn(),
            	true
            );

            $orderDirection = ($direction == Grid::DIR_DOWN)?QAL::ASC:QAL::DESC;

			$baseFilter = $this->getFilter();

            if(!empty($baseFilter)){
				$baseFilter = ' AND '.str_replace('WHERE', '', $this->dbh->buildWhereCondition($this->getFilter()));
			}
			else{
				$baseFilter = '';
			}

            //Определяем идентификатор записи которая находится рядом с текущей
            $request =
            	'SELECT '.$this->getPK().' as neighborID, '.$this->getOrderColumn().' as neighborOrderNum '.
            	'FROM '.$this->getTableName().' '.
            	'WHERE '.$this->getOrderColumn().' '.$direction.' '.$currentOrderNum.' '.$baseFilter.
            	'ORDER BY '.$this->getOrderColumn().' '.$orderDirection.' Limit 1';

            $data = convertDBResult($this->dbh->selectRequest($request), 'neighborID');
            if ($data) {
                extract(current($data));
                $this->dbh->beginTransaction();
                $this->dbh->modify(
                	QAL::UPDATE,
                	$this->getTableName(),
                	array($this->getOrderColumn()=>$neighborOrderNum),
                	array($this->getPK()=>$currentID)
                );
                $this->dbh->modify(
                	QAL::UPDATE,
                	$this->getTableName(),
                	array($this->getOrderColumn()=>$currentOrderNum),
                	array($this->getPK()=>$neighborID)
                );
                $this->dbh->commit();
            }

            $JSONResponse = array(
            'result' => true,
            'dir' => $direction
            );
        }
        catch (SystemException $e){
            $JSONResponse = $this->generateError($e->getCode(), $e->getMessage());

        }
        return json_encode($JSONResponse);
    }

    /**
     * Метод применеющий фильтр
     *
     * @return void
     * @access protected
     */

    protected function applyUserFilter() {
        //Формат фильтра
        //$_POST['filter'][$tableName][$fieldName] = значение фильтра
        if (isset($_POST['filter'])) {
            $tableName = key($_POST['filter']);
            $fieldName = key($_POST['filter'][$tableName]);
            $value = trim($_POST['filter'][$tableName][$fieldName]);
            //получили текущий фильтр
            /*$currentFilter = $this->getFilter();
            if ($currentFilter) {
                $currentFilter =str_replace('WHERE', '',$this->dbh->buildWhereCondition($currentFilter)).' AND ';
            }
            else {
                $currentFilter = '';
            }
            //к текущему фильтру присоединяем пользовательский
            $this->setFilter($currentFilter.$tableName.'.'.$fieldName.' LIKE \'%'.$value.'%\' ');
             */
            $this->addFilterCondition($tableName.'.'.$fieldName.' LIKE \'%'.$value.'%\' ');
        }
    }

    /**
     * Добавляет переводы для WYSIWYG при необходимости 
     * 
     * @access private
     * @return void
     */
    private function addToolbarTranslations(){
        foreach($this->getDataDescription() as $fd){
            if(($fd->getType() == FieldDescription::FIELD_TYPE_HTML_BLOCK)){
                $this->addWYSIWYGTranslations();       
                break;
            }
        }
    }
    
    /**
     * Строит список дополнительных файлов
     * Используется в тех случаях когда необходимо создать дополнительную вкладку с приаттачеными к записи файлами
     * 
     * Сохранение приаттаченных данных должно происходить в методе saveData на общих основаниях 
     * @see DivisionEditor
     * @see ProductEditor
     *  
     * @access protected
     * @return void
     */
    protected function addAttFilesField($tableName, $data = true){
    	    $field = new FieldDescription('attached_files');
            $field->setType(FieldDescription::FIELD_TYPE_CUSTOM);
            $field->setProperty('tabName', $this->translate('TAB_ATTACHED_FILES'));
            $field->setProperty('tableName', $tableName);
            $this->getDataDescription()->addFieldDescription($field);
            
    	   //Добавляем поле с дополнительными файлами
            $field = new Field('attached_files');

            //Ссылки на добавление и удаление файла
            $this->addTranslation('BTN_ADD_FILE', 'BTN_LOAD_FILE', 'BTN_DEL_FILE', 'BTN_UP', 'BTN_DOWN');
            
            $attachedFilesData = $this->buildAttachedFiles($data); 
            for ($i = 0; $i < count(Language::getInstance()->getLanguages()); $i++) {
                $field->addRowData($attachedFilesData);
            }
            $this->getData()->addField($field);
    }
    /**
     * @param $data Данные 
     * 
     * @access private
     * @return DOMNode
     */
    
    private function buildAttachedFiles($data){
        $builder = new Builder();
        $dd = new DataDescription();
        $f = new FieldDescription('upl_id');
        $dd->addFieldDescription($f);
        /*
        $f = new FieldDescription('upl_is_main');
        $f->setType(FieldDescription::FIELD_TYPE_BOOL);
        $dd->addFieldDescription($f);
*/
        $f = new FieldDescription('upl_name');
        $dd->addFieldDescription($f);

        $f = new FieldDescription('upl_path');
        $f->setType(FieldDescription::FIELD_TYPE_STRING);
        $f->setProperty('title', $this->translate('FIELD_UPL_FILE'));
        $dd->addFieldDescription($f);

        $d = new Data();
            
        if(is_array($data)){
            $d->load($data);
            $pathField = $d->getFieldByName('upl_path');
            foreach ($pathField as $i => $path) {
            	if(in_array(FileInfo::getInstance()->analyze($path)->type, array(FileInfo::META_TYPE_IMAGE, FileInfo::META_TYPE_VIDEO))){
                    $pathField->setRowData($i, FileObject::getThumbFilename($path, 50, 50));
                    $pathField->setRowProperty($i, 'is_image', true);            		
            	}
            }
        }

        $this->addTranslation('MSG_NO_ATTACHED_FILES');

        $builder->setData($d);
        $builder->setDataDescription($dd);

        $builder->build();

        return $builder->getResult();	
    }
    /**
      * Быстрая загрузка аттачмента в репозиторий 
      * 
      * @return void
      * @access protected
      */
    protected function put(){
        $this->request->setPathOffset($this->request->getPathOffset() + 1);
        $this->fileLibrary = $this->document->componentManager->createComponent(
            'filelibrary', 
            'share', 
            'FileLibrary', 
            array('action' => 'put', 'active' => false)
        );
        $this->fileLibrary->run();  
    }
}