<?php
/**
 * @file
 * Grid
 * It contains the definition to:
 * @code
class Grid;
 * @endcode
 * @author dr.Pavka
 * @copyright Energine 2006
 * @version 1.0.0
 */
namespace Energine\share\components;

use Energine\share\gears\AttachmentManager;
use Energine\share\gears\ComponentConfig;
use Energine\share\gears\Data;
use Energine\share\gears\DataDescription;
use Energine\share\gears\DocumentController;
use Energine\share\gears\ExtendedSaver;
use Energine\share\gears\Field;
use Energine\share\gears\FieldDescription;
use Energine\share\gears\Filter;
use Energine\share\gears\FilterExpression;
use Energine\share\gears\FilterField;
use Energine\share\gears\GridConfig;
use Energine\share\gears\JSONBuilder;
use Energine\share\gears\JSONCustomBuilder;
use Energine\share\gears\QAL;
use Energine\share\gears\Saver;
use Energine\share\gears\SystemException;
use Energine\share\gears\TagManager;

/**
 * Grid.
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
    private $orderColumn = NULL;

    /**
     * Filter.
     * @var Filter $filter_control
     */
    protected $filter_control;

    /**
     * Grid for select fields
     * @var \Lookup
     */
    protected $lookupEditor = NULL;

    /**
     * @var ActionLog
     */
    protected $logClass = NULL;

    /**
     * @copydoc DBDataSet::__construct
     */
    public function __construct($name, array $params = NULL) {
        parent::__construct($name, $params);

        $this->setProperty('exttype', 'grid');
        if (!$this->getParam('recordsPerPage')) {
            $this->setParam('recordsPerPage', DataSet::RECORD_PER_PAGE);
        }
        if (!$this->getTitle()) {
            $this->setTitle($this->translate(
                'TXT_' . strtoupper($this->getName())));
        }
        if ($this->getParam('order')) {
            if (in_array($this->getParam('order'), array_keys($this->dbh->getColumnsInfo($this->getTableName())))) {
                $this->orderColumn = $this->getParam('order');
            }
        }
        $this->logClass = $this->getConfigValue('site.action_log');
    }

    /**
     * @copydoc DBDataSet::defineParams
     */
    protected function defineParams() {
        $params = [];
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
                    sprintf(CORE_DIR . ComponentConfig::CORE_CONFIG_DIR, 'share') .
                    'Grid.component.xml';
            }
        }
        $params['active'] = true;
        $params['thumbnail'] =
            [$this->getConfigValue('thumbnail.width'), $this->getConfigValue('thumbnail.height')];
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
     * @return mixed
     * @see Grid::save()
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
            if ($this->logClass) {
                /**
                 * @var ActionLog $logger
                 */
                $logger = new $this->logClass(get_class($this), $this->getName());
                $logger->write(QAL::DELETE, $id);
            }
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
     * @param int $id Record ID.
     */
    protected function deleteData($id) {
        $ids = false;

        if ($orderColumn = $this->getOrderColumn()) {
            $deletedOrderNum = $this->dbh->getScalar($this->getTableName(), $this->getOrderColumn(),
                [$this->getPK() => $id]);
            if (!empty($deletedOrderNum)) {
                $ids = $this->dbh->getColumn($this->getTableName(), [$this->getPK()],
                    array_merge($this->getFilter(), [
                        $orderColumn .
                        ' > ' .
                        $deletedOrderNum
                    ]), [$orderColumn => QAL::ASC]);
            }
        }
        $this->dbh->modify(QAL::DELETE, $this->getTableName(), NULL, [$this->getPK() => $id]);

        //если определен порядок следования перестраиваем индекс сортировки
        if ($orderColumn && $ids) {
            $this->addFilterCondition([$this->getPK() => $ids]);
            $request =
                'UPDATE ' . $this->getTableName() . ' SET ' . $orderColumn .
                ' = ' . $orderColumn . ' - 1 ' .
                $this->dbh->buildWhereCondition($this->getFilter());

            $this->dbh->modify($request);
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
        } else {
            $result = parent::getDataLanguage();
        }

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
        if ($this->pager) {
            $this->getBuilder()->setPager($this->pager);
        }
    }

    /**
     * Lookup state
     *
     * @throws SystemException
     */
    protected function lookup() {
        $params = $this->getStateParams(true);
        $FKField = $params['fk_field_name'];

        if (array_key_exists('editor_class', $params)) {
            $lookupClass = str_replace('.', '\\', $params['editor_class']);
        } else {
            $lookupClass = implode('\\', ['Energine', 'share', 'components', 'Lookup']);
        }

        $columns = $this->dbh->getColumnsInfo($this->getTableName());
        if (!isset($columns[$FKField]) || !is_array($columns[$FKField]['key'])) {
            throw new SystemException('ERR_NO_FIELD', SystemException::ERR_DEVELOPER, $FKField);
        }

        $params = [
            'tableName' => $columns[$FKField]['key']['tableName']
        ];

        $this->request->shiftPath(2);
        $this->lookupEditor = $this->document->componentManager->createComponent('lookupEditor', $lookupClass, $params);
        $this->lookupEditor->run();
    }

    //todo VZ: What is the trick with external and internal methods?
    /**
     * Save.
     * @note This is 'external' method that calls 'internal' Grid::saveData.
     */
    protected function save() {
        $transactionStarted = $this->dbh->beginTransaction();
        try {
            $result = $this->saveData();
            $transactionStarted = !($this->dbh->commit());
            if ($this->logClass && $this->getSaver()->getData()) {
                $logger = new $this->logClass(get_class($this), $this->getName());
                $logger->write($this->getSaver()->getMode(), $this->getSaver()->getData()->asArray(true));

            }
            $b = new JSONCustomBuilder();
            $b->setProperties([
                'data' => (is_int($result)) ? $result
                    : (int)$_POST[$this->getTableName()][$this->getPK()],
                'result' => true,
                'mode' => (is_int($result)) ? 'insert' : 'update'
            ]);
            $this->setBuilder($b);
        } catch (\Exception $e) {
            if ($transactionStarted) {
                $this->dbh->rollback();
            }
            $code = $e->getCode();
            if (!is_numeric($code)) {
                $code = SystemException::ERR_CRITICAL;
            }
            throw new SystemException($e->getMessage(), $code);
        }
    }

    /**
     * @copydoc DBDataSet::createDataDescription
     */
    protected function createDataDescription() {
        //Если поле OrderColumn присутствует в списке, убираем его
        if (in_array($this->getState(), ['printData' /*, 'exportCSV'*/])) {
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
        $result = [];
        if ($this->getState() !== self::DEFAULT_STATE_NAME) {
            $result =
                $this->dbh->getForeignKeyData($fkTableName, $fkKeyName, $this->document->getLang());
        }

        return $result;
    }

    /**
     * Generate error.
     * @param string $errorType Error type.
     * @param string $errorMessage Error message.
     * @param mixed $errorCustomInfo Optional additional info about error.
     * @return array
     */
    protected function generateError($errorType, $errorMessage, $errorCustomInfo = false) {
        $message['errors'][] = ['message' => $errorMessage];
        $response =
            array_merge(['result' => false, 'header' => $this->translate('TXT_SHIT_HAPPENS')], $message);

        return $response;
    }

    /**
     * Get saver.
     * @return Saver
     * @final
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
     * @param Saver $saver Saver.
     */
    final protected function setSaver(Saver $saver) {
        $this->saver = $saver;
    }

    /**
     * Save data.
     * @return mixed
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
            $this->setFilter([$this->getPK() => $_POST[$this->getTableName()][$this->getPK()]]);
        } else {
            $mode = self::COMPONENT_TYPE_FORM_ADD;
        }


 
        if (!method_exists($this, $this->getPreviousState())) {
            throw new SystemException('ERR_NO_ACTION', SystemException::ERR_CRITICAL);
        }
        //создаем объект описания данных
        $dataDescriptionObject = new DataDescription();

        //получаем описание полей для метода
        $configDataDescription =
            $this->getConfig()->getStateConfig($this->getPreviousState());
        //если в конфиге есть описание полей для метода - загружаем их
        if (isset($configDataDescription->fields)) {
            $dataDescriptionObject->loadXML($configDataDescription->fields);
        }
        $currentConfigDataDescription =
            $this->getConfig()->getStateConfig($this->getState());
        if (isset($currentConfigDataDescription->fields)) {
            $dataDescriptionObject->loadXML($currentConfigDataDescription->fields);
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
            $this->addFilterCondition([$this->getPK() . '!=' . $result]);
            $request =
                'UPDATE ' . $this->getTableName() . ' SET ' . $orderColumn .
                '=' . $orderColumn . '+1 ' .
                $this->dbh->buildWhereCondition($this->getFilter());
            $this->dbh->modify($request);
        }

        return $result;
    }


    /**
     * @copydoc DBDataSet::build
     * @note It includes translations and information about tabs.
     */
    public function build() {
        switch ($this->getState()) {
            case 'attachments':
                return $this->attachmentEditor->build();
                break;
            case 'tags':
                return $this->tagEditor->build();
                break;
            case 'lookup':
                return $this->lookupEditor->build();
                break;
            default:
                // do nothing
        }

        if ($this->getType() == self::COMPONENT_TYPE_LIST) {
            $this->addTranslation('MSG_CONFIRM_DELETE');
        }

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
            $result = [$data];
            if ($this->getTranslationTableName()) {
                if (!isset($_POST[$this->getTranslationTableName()])) {
                    throw new SystemException('ERR_NO_DATA', SystemException::ERR_CRITICAL);
                }
                $result = [];
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
     * @throws SystemException 'ERR_CANT_EXPORT'
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

        $selectFields = $multiFields = [];
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
        $titles = [];
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
            $res = $this->dbh->query($request);

            if ($res && $res->rowCount()) {
                while ($row = $res->fetch(\PDO::FETCH_LAZY)) {
                    $tmpRow = [];
                    foreach ($row as $fieldName => $fieldValue) {
                        if ($fd = $dd->getFieldDescriptionByName($fieldName)) {
                            switch ($fd->getType()) {
                                case FieldDescription::FIELD_TYPE_DATE:
                                case FieldDescription::FIELD_TYPE_TIME:
                                case FieldDescription::FIELD_TYPE_DATETIME:
                                    if ($format = $fieldInfo->getPropertyValue('outputFormat')) {
                                        $fieldValue = E()->Utils->formatDate($fieldValue, $format,
                                            $fd->getType());
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
                                        $fieldValue = [];
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
                    if ($tmpRow) {
                        $data .= $this->prepareCSVString($tmpRow);
                    }
                }
            }

        }

        $filename = $this->getTitle() . '.csv';
        $MIMEType = 'application/csv';

        if ($encoding != 'utf-8') {
            $data = iconv('utf-8', $encoding . '//IGNORE', $data);
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
        ) {
            E()->getController()->getTransformer()->setFileName('print.xslt');
        }
        $this->prepare();
    }

    /**
     * Prepare CSV string.
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
                str_replace([$separator, $delimiter], ["''", ','], $fieldValue) .
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
        $attachmentEditorParams = [
            'origTableName' => $this->getTableName(),
            'pk' => $this->getPK(),
            'tableName' => $this->getTableName() . AttachmentManager::ATTACH_TABLE_SUFFIX,
        ];

        if (isset($sp['id'])) {
            $this->request->shiftPath(2);
            $attachmentEditorParams['linkedID'] = $sp['id'];
        } else {
            $this->request->shiftPath(1);
        }

        $this->attachmentEditor = $this->document->componentManager->createComponent(
            'attachmentEditor', 'Energine\share\components\AttachmentEditor', $attachmentEditorParams
        );
        $this->attachmentEditor->run();
    }

    /**
     * Show component: tag editor.
     */
    protected function tags() {
        $this->request->setPathOffset($this->request->getPathOffset() + 1);
        $this->tagEditor = $this->document->componentManager->createComponent('tageditor', 'Energine\share\components\TagEditor',
            ['config' => 'core/modules/share/config/TagEditorModal.component.xml']);
        $this->tagEditor->run();
    }
    /**
     * Set column name for user sorting.
     * @param string $columnName Column name.
     * @see Grid::getOrderColumn
     */
    protected function setOrderColumn($columnName) {
        $this->orderColumn = $columnName;
        if ($columnName) {
            $this->setOrder([$columnName => QAL::ASC]);
        }
    }

    /**
     * Get column name for user sorting.
     * @return string
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
     * Allowed movement:
     * - above
     * - below
     * - top
     * - bottom
     * @todo: Пофиксить перемещение в начало списка, т.к. сейчас порядковый номер может выйти меньше 0. Аналогичная ситуация с move above.
     * @throws SystemException 'ERR_NO_ORDER_COLUMN'
     */
    protected function moveTo() {
        if (!$this->getOrderColumn()) {
            //Если не задана колонка для пользовательской сортировки то на выход
            throw new SystemException('ERR_NO_ORDER_COLUMN', SystemException::ERR_DEVELOPER);
        }

        $params = $this->getStateParams();
        list($firstItem, $direction) = $params;

        $allowed_directions = ['first', 'last', 'above', 'below'];
        if (in_array($direction, $allowed_directions) && $firstItem == intval($firstItem)) {
            switch ($direction) {
                // двигаем элемент с id=$firstItem на самый верх
                case 'first':
                    $oldFirstItem = (int)$this->dbh->getScalar('SELECT MIN(' . $this->getOrderColumn() . ') FROM ' . $this->getTableName() . ' LIMIT 1');
                    if ($oldFirstItem != $firstItem) {
                        $this->dbh->modify(
                            QAL::UPDATE,
                            $this->getTableName(),
                            [$this->getOrderColumn() => $oldFirstItem - 1],
                            [$this->getPK() => $firstItem]
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
                            [$this->getOrderColumn() => $oldLastItem + 1],
                            [$this->getPK() => $firstItem]
                        );
                    }
                    break;
                // двигаем элемент выше или ниже id=$secondItem
                case 'above':
                case 'below':
                    $secondItem = (!empty($params[2])) ? $params[2] : NULL;
                    if ($secondItem == intval($secondItem) && $firstItem != $secondItem) {
                        $secondItemOrderNum = $this->dbh->getScalar(
                            'SELECT ' . $this->getOrderColumn() . ' as secondItemOrderNum ' .
                            'FROM ' . $this->getTableName() . ' ' .
                            'WHERE ' . $this->getPK() . ' = ' . $secondItem
                        );
                        $this->dbh->beginTransaction();
                        // сдвигаем все элементы выше или ниже второго id
                        $this->dbh->modify(
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
                            [$this->getOrderColumn() => (($direction == 'below') ? $secondItemOrderNum + 1 : $secondItemOrderNum - 1)],
                            [$this->getPK() => $firstItem]
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
     * @param string $direction Direction.
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
        $currentOrderNum = $this->dbh->getScalar($this->getTableName(), $this->getOrderColumn(), [$this->getPK() => $currentID]);

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
            convertDBResult($this->dbh->select($request), 'neighborID');
        if ($data) {
            $neighborID = NULL;
            $neighborOrderNum = 0;
            extract(current($data));
            $this->dbh->beginTransaction();
            $this->dbh->modify(
                QAL::UPDATE,
                $this->getTableName(),
                [$this->getOrderColumn() => $neighborOrderNum],
                [$this->getPK() => $currentID]
            );
            $this->dbh->modify(
                QAL::UPDATE,
                $this->getTableName(),
                [$this->getOrderColumn() => $currentOrderNum],
                [$this->getPK() => $neighborID]
            );
            $this->dbh->commit();
        }
        $b = new JSONCustomBuilder();
        $b->setProperties([
            'result' => true,
            'dir' => $direction
        ]);
        $this->setBuilder($b);
    }

    /**
     * Apply user filter.
     */
    protected function applyUserFilter() {
        (new Filter(FilterExpression::createFromPOST()))->apply($this);        
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
            $this->setOrder([$actionParams['sortField'] => $actionParams['sortDir']]);
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
     * @param string $tableName Table name.
     * @param bool $data Data.
     * @see DivisionEditor
     * @see ProductEditor
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

        if ($this->dbh->getTagsTablename($this->getTableName())) {
            $tm = new TagManager($this->getDataDescription(), $this->getData(), $this->getTableName());
            $tm->createFieldDescription();
            $tm->createField();
        }
    }

    /**
     * Autocomplete tag names.
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
                        $result['data'][] = [
                            'key' => $tag,
                            'value' => $tag
                        ];
                    }
                }
            }
        } catch (\Exception $e) {
            $result = [
                'result' => false,
                'data' => false,
                'errors' => []
            ];
        }

        $b->setProperties($result);
    }

    /**
     * @copydoc DBDataSet::prepare
     */
    protected function prepare() {
        parent::prepare();

        if ($this->getType() == self::COMPONENT_TYPE_LIST) {
            $this->createFilter();
        }
    }

    /**
     * Create Grid filter
     * if there is filter description in config - load it
     * else  - create filter using DataDescription info
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
                    if (in_array($fAttributes->getType(), [
                            FieldDescription::FIELD_TYPE_DATETIME,
                            FieldDescription::FIELD_TYPE_DATE,
                            FieldDescription::FIELD_TYPE_INT,
                            FieldDescription::FIELD_TYPE_SELECT,
                            FieldDescription::FIELD_TYPE_PHONE,
                            FieldDescription::FIELD_TYPE_EMAIL,
                            FieldDescription::FIELD_TYPE_STRING,
                            FieldDescription::FIELD_TYPE_TEXT,
                            FieldDescription::FIELD_TYPE_HTML_BLOCK,
                            FieldDescription::FIELD_TYPE_BOOL
                        ])
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