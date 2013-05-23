<?php

/**
 * Содержит класс Grid
 *
 * @package energine
 * @subpackage share
 * @author dr.Pavka
 * @copyright Energine 2006
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
     * @param array $params
     * @access public
     */
    public function __construct($name, $module, array $params = null) {
        parent::__construct($name, $module, $params);

        $this->setProperty('exttype', 'grid');
        if (!$this->getParam('recordsPerPage')) {
            $this->setParam('recordsPerPage', self::RECORD_PER_PAGE);
        }

        if (!$this->getTitle())
            $this->setTitle($this->translate(
                'TXT_' . strtoupper($this->getName())));

        if ($this->getParam('order')) {
            if (in_array($this->getParam('order'), array_keys($this->dbh->getColumnsInfo($this->getTableName())))) {
                $this->orderColumn = $this->getParam('order');
            }
        }

    }

    /**
     * Переопределен параметр config
     *
     * @return array
     * @access protected
     */

    protected function defineParams() {
        $params = array();
        if (!$this->params['config']) {
            $fileName = get_class($this) . '.component.xml';
            $fileConf =
                sprintf(SITE_DIR . ComponentConfig::SITE_CONFIG_DIR, E()->getSiteManager()->getCurrentSite()->folder) .
                $fileName;
            $coreConf =
                sprintf(CORE_DIR . ComponentConfig::CORE_CONFIG_DIR, $this->module) .
                $fileName;
            if (file_exists($fileConf)) {
                $params['config'] = $fileConf;
            } elseif (file_exists($coreConf)) {
                $params['config'] = $coreConf;
            } else {
                $params['config'] =
                    sprintf(CORE_DIR . ComponentConfig::CORE_CONFIG_DIR, 'share/') .
                    'Grid.component.xml';
            }
        }
        $params['active'] = true;
        $params['thumbnail'] =
            array($this->getConfigValue('thumbnail.width'), $this->getConfigValue('thumbnail.height'));
        $params['order'] = false;
        return array_merge(parent::defineParams(), $params);
    }

    /**
     * @return GridConfig
     */
    protected function getConfig() {
        if (!$this->config) {
            $this->config = new GridConfig(
                $this->getParam('config'),
                get_class($this),
                $this->module
            );
        }
        return $this->config;
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
        $this->addAttFilesField($this->getTableName());
    }

    /**
     * Метод выводящий форму редактирования
     *
     * @return void
     * @access protected
     */

    protected function edit() {
        $this->setType(self::COMPONENT_TYPE_FORM_ALTER);

        $id = $this->getStateParams();
        list($id) = $id;
        if (!$this->recordExists($id)) {
            throw new SystemException('ERR_404', SystemException::ERR_404);
        }
        $this->setFilter($id);
        $this->prepare();
        $this->addToolbarTranslations();
        $this->addAttFilesField($this->getTableName());
    }

    /**
     * Добавлены переводы для фильтра
     *
     * @return void
     * @access protected
     */

    protected function main() {
        parent::main();
        $this->addTranslation('TXT_FILTER', 'BTN_APPLY_FILTER', 'TXT_RESET_FILTER', 'TXT_FILTER_SIGN_BETWEEN', 'TXT_FILTER_SIGN_CONTAINS', 'TXT_FILTER_SIGN_NOT_CONTAINS');
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
            list($id) = $this->getStateParams();
            if (!$this->recordExists($id)) {
                throw new SystemException('ERR_404', SystemException::ERR_404);
            }

            $this->deleteData($id);

            $b = new JSONCustomBuilder();
            $b->setProperty('result', true)->setProperty('mode', 'delete');
            $this->setBuilder($b);

            $this->dbh->commit();
        } catch (SystemException $e) {
            if ($transactionStarted) {
                $this->dbh->rollback();
            }
            throw $e;
        }
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
            $deletedOrderNum =
                simplifyDBResult($this->dbh->select($this->getTableName(), $this->getOrderColumn(), array($this->getPK() => $id)), $this->getOrderColumn(), true);

            $ids =
                simplifyDBResult($this->dbh->select($this->getTableName(), array($this->getPK()), array_merge($this->getFilter(), array(
                    $orderColumn .
                    ' > ' .
                    $deletedOrderNum)), array($orderColumn => QAL::ASC)), $this->getPK());

        }
        $this->dbh->modify(QAL::DELETE, $this->getTableName(), null, array($this->getPK() => $id));

        //если определен порядок следования перестраиваем индекс сортировки
        if ($orderColumn && $ids) {
            $this->addFilterCondition(array($this->getPK() => $ids));
            $request =
                'UPDATE ' . $this->getTableName() . ' SET ' . $orderColumn .
                ' = ' . $orderColumn . ' - 1 ' .
                $this->dbh->buildWhereCondition($this->getFilter());

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
        if (isset($_POST['languageID']) && $this->getState() == 'getRawData') {
            $langID = $_POST['languageID'];
            if (!E()->getLanguage()->isValidLangID($langID)) {
                throw new SystemException('ERR_BAD_LANG_ID', SystemException::ERR_WARNING);
            }
            $result = $langID;
        } else $result = parent::getDataLanguage();

        return $result;
    }

    /**
     * Выводит данные в JSON формате для AJAX
     *
     * @return void
     * @access protected
     */

    protected function getRawData() {

        $this->setParam('onlyCurrentLang', true);
        $this->getConfig()->setCurrentState(self::DEFAULT_STATE_NAME);
        $this->setBuilder(new JSONBuilder());

        $this->setDataDescription($this->createDataDescription());
        $this->createPager();

        $this->applyUserFilter();
        $this->applyUserSort();

        $data = $this->createData();
        if ($data instanceof Data) {
            $this->setData($data);
        }
        if ($this->pager) $this->getBuilder()->setPager($this->pager);
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

            $b = new JSONCustomBuilder();
            $b->setProperties(array(
                'data' => (is_int($result)) ? $result
                    : (int)$_POST[$this->getTableName()][$this->getPK()],
                'result' => true,
                'mode' => (is_int($result)) ? 'insert' : 'update'
            ));
            $this->setBuilder($b);
        } catch (SystemException $e) {
            if ($transactionStarted) {
                $this->dbh->rollback();
            }
            throw $e;
        }
    }

    /**
     * Если поле OrderColumn присутствует в списке, убираем его
     *
     * @return DataDescription
     * @access protected
     */
    protected function createDataDescription() {
        if (in_array($this->getState(), array('printData' /*, 'exportCSV'*/))) {
            $previousAction = $this->getState();
            $this->getConfig()->setCurrentState(self::DEFAULT_STATE_NAME);
            $result = parent::createDataDescription();
            $this->getConfig()->setCurrentState($previousAction);
        } else {
            $result = parent::createDataDescription();
        }

        if (
            ($col = $this->getOrderColumn()) && (
            $field = $result->getFieldDescriptionByName($col))
        ) {
            $result->removeFieldDescription($field);
        }

        return $result;
    }

    /**
     * ДЛя main убираем список значений в селекте
     * ни к чему он там
     *
     * @return array
     * @access protected
     */

    protected function getFKData($fkTableName, $fkKeyName) {
        $result = array();
        if ($this->getState() !== self::DEFAULT_STATE_NAME)
            $result =
                $this->dbh->getForeignKeyData($fkTableName, $fkKeyName, $this->document->getLang());

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
        $message['errors'][] = array('message' => $errorMessage);
        $response =
            array_merge(array('result' => false, 'header' => $this->translate('TXT_SHIT_HAPPENS')), $message);
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
    final protected function getSaver() {
        if (is_null($this->saver)) {
            $this->saver = new ExtendedSaver();
        }

        return $this->saver;
    }

    final protected function setSaver(Saver $saver) {
        $this->saver = $saver;
    }

    /**
     * Внутренний метод сохранения
     *
     * @return mixed
     * @access protected
     */

    protected function saveData() {
        $result = false;
        //если в POST не пустое значение значение первичного ключа - значит мы находимся в режиме редактирования
        if (isset($_POST[$this->getTableName()][$this->getPK()]) &&
            !empty($_POST[$this->getTableName()][$this->getPK()])
        ) {
            $mode = self::COMPONENT_TYPE_FORM_ALTER;
            $this->setFilter(array($this->getPK() => $_POST[$this->getTableName()][$this->getPK()]));
        } else {
            $mode = self::COMPONENT_TYPE_FORM_ADD;
        }

        //создаем объект описания данных
        $dataDescriptionObject = new DataDescription();

        if (!method_exists($this, $this->getPreviousState())) {
            throw new SystemException('ERR_NO_ACTION', SystemException::ERR_CRITICAL);
        }

        //получаем описание полей для метода
        $configDataDescription =
            $this->getConfig()->getStateConfig($this->getPreviousState());
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
        if (($col = $this->getOrderColumn()) && ($field =
                $this->getDataDescription()->getFieldDescriptionByName($col))
        ) {
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

        if ($saver->validate() === true) {
            $saver->setFilter($this->getFilter());
            $saver->save();
            $result = $saver->getResult();

        } else {
            //выдвигается exception который перехватывается в методе save
            throw new SystemException('ERR_VALIDATE_FORM', SystemException::ERR_WARNING, $this->saver->getErrors());
        }

        //Если у нас режим вставки и определена колонка для порядка следования, изменяем порядок следования
        if (($orderColumn = $this->getOrderColumn()) &&
            ($mode == self::COMPONENT_TYPE_FORM_ADD)
        ) {
            $this->addFilterCondition(array($this->getPK() . '!=' . $result));
            $request =
                'UPDATE ' . $this->getTableName() . ' SET ' . $orderColumn .
                '=' . $orderColumn . '+1 ' .
                $this->dbh->buildWhereCondition($this->getFilter());
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
        switch ($this->getState()) {
            case 'imageManager':
                return $this->imageManager->build();
                break;
            case 'fileLibrary':
            case 'put':
                return $this->fileLibrary->build();
                break;
            default:
                // do nothing
        }

        if ($this->getType() == self::COMPONENT_TYPE_LIST) {
            $this->addTranslation('MSG_CONFIRM_DELETE');
        }
        /*elseif($this->getTranslationTableName()) {
            $this->addTranslation('TXT_COPY_DATA_TO_ANOTHER_TAB');
        }*/

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
        if ($this->getState() == self::DEFAULT_STATE_NAME) {
            $result = false;
        } elseif ($this->getState() == 'save') {
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

        } else {
            $result = parent::loadData();
        }
        return $result;
    }

    /**
     * Выводит список в файл в формате CSV
     *
     * @todo не подхватывает фильтр, а должен
     * @return void
     * @access protected
     */

    protected function exportCSV() {
        $sp = $this->getStateParams(true);
        if (isset($sp['encoding'])) {
            $encoding = $sp['encoding'];
        } else {
            $encoding = 'utf-8';
        }

        //Если у нас есть таблица с переводами то експортить не получится
        if ($this->getTranslationTableName()) {
            throw new SystemException('ERR_CANT_EXPORT', SystemException::ERR_DEVELOPER);
        }

        $this->setDataDescription($dd = $this->createDataDescription());

        $selectFields = $multiFields = array();
        //собираем перечень всех селект полей
        if ($sf = $dd->getFieldDescriptionsByType(FieldDescription::FIELD_TYPE_SELECT)) {
            foreach ($sf as $name => $fd) {
                $selectFields[$name] = $fd->getAvailableValues();
            }
        }
        //Собираем перечень всех мультиполей
        if ($mf = $dd->getFieldDescriptionsByType(FieldDescription::FIELD_TYPE_MULTI)) {
            foreach ($mf as $name => $fd) {
                //значения мультиполей
                $multiFieldsData[$name] = $fd;
                //Для замены имени поля в списке полей
                $multiFields[$name] = 'GROUP_CONCAT(' . $name . '.fk_id) as ' . $name;
            }
        }

        $data = '';
        $titles = array();
        //первая строка(заголовки полей)
        foreach ($dd as $fieldInfo) {
            $titles[] = $fieldInfo->getPropertyValue('title');
        }
        $data .= $this->prepareCSVString($titles);

        if ($fields = $dd->getFieldDescriptionList()) {
            //Хитросделанная конструкция чтобы получить список полей с замещенными названиями для мультиполей
            $fields = array_values(
                array_merge(
                    array_combine(
                        $fields, $fields
                    ),
                    $multiFields
                )
            );

            $request = 'SELECT ' . implode(',', $fields) . ' FROM ' . $this->getTableName() . '';
            //Для мультиполей добавляем JOIN и группировку по первичному ключу, для того чтобы можно было использовать GROUP_CONCAT
            if (!empty($multiFields)) {
                foreach (array_values($multiFieldsData) as $fieldProps) {
                    $mTableName = $fieldProps->getPropertyValue('key');
                    $mTableName = $mTableName['tableName'];
                    $request .= ' LEFT JOIN ' . $mTableName . ' USING(' . $this->getPK() . ')';
                }

                $request .= ' GROUP BY ' . $this->getPK();
            }
            //в $data накапливаем строки
            $res = $this->dbh->get($request);

            if ($res && $res->rowCount()) {
                while ($row = $res->fetch(PDO::FETCH_LAZY)) {
                    $tmpRow = array();
                    foreach ($row as $fieldName => $fieldValue) {
                        if ($fd = $dd->getFieldDescriptionByName($fieldName)) {
                            switch ($fd->getType()) {
                                case FieldDescription::FIELD_TYPE_DATE:
                                case FieldDescription::FIELD_TYPE_TIME:
                                case FieldDescription::FIELD_TYPE_DATETIME:
                                    if ($format = $fieldInfo->getPropertyValue('outputFormat')) {
                                        $fieldValue = AbstractBuilder::enFormatDate($fieldValue, $format, $fd->getType());
                                    }
                                    break;
                                case FieldDescription::FIELD_TYPE_SELECT:
                                    if (isset($selectFields[$fieldName][$fieldValue])) {
                                        $fieldValue = $selectFields[$fieldName][$fieldValue]['value'];
                                    }
                                    break;

                                case FieldDescription::FIELD_TYPE_BOOL:
                                    $fieldValue = ($fieldValue) ? $this->translate('TXT_YES') : $this->translate('TXT_NO');
                                    break;
                                case FieldDescription::FIELD_TYPE_MULTI:

                                    if ($fieldValue && isset($multiFieldsData[$fieldName])) {
                                        $value = explode(',', $fieldValue);
                                        $fieldValue = array();
                                        $multiFieldValues = $multiFieldsData[$fieldName]->getAvailableValues();
                                        foreach ($value as $v) {
                                            if (isset($multiFieldValues[$v])) {
                                                array_push($fieldValue, $multiFieldValues[$v]['value']);
                                            }
                                        }
                                        $fieldValue = implode(', ', $fieldValue);
                                    }
                                    break;
                            }

                            $tmpRow[] = $fieldValue;
                        }

                    }
                    if ($tmpRow)
                        $data .= $this->prepareCSVString($tmpRow);
                }
            }

        }

        $filename = $this->getTitle() . '.csv';
        $MIMEType = 'application/csv';

        if ($encoding != 'utf-8') {
            $data = iconv('utf-8', $encoding . '//TRANSLIT', $data);
        }
        $this->downloadFile($data, $MIMEType, $filename);
    }

    /**
     * Формирует список для печати
     *
     * @return void
     */
    protected function printData() {
        $this->setParam('recordsPerPage', false);
        if (E()->getController()->getViewMode() ==
            DocumentController::TRANSFORM_HTML
        )
            E()->getController()->getTransformer()->setFileName('print.xslt');
        $this->prepare();
    }

    protected function prepareCSVString(Array $nextValue) {
        $separator = '"';
        $delimiter = ';';
        $rowDelimiter = "\r\n";
        $row = '';
        foreach ($nextValue as $fieldValue) {
            $row .= $separator .
                //mb_convert_encoding(str_replace(array($separator, $delimiter), array("''", ','), $fieldValue), 'Windows-1251', 'UTF-8') .
                str_replace(array($separator, $delimiter), array("''", ','), $fieldValue) .
                $separator . $delimiter;
        }
        $row = substr($row, 0, -1);

        return $row . $rowDelimiter;
    }


    /**
     * Выводит компонент: менеджер изображений
     *
     * @return void
     * @access protected
     */
    protected function imageManager() {
        $this->imageManager =
            $this->document->componentManager->createComponent('imagemanager', 'share', 'ImageManager', null);
        //$this->imageManager->getState();
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
        $this->fileLibrary = $this->document->componentManager->createComponent('filelibrary', 'share', 'FileRepository', array('config' => 'core/modules/share/config/FileRepositoryModal.component.xml'));
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
            list($dirname, $basename, $extension, $filename) =
                array_values(pathinfo($sourceFileName));
            $destFileName =
                $dirname . '/' . '.' . $filename . '.' . $width . '-' .
                $height . '.' . $extension;
            if (
                (
                    file_exists($fullDestFileName =
                        dirname($_SERVER['SCRIPT_FILENAME']) .
                        '/' .
                        $destFileName)
                    && $rewrite
                )
                || !file_exists($fullDestFileName)
            ) {
                $image = new Image();
                $image->loadFromFile($sourceFileName);
                $image->resize($width, $height);
                $image->saveToFile($destFileName);

                //Сохраняем в БД
                $this->dbh->modify(QAL::UPDATE, $this->getTableName(), array($destFieldName => $destFileName), $filter);
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
        if (is_null($this->orderColumn)) {
            $this->orderColumn = false;
            $columns = $this->dbh->getColumnsInfo($this->getTableName());
            foreach (array_keys($columns) as $columnName) {
                if (strpos($columnName, '_order_num')) {
                    $this->setOrderColumn($columnName);
                    break;
                }
            }
        }
        return $this->orderColumn;
    }

    /**
     * Перемещает сущность на позицию выше
     * другой выбранной сущности
     *
     * @throws SystemException
     * @access protected
     * @return void
     */
    protected function move() {
        if (!$this->getOrderColumn()) {
            //Если не задана колонка для пользовательской сортировки то на выход
            throw new SystemException('ERR_NO_ORDER_COLUMN', SystemException::ERR_DEVELOPER);
        }
        $params = $this->getStateParams();
        list($firstItem, $secondItem) = $params;

        if ((($firstItem = intval($firstItem)) && ($secondItem = intval($secondItem)))
            && ($firstItem != $secondItem)
        ) {
            $secondItemOrderNum = $this->dbh->getScalar(
                'SELECT ' . $this->getOrderColumn() . ' as secondItemOrderNum ' .
                'FROM ' . $this->getTableName() . ' ' .
                'WHERE ' . $this->getPK() . ' = ' . $secondItem
            );

            $this->dbh->beginTransaction();
            $this->dbh->modify('UPDATE ' . $this->getTableName() . ' SET ' . $this->getOrderColumn() . ' = ' . $this->getOrderColumn()
            . ' + 1 WHERE ' . $this->getOrderColumn() . ' >= ' . $secondItemOrderNum);
            $this->dbh->modify(
                QAL::UPDATE,
                $this->getTableName(),
                array($this->getOrderColumn() => $secondItemOrderNum),
                array($this->getPK() => $firstItem)
            );
            $this->dbh->commit();
        }

        $b = new JSONCustomBuilder();
        $b->setProperty('result', true);
        $this->setBuilder($b);
    }

    /**
     * Метод для изменения порядка следования  - вверх
     *
     * @return void
     * @access protected
     */

    protected function up() {
        $this->changeOrder(Grid::DIR_UP);
    }

    /**
     * Метод для изменения порядка следования  - вниз
     *
     * @return void
     * @access protected
     */

    protected function down() {
        $this->changeOrder(Grid::DIR_DOWN);
    }

    /**
     * Изменяет порядок следования
     *
     * @param string  - направление
     * @return void
     * @access protected
     */

    protected function changeOrder($direction) {


        if (!$this->getOrderColumn()) {
            //Если не задана колонка для пользовательской сортировки то на выход
            throw new SystemException('ERR_NO_ORDER_COLUMN', SystemException::ERR_DEVELOPER);
        }

        $currentID = $this->getStateParams();
        list($currentID) = $currentID;

        //Определяем order_num текущей страницы
        $currentOrderNum = simplifyDBResult(
            $this->dbh->selectRequest(
                'SELECT ' . $this->getOrderColumn() . ' ' .
                'FROM ' . $this->getTableName() . ' ' .
                'WHERE ' . $this->getPK() . ' = %s',
                $currentID
            ),
            $this->getOrderColumn(),
            true
        );

        $orderDirection = ($direction == Grid::DIR_DOWN) ? QAL::ASC : QAL::DESC;

        $baseFilter = $this->getFilter();

        if (!empty($baseFilter)) {
            $baseFilter = ' AND ' .
                str_replace('WHERE', '', $this->dbh->buildWhereCondition($this->getFilter()));
        } else {
            $baseFilter = '';
        }

        //Определяем идентификатор записи которая находится рядом с текущей
        $request =
            'SELECT ' . $this->getPK() . ' as neighborID, ' .
            $this->getOrderColumn() . ' as neighborOrderNum ' .
            'FROM ' . $this->getTableName() . ' ' .
            'WHERE ' . $this->getOrderColumn() . ' ' . $direction .
            ' ' . $currentOrderNum . ' ' . $baseFilter .
            'ORDER BY ' . $this->getOrderColumn() . ' ' .
            $orderDirection . ' Limit 1';

        $data =
            convertDBResult($this->dbh->selectRequest($request), 'neighborID');
        if ($data) {
            extract(current($data));
            $this->dbh->beginTransaction();
            $this->dbh->modify(
                QAL::UPDATE,
                $this->getTableName(),
                array($this->getOrderColumn() => $neighborOrderNum),
                array($this->getPK() => $currentID)
            );
            $this->dbh->modify(
                QAL::UPDATE,
                $this->getTableName(),
                array($this->getOrderColumn() => $currentOrderNum),
                array($this->getPK() => $neighborID)
            );
            $this->dbh->commit();
        }
        $b = new JSONCustomBuilder();
        $b->setProperties(array(
            'result' => true,
            'dir' => $direction
        ));
        $this->setBuilder($b);
    }

    /**
     * Метод применяющий фильтр в гриде
     *
     * @return void
     * @access protected
     */

    protected function applyUserFilter() {
        //Формат фильтра
        //$_POST['filter'][$tableName][$fieldName] = значение фильтра
        if (isset($_POST['filter'])) {
            $condition = $_POST['filter']['condition'];
            $conditionPatterns = array(
                'like' => 'LIKE \'%%%s%%\'',
                'notlike' => 'NOT LIKE \'%%%s%%\'',
                '=' => '= \'%s\'',
                '!=' => '!= \'%s\'',
                '<' => '<\'%s\'',
                '>' => '>\'%s\'',
                'between' => 'BETWEEN \'%s\' AND \'%s\''
            );

            unset($_POST['filter']['condition']);
            $tableName = key($_POST['filter']);
            $fieldName = key($_POST['filter'][$tableName]);
            $values = $_POST['filter'][$tableName][$fieldName];

            if (
                $this->dbh->tableExists($tableName) &&
                ($tableInfo = $this->dbh->getColumnsInfo($tableName)) &&
                isset($tableInfo[$fieldName]) &&
                is_array($tableInfo[$fieldName]['key'])
            ) {
                $fkTranslationTableName =
                    $this->dbh->getTranslationTablename($tableInfo[$fieldName]['key']['tableName']);
                $fkTableName =
                    ($fkTranslationTableName) ? $fkTranslationTableName
                        : $tableInfo[$fieldName]['key']['tableName'];
                $fkValueField = substr($fkKeyName =
                        $tableInfo[$fieldName]['key']['fieldName'], 0, strrpos($fkKeyName, '_')) .
                    '_name';
                $fkTableInfo = $this->dbh->getColumnsInfo($fkTableName);
                if (!isset($fkTableInfo[$fkValueField])) $fkValueField = $fkKeyName;

                if ($res =
                    simplifyDBResult($this->dbh->select($fkTableName, $fkKeyName,
                        $fkTableName . '.' .
                        $fkValueField .
                        ' ' .
                        call_user_func_array('sprintf', array_merge(array($conditionPatterns[$condition]), $values)) .
                        ' '), $fkKeyName)
                ) {
                    $this->addFilterCondition(array($tableName . '.' .
                    $fieldName => $res));
                } else {
                    $this->addFilterCondition(' FALSE');
                }
            } else {
                if (in_array($condition, array('like', 'notlike')) && in_array($this->getDataDescription($fieldName), array('date', 'datetime', 'time'))) {
                    if ($condition == 'like') {
                        $condition = '=';
                    } else {
                        $condition = '!=';
                    }
                }
                $tableName = ($tableName) ? $tableName . '.' : '';
                $this->addFilterCondition(
                    $tableName . $fieldName . ' ' .
                    call_user_func_array('sprintf', array_merge(array($conditionPatterns[$condition]), $values)) .
                    ' '
                );
            }
        }
        //inspect($this->getFilter());
    }

    /**
     * Применение сортировки
     *
     * @return void
     */
    protected function applyUserSort() {
        $actionParams = $this->getStateParams(true);
        if (isset($actionParams['sortField']) &&
            isset($actionParams['sortDir'])
        ) {
            //подразумевается что sortDir - тоже существует
            $this->setOrder(array($actionParams['sortField'] => $actionParams['sortDir']));
        }
    }

    /**
     * Добавляет переводы для WYSIWYG при необходимости
     *
     * @access private
     * @return void
     */
    private function addToolbarTranslations() {
        foreach ($this->getDataDescription() as $fd) {
            if (($fd->getType() == FieldDescription::FIELD_TYPE_HTML_BLOCK)) {
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
    protected function addAttFilesField($tableName, $data = false) {
        if ($this->dbh->tableExists($this->getTableName() . AttachmentManager::ATTACH_TABLE_PREFIX)) {
            $am = new AttachmentManager(
                $this->getDataDescription(),
                $this->getData(),
                $tableName
            );
            $am->createAttachmentTab($data);

            //Ссылки на добавление и удаление файла
            $this->addTranslation('BTN_ADD_FILE', 'BTN_LOAD_FILE', 'BTN_DEL_FILE', 'BTN_UP', 'BTN_DOWN', 'MSG_NO_ATTACHED_FILES');
        }
    }

}