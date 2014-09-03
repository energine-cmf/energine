<?php
/**
 * @file
 * Grid
 *
 * It contains the definition to:
 * @code
class Grid;
 * @endcode
 *
 * @author dr.Pavka
 * @copyright Energine 2006
 *
 * @version 1.0.0
 */
namespace Energine\share\components;

use Energine\share\gears\AbstractBuilder;
use Energine\share\gears\DocumentController;
use Energine\share\gears\ExtendedSaver;
use Energine\share\gears\FilterField;
use Energine\share\gears\GridConfig;
use Energine\share\gears\Saver;
use Energine\share\gears\SystemException, Energine\share\gears\FieldDescription, Energine\share\gears\QAL, Energine\share\gears\JSONCustomBuilder, Energine\share\gears\Filter, Energine\share\gears\ComponentConfig, Energine\share\gears\JSONBuilder, Energine\share\gears\TagManager, Energine\share\gears\Field, Energine\share\gears\AttachmentManager, Energine\share\gears\Image, Energine\share\gears\Data, Energine\share\gears\DataDescription;

/**
 * Grid.
 *
 * @code
class Grid;
 * @endcode
 */
class Grid extends DBDataSet {
    /**
     * Direction: up.
     * @var string DIR_UP
     */
    const DIR_UP = '<';
    /**
     * Direction: dowm.
     * @var string DIR_DOWN
     */
    const DIR_DOWN = '>';

    /**
     * Component: attachment editor manager.
     * @var AttachmentEditor $attachmentEditor
     */
    protected $attachmentEditor;

    /**
     * Tag editor.
     * @var TagEditor $tagEditor
     */
    protected $tagEditor;

    /**
     * Saver.
     * @var Saver $saver
     */
    protected $saver;

    /**
     * Column name for user sorting.
     * @var string $orderColumn
     */
    private $orderColumn = null;

    /**
     * Filter.
     * @var Filter $filter_control
     */
    protected $filter_control;

    /**
     * Grid for select fields
     * @var Grid
     */
    protected $fkCRUDEditor = null;

    /**
     * @copydoc DBDataSet::__construct
     */
    public function __construct($name, $module, array $params = null) {
        parent::__construct($name, $module, $params);

        $this->setProperty('exttype', 'grid');
        if (!$this->getParam('recordsPerPage')) {
            $this->setParam('recordsPerPage', DataSet::RECORD_PER_PAGE);
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
     * @copydoc DBDataSet::defineParams
     */
    protected function defineParams() {
        $params = array();
        if (!$this->params['config']) {
            $fileName = simplifyClassName(get_class($this)) . '.component.xml';
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
     * @copydoc DBDataSet::getConfig
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
     * Show add form.
     */
    protected function add() {
        $this->setType(self::COMPONENT_TYPE_FORM_ADD);
        $this->prepare();
        $this->addToolbarTranslations();
        $this->linkExtraManagers($this->getTableName());
        foreach ($this->getDataDescription() as $fdName => $fieldDescription) {
            if (($default = $fieldDescription->getPropertyValue('default')) || ($default === '0')) {
                if (!($f = $this->getData()->getFieldByName($fdName))) {
                    $f = new Field($fdName);
                    $this->getData()->addField($f);
                }
                $f->setData($default, true);
            }
        }
    }

    /**
     * Show edit form.
     *
     * @throws SystemException 'ERR_404'
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
        $this->linkExtraManagers($this->getTableName());
    }

    /**
     * Delete.
     *
     * @return mixed
     *
     * @see Grid::save()
     *
     * @throws SystemException 'ERR_404'
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
     * Delete record.
     *
     * @param int $id Record ID.
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
     * @copydoc DBDataSet::getDataLanguage
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
     * Show data in JSON format for AJAX
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
     * Single mode state that show Grid for select field values
     */
    protected function fkEditor() {
        list($fkField, $className) = $this->getStateParams();
        $className = explode('\\', urldecode($className));
        //Using tmp variable $class because list() cannot  modify variable it is working with
        if (sizeof($className) > 1) {
            list($module, $class) = $className;
        } else {
            $module = $this->module;
            list($class) = $className;
        }
        unset($className);
        $params = array();
        if ($class == 'Grid') {
            $cols = $this->dbh->getColumnsInfo($this->getTableName());
            if (!in_array($fkField, array_keys($cols)) && $this->getTranslationTableName()) {
                $cols = $this->dbh->getColumnsInfo($this->getTranslationTableName());
                if (!in_array($fkField, array_keys($cols))) {
                    throw new SystemException('ERR_NO_COLUMN', SystemException::ERR_DEVELOPER, $fkField);
                }
            } elseif (!$this->getTranslationTableName()) {
                throw new SystemException('ERR_NO_COLUMN', SystemException::ERR_DEVELOPER, $fkField);
            }
            if (!is_array($cols[$fkField]['key'])) {
                throw new SystemException('ERR_BAD_FK_COLUMN', SystemException::ERR_DEVELOPER, $fkField);
            }
            $params['tableName'] = $cols[$fkField]['key']['tableName'];
        } else {
            try {
                class_exists($class);
            } catch (SystemException $e) {
                throw new SystemException('ERR_BAD_CLASS', SystemException::ERR_DEVELOPER, $class);
            }
            if (!is_subclass_of($class, 'Grid')) {
                throw new SystemException('ERR_BAD_CLASS', SystemException::ERR_DEVELOPER, $class);
            }
        }
        //Search for modal component config
        if (!file_exists($config = CORE_REL_DIR . sprintf(ComponentConfig::CORE_CONFIG_DIR, $module) . $class . 'Modal.component.xml')) {
            if (!file_exists($config = CORE_REL_DIR . sprintf(ComponentConfig::CORE_CONFIG_DIR, $module) . $class . '.component.xml')) {
                $config = CORE_REL_DIR . sprintf(ComponentConfig::CORE_CONFIG_DIR, 'share') . 'GridModal.component.xml';
            }
        }
        $params['config'] = $config;


        $this->request->shiftPath(2);
        $this->fkCRUDEditor = $this->document->componentManager->createComponent('fkEditor', $module, $class, $params);
        $this->fkCRUDEditor->run();
    }

    /**
     * Single mode state for gettting latest values from FK table
     */
    protected function fkValues() {
        list($fkField) = $this->getStateParams();
        $cols = $this->dbh->getColumnsInfo($this->getTableName());
        if (!in_array($fkField, array_keys($cols)) && $this->getTranslationTableName()) {
            $cols = $this->dbh->getColumnsInfo($this->getTranslationTableName());
            if (!in_array($fkField, array_keys($cols))) {
                throw new SystemException('ERR_NO_COLUMN', SystemException::ERR_DEVELOPER, $fkField);
            }
        } elseif (!$this->getTranslationTableName()) {
            throw new SystemException('ERR_NO_COLUMN', SystemException::ERR_DEVELOPER, $fkField);
        }
        if (!is_array($cols[$fkField]['key'])) {
            throw new SystemException('ERR_BAD_FK_COLUMN', SystemException::ERR_DEVELOPER, $fkField);
        }

        $builder = new JSONCustomBuilder();
        $builder->setProperty('result', $this->getFKData($cols[$fkField]['key']['tableName'], $cols[$fkField]['key']['fieldName']));
        $this->setBuilder($builder);
    }

    //todo VZ: What is the trick with external and internal methods?
    /**
     * Save.
     *
     * @note This is 'external' method that calls 'internal' Grid::saveData.
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
     * @copydoc DBDataSet::createData
     */
    protected function createData() {
        if (in_array($this->getType(), array(self::COMPONENT_TYPE_FORM_ADD, self::COMPONENT_TYPE_FORM_ALTER, self::COMPONENT_TYPE_FORM))) {
            $dd = $this->getDataDescription();
            if ($selects = $dd->getFieldDescriptionsByType(FieldDescription::FIELD_TYPE_SELECT)) {
                foreach ($selects as $select) {
                    //is null - use default
                    //is empty - no editor
                    //string - check for class existance
                    $editorClassName = $select->getPropertyValue('editor');
                    if (is_null($editorClassName)) {
                        $select->setProperty('editor', 'Grid');
                    } elseif (empty($editorClassName)) {
                        $select->removeProperty('editor');
                    } else {
                        $editorClassName = explode('\\', $editorClassName);
                        if (sizeof($editorClassName) > 1) {
                            $editorClassName = $editorClassName[1];
                        } else {
                            list($editorClassName) = $editorClassName;
                        }
                        try {
                            class_exists($editorClassName);
                        } catch (SystemException $e) {
                            throw new SystemException('ERR_NO_EDITOR_CLASS', SystemException::ERR_DEVELOPER, $editorClassName);
                        }
                    }
                }
            }
        }
        $r = parent::createData();
        return $r;
    }

    /**
     * @copydoc DBDataSet::createDataDescription
     */
    protected function createDataDescription() {
        //Если поле OrderColumn присутствует в списке, убираем его
        if (in_array($this->getState(), array('printData' /*, 'exportCSV'*/))) {
            $previousAction = $this->getState();
            $this->getConfig()->setCurrentState(self::DEFAULT_STATE_NAME);
            $result = parent::createDataDescription();
            $this->getConfig()->setCurrentState($previousAction);
        } else {
            $result = parent::createDataDescription();
        }

        if (($col = $this->getOrderColumn())
            && ($field = $result->getFieldDescriptionByName($col))
        ) {
            $result->removeFieldDescription($field);
        }

        return $result;
    }

    /**
     * @copydoc DBDataSet::getFKData
     */
    protected function getFKData($fkTableName, $fkKeyName) {
        // Для main убираем список значений в селекте, ни к чему он там
        $result = array();
        if ($this->getState() !== self::DEFAULT_STATE_NAME)
            $result =
                $this->dbh->getForeignKeyData($fkTableName, $fkKeyName, $this->document->getLang());

        return $result;
    }

    /**
     * Generate error.
     *
     * @param string $errorType Error type.
     * @param string $errorMessage Error message.
     * @param mixed $errorCustomInfo Optional additional info about error.
     * @return array
     */
    protected function generateError($errorType, $errorMessage, $errorCustomInfo = false) {
        $message['errors'][] = array('message' => $errorMessage);
        $response =
            array_merge(array('result' => false, 'header' => $this->translate('TXT_SHIT_HAPPENS')), $message);
        return $response;
    }

    /**
     * Get saver.
     *
     * @return Saver
     *
     * @final
     *
     * @note There is only reason to call this function inside save/saveSate, in other cases it will return false.
     */
    final protected function getSaver() {
        if (is_null($this->saver)) {
            $this->saver = new ExtendedSaver();
        }

        return $this->saver;
    }

    /**
     * Set saver.
     *
     * @param Saver $saver Saver.
     */
    final protected function setSaver(Saver $saver) {
        $this->saver = $saver;
    }

    /**
     * Save data.
     *
     * @return mixed
     *
     * @throws SystemException 'ERR_NO_ACTION'
     * @throws SystemException 'ERR_VALIDATE_FORM'
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
     * @copydoc DBDataSet::build
     *
     * @note It includes translations and information about tabs.
     */
    public function build() {
        switch ($this->getState()) {
            /*case 'imageManager':
                return $this->imageManager->build();
                break;
            case 'fileLibrary':
            case 'put':
                return $this->fileLibrary->build();
                break;*/
            case 'attachments':
                return $this->attachmentEditor->build();
                break;
            case 'tags':
                return $this->tagEditor->build();
                break;
            case 'fkEditor':
                return $this->fkCRUDEditor->build();
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

        if (!empty($this->filter_control)) {
            if ($f = $this->filter_control->build()) {
                $result->documentElement->appendChild(
                    $result->importNode($f, true)
                );
            }
        }

        return $result;
    }

    /**
     * @copydoc DBDataSet::loadData
     */
    protected function loadData() {
        // Для действия main не выводим данные
        // Для действия save определяем другой формат данных
        if ($this->getState() == self::DEFAULT_STATE_NAME or $this->getState() == 'move') {
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
     * Export the list into CSV file.
     *
     * @throws SystemException 'ERR_CANT_EXPORT'
     *
     * @todo не подхватывает фильтр, а должен
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
                while ($row = $res->fetch(\PDO::FETCH_LAZY)) {
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
     * Prepare the list for printing.
     */
    protected function printData() {
        $this->setParam('recordsPerPage', false);
        if (E()->getController()->getViewMode() ==
            DocumentController::TRANSFORM_HTML
        )
            E()->getController()->getTransformer()->setFileName('print.xslt');
        $this->prepare();
    }

    /**
     * Prepare CSV string.
     *
     * @param array $nextValue Next value.
     * @return string
     */
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
     * @copydoc DBDataSet::imageManager
     */
    /*    protected function imageManager() {
            $this->imageManager =
                $this->document->componentManager->createComponent('imagemanager', 'share', 'ImageManager', null);
            //$this->imageManager->getState();
            $this->imageManager->run();
        }*/

    /**
     * @copydoc DBDataSet::fileLibrary
     */
    /*    protected function fileLibrary() {
            $this->request->setPathOffset($this->request->getPathOffset() + 1);
            $this->fileLibrary = $this->document->componentManager->createComponent('filelibrary', 'share', 'FileRepository', array('config' => 'core/modules/share/config/FileRepositoryModal.component.xml'));
            $this->fileLibrary->run();
        }*/

    /**
     * Show component: attachments.
     */
    protected function attachments() {
        $sp = $this->getStateParams(true);
        $attachmentEditorParams = array(
            'origTableName' => $this->getTableName(),
            'pk' => $this->getPK(),
            'tableName' => $this->getTableName() . AttachmentManager::ATTACH_TABLE_SUFFIX,
        );

        if (isset($sp['id'])) {
            $this->request->shiftPath(2);
            $attachmentEditorParams['linkedID'] = $sp['id'];
        } else {
            $this->request->shiftPath(1);
        }

        $this->attachmentEditor = $this->document->componentManager->createComponent(
            'attachmentEditor', 'share', 'AttachmentEditor', $attachmentEditorParams
        );
        $this->attachmentEditor->run();
    }

    /**
     * Show component: tag editor.
     */
    protected function tags() {
        $this->request->setPathOffset($this->request->getPathOffset() + 1);
        $this->tagEditor = $this->document->componentManager->createComponent('tageditor', 'share', 'TagEditor', array('config' => 'core/modules/share/config/TagEditorModal.component.xml'));
        $this->tagEditor->run();
    }

    /**
     * Generate thumbnails and save them in data base.
     *
     * @param string $sourceFileName Source filename.
     * @param string $destFieldName Destination filed name.
     * @param int $width Width
     * @param int $height Height.
     * @param array $filter Filter.
     * @param bool $rewrite Overwrite existed?
     * @return bool|string
     */
    protected function generateThumbnail($sourceFileName, $destFieldName, $width, $height, $filter, $rewrite = true) {
        $destFileName = false;
        if (!empty($sourceFileName)) {
            list($dirname, $basename, $extension, $filename) =
                array_values(pathinfo($sourceFileName));
            $destFileName =
                $dirname . '/' . '.' . $filename . '.' . $width . '-' .
                $height . '.' . $extension;
            if ((
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
     * Set column name for user sorting.
     *
     * @param string $columnName Column name.
     *
     * @see Grid::getOrderColumn
     */
    protected function setOrderColumn($columnName) {
        $this->orderColumn = $columnName;
        $this->setOrder(array($columnName => QAL::ASC));
    }

    /**
     * Get column name for user sorting.
     *
     * @return string
     *
     * @see Grid::setOrderColumn
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
     * Show GRID for moving element.
     *
     * @throws SystemException 'ERR_NO_ORDER_COLUMN'
     * @throws SystemException 'ERR_404'
     */
    protected function move() {
        if (!$this->getOrderColumn()) {
            //Если не задана колонка для пользовательской сортировки то на выход
            throw new SystemException('ERR_NO_ORDER_COLUMN', SystemException::ERR_DEVELOPER);
        }
        $id = $this->getStateParams();
        list($id) = $id;
        if (!$this->recordExists($id)) {
            throw new SystemException('ERR_404', SystemException::ERR_404);
        }
        $this->setType(self::COMPONENT_TYPE_LIST);
        $this->setProperty('moveFromId', $id);
        //$this->addTranslation('TXT_FILTER', 'BTN_APPLY_FILTER', 'TXT_RESET_FILTER', 'TXT_FILTER_SIGN_BETWEEN', 'TXT_FILTER_SIGN_CONTAINS', 'TXT_FILTER_SIGN_NOT_CONTAINS');
        $this->prepare();
    }

    /**
     * Move the record.
     *
     * Allowed movement:
     *
     * - above
     * - below
     * - top
     * - bottom
     *
     * @todo: Пофиксить перемещение в начало списка, т.к. сейчас порядковый номер может выйти меньше 0. Аналогичная ситуация с move above.
     *
     * @throws SystemException 'ERR_NO_ORDER_COLUMN'
     */
    protected function moveTo() {
        if (!$this->getOrderColumn()) {
            //Если не задана колонка для пользовательской сортировки то на выход
            throw new SystemException('ERR_NO_ORDER_COLUMN', SystemException::ERR_DEVELOPER);
        }
        $params = $this->getStateParams();
        list($firstItem, $direction) = $params;

        $allowed_directions = array('first', 'last', 'above', 'below');
        if (in_array($direction, $allowed_directions) && $firstItem == intval($firstItem)) {
            switch ($direction) {
                // двигаем элемент с id=$firstItem на самый верх
                case 'first':
                    $oldFirstItem = (int)$this->dbh->getScalar('SELECT MIN(' . $this->getOrderColumn() . ') FROM ' . $this->getTableName() . ' LIMIT 1');
                    if ($oldFirstItem != $firstItem) {
                        $this->dbh->modify(
                            QAL::UPDATE,
                            $this->getTableName(),
                            array($this->getOrderColumn() => $oldFirstItem - 1),
                            array($this->getPK() => $firstItem)
                        );
                    }
                    break;
                // двигаем элемент с id=$firstItem в самый низ
                case 'last':
                    $oldLastItem = (int)$this->dbh->getScalar('SELECT MAX(' . $this->getOrderColumn() . ') FROM ' . $this->getTableName() . ' LIMIT 1');
                    if ($oldLastItem != $firstItem) {
                        $this->dbh->modify(
                            QAL::UPDATE,
                            $this->getTableName(),
                            array($this->getOrderColumn() => $oldLastItem + 1),
                            array($this->getPK() => $firstItem)
                        );
                    }
                    break;
                // двигаем элемент выше или ниже id=$secondItem
                case 'above':
                case 'below':
                    $secondItem = (!empty($params[2])) ? $params[2] : null;
                    if ($secondItem == intval($secondItem) && $firstItem != $secondItem) {
                        $secondItemOrderNum = $this->dbh->getScalar(
                            'SELECT ' . $this->getOrderColumn() . ' as secondItemOrderNum ' .
                            'FROM ' . $this->getTableName() . ' ' .
                            'WHERE ' . $this->getPK() . ' = ' . $secondItem
                        );
                        $this->dbh->beginTransaction();
                        // сдвигаем все элементы выше или ниже второго id
                        $this->dbh->select(
                            'UPDATE ' . $this->getTableName() . ' ' .
                            'SET ' . $this->getOrderColumn() . ' = ' .
                            $this->getOrderColumn() . (($direction == 'below') ? ' +2 ' : ' -2 ') .
                            'WHERE ' . $this->getOrderColumn() . (($direction == 'below') ? ' > ' : ' < ') .
                            intval($secondItemOrderNum)
                        );
                        // устанавливаем новый порядок для первого id
                        $this->dbh->modify(
                            QAL::UPDATE,
                            $this->getTableName(),
                            array($this->getOrderColumn() => (($direction == 'below') ? $secondItemOrderNum + 1 : $secondItemOrderNum - 1)),
                            array($this->getPK() => $firstItem)
                        );
                        $this->dbh->commit();
                    }
                    break;
            }
        }

        $b = new JSONCustomBuilder();
        $b->setProperty('result', true);
        $this->setBuilder($b);
    }

    /**
     * Change the moving direction to Grid::DIR_UP.
     */
    protected function up() {
        $this->changeOrder(Grid::DIR_UP);
    }

    /**
     * Change the moving direction to Grid::DIR_DOWN.
     */
    protected function down() {
        $this->changeOrder(Grid::DIR_DOWN);
    }

    /**
     * Change order.
     *
     * @param string $direction Direction.
     *
     * @throws SystemException 'ERR_NO_ORDER_COLUMN'
     */
    protected function changeOrder($direction) {
        $this->applyUserFilter();
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
            $neighborID = null;
            $neighborOrderNum = 0;
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
     * Apply user filter.
     */
    protected function applyUserFilter() {
        $filter = new Filter();
        $filter->apply($this);
    }

    /**
     * Apply user sorting.
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
     * Add translations for WYSIWYG.
     */
    private function addToolbarTranslations() {
        foreach ($this->getDataDescription() as $fd) {
            if (($fd->getType() == FieldDescription::FIELD_TYPE_HTML_BLOCK)) {
                $this->addWYSIWYGTranslations();
                break;
            }
        }
    }

    // Сохранение приаттаченных данных должно происходить в методе saveData на общих основаниях
    //todo VZ: $data is not used.
    /**
     * Build the list of additional files.
     *
     * @param string $tableName Table name.
     * @param bool $data Data.
     *
     * @see DivisionEditor
     * @see ProductEditor
     *
     * @note It is used for the cases when additional tab with attached files to the record should be created.
     */
    protected function linkExtraManagers($tableName, $data = false) {
        if ($this->dbh->tableExists($tableName . AttachmentManager::ATTACH_TABLE_SUFFIX) && $this->getState() != 'attachments') {

            $fd = new FieldDescription('attached_files');
            $fd->setType(FieldDescription::FIELD_TYPE_TAB);
            $fd->setProperty('title', $this->translate('TAB_ATTACHED_FILES'));
            $fd->setProperty('tableName', $tableName . AttachmentManager::ATTACH_TABLE_SUFFIX);
            $this->getDataDescription()->addFieldDescription($fd);

            $field = new Field('attached_files');
            $state = $this->getState();
            $tab_url = (($state != 'add') ? $this->getData()->getFieldByName($this->getPK())->getRowData(0) : '') . '/attachments/';

            $field->setData($tab_url, true);
            $this->getData()->addField($field);
        }

        if ($this->dbh->tableExists($this->getTableName() . TagManager::TAGS_TABLE_SUFFIX)) {
            $tm = new TagManager($this->getDataDescription(), $this->getData(), $this->getTableName());
            $tm->createFieldDescription();
            $tm->createField();
        }
    }

    /**
     * Autocomplete tag names.
     *
     * @throws SystemException 'ERR_NO_DATA'
     */
    protected function autoCompleteTags() {
        $b = new JSONCustomBuilder();
        $this->setBuilder($b);

        try {
            if (!isset($_POST['value'])) {
                throw new SystemException('ERR_NO_DATA', SystemException::ERR_CRITICAL);
            } else {

                $tags = TagManager::getTagStartedWith($_POST['value'], 10);
                $result['result'] = true;

                if (is_array($tags) && !empty($tags)) {
                    foreach ($tags as $tag) {
                        $result['data'][] = array(
                            'key' => $tag,
                            'value' => $tag
                        );
                    }
                }
            }
        } catch (\Exception $e) {
            $result = array(
                'result' => false,
                'data' => false,
                'errors' => array()
            );
        }

        $b->setProperties($result);
    }

    /**
     * @copydoc DBDataSet::prepare
     */
    protected function prepare() {
        parent::prepare();

        if ($this->getType() == self::COMPONENT_TYPE_LIST)
            $this->createFilter();
    }

    /**
     * Create Grid filter
     */
    protected function createFilter() {
        if ($config = $this->getConfig()->getCurrentStateConfig()) {
            $this->filter_control = new Filter();
            $cInfo = $this->dbh->getColumnsInfo($this->getTableName());
            if ($this->getTranslationTableName()) {
                $cInfo = array_merge($cInfo, $this->dbh->getColumnsInfo($this->getTranslationTableName()));
            }
            if ($config->filter) {
                $this->filter_control->load($config->filter, $cInfo);
            } else {
                foreach ($this->getDataDescription() as $fName => $fAttributes) {
                    if (in_array($fAttributes->getType(), array(FieldDescription::FIELD_TYPE_DATETIME, FieldDescription::FIELD_TYPE_DATE, FieldDescription::FIELD_TYPE_INT, FieldDescription::FIELD_TYPE_SELECT, FieldDescription::FIELD_TYPE_PHONE, FieldDescription::FIELD_TYPE_EMAIL, FieldDescription::FIELD_TYPE_STRING, FieldDescription::FIELD_TYPE_TEXT, FieldDescription::FIELD_TYPE_HTML_BLOCK, FieldDescription::FIELD_TYPE_BOOL))
                        && ($fAttributes->getPropertyValue('index') != 'PRI')
                        && !strpos($fName, '_num')
                        && array_key_exists($fName, $cInfo)
                    ) {
                        $ff = new FilterField($fName, $fAttributes->getType());
                        $ff->setAttribute('tableName', $cInfo[$fName]['tableName']);
                        $ff->setAttribute('title', 'FIELD_' . $fName);
                        $this->filter_control->attachField($ff);
                    }
                }
            }
        }
    }
}