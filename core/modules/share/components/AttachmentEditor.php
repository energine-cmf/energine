<?php
/**
 * @file
 * AttachmentEditor
 *
 * It contains the definition to:
 * @code
class AttachmentEditor;
 * @endcode
 *
 * @author andy.karpov
 * @copyright Energine 2013
 *
 * @version 1.0.0
 */
namespace Energine\share\components;

use Energine\share\gears\Data;
use Energine\share\gears\DataDescription;
use Energine\share\gears\FieldDescription;
use Energine\share\gears\JSONCustomBuilder;
use Energine\share\gears\QAL;
use Energine\share\gears\SystemException;

/**
 * Attachment editor.
 *
 * @code
class AttachmentEditor;
 * @endcode
 */
class AttachmentEditor extends Grid
{
    /**
     * @copydoc Grid::__construct
     */
    public function __construct($name, $module, array $params = null)
    {
        parent::__construct($name, $module, $params);

        $linkedID = $this->getParam('linkedID');
        $pk = $this->getParam('pk');

        if ($this->getState() != 'save') {
            if ($linkedID) {
                $this->addFilterCondition(array($pk => $linkedID));
            } else {
                $this->addFilterCondition(array($pk => null, 'session_id' => session_id()));
            }
        }

        $quick_upload_path = $this->getConfigValue('repositories.quick_upload_path', 'uploads/public');
        $quick_upload_pid = $this->dbh->getScalar('SELECT upl_id FROM share_uploads WHERE upl_path=%s LIMIT 1', $quick_upload_path);
        $quick_upload_enabled = true;

        $columns = $this->dbh->getColumnsInfo($this->getTableName());
        if ($columns) {
            foreach ($columns as $colName => $colProps) {
                if ((empty($colProps['index']) or $colProps['index'] != 'PRI') and $colName != 'upl_id' and strpos($colName, 'order_num') === false and !$colProps['nullable']) {
                    $quick_upload_enabled = false;
                }
            }
        }

        if ($langTable = $this->dbh->getTranslationTablename($this->getTableName())) {
            $lang_columns = $this->dbh->getColumnsInfo($langTable);
            foreach ($lang_columns as $colName => $colProps) {
                if ((empty($colProps['index']) or $colProps['index'] != 'PRI') and $colName != 'upl_id' and strpos($colName, 'order_num') === false and !$colProps['nullable']) {
                    $quick_upload_enabled = false;
                }
            }
        }

        $this->setProperty('quickUploadPath', $quick_upload_path);
        $this->setProperty('quickUploadPid', $quick_upload_pid);
        $this->setProperty('quickUploadEnabled', $quick_upload_enabled);
    }

    /**
     * @copydoc Grid::defineParams
     */
    protected function defineParams()
    {
        return array_merge(
            parent::defineParams(),
            array(
                'linkedID' => false,
                'pk' => false,
                'origTableName' => false,
            )
        );
    }

    /**
     * @copydoc Grid::prepare
     */
    // Делает поле upl_id типа INT в форме add/edit, чтобы исключить подтягивание значений по FK
    protected function prepare()
    {
        parent::prepare();

        if (in_array($this->getState(), array('add', 'edit'))) {
            $fd = $this->getDataDescription()->getFieldDescriptionByName('upl_id');
            $fd->setType(FieldDescription::FIELD_TYPE_INT);
            if ($this->getState() == 'edit') {
                $res = $this->dbh->getScalar('share_uploads', 'upl_path', array('upl_id' => $this->getData()->getFieldByName('upl_id')->getRowData(0)));
                if ($res) {
                    $fd->setProperty('upl_path', $res);
                }
            }

            $f = $this->getData()->getFieldByName($this->getParam('pk'));
            $f->setData($this->getParam('linkedID'), true);

            $f = $this->getData()->getFieldByName('session_id');
            $f->setData(session_id(), true);

            /*for ($i = 0; $i < $this->getData()->getRowCount(); $i++) {
                $f = $this->getData()->getFieldByName($this->getParam('pk'));
                $f->setRowData($i, $this->getParam('linkedID'));

                $f = $this->getData()->getFieldByName('session_id');
                $f->setRowData($i, session_id());
            }*/
        }
    }

    /**
     * @copydoc Grid::createDataDescription
     */
    // Дополняет описание данных доп полями из таблицы share_uploads, а также исключает из вывода PK основной таблицы и поле session_id
    protected function createDataDescription()
    {
        $dd = parent::createDataDescription();

        $fd = $dd->getFieldDescriptionByName($this->getParam('pk'));
        $fd->setType(FieldDescription::FIELD_TYPE_HIDDEN);

        $fd = $dd->getFieldDescriptionByName('session_id');
        $fd->setType(FieldDescription::FIELD_TYPE_HIDDEN);

        if ($this->getState() == 'getRawData' || $this->getState() == 'main') {

            $fd = $dd->getFieldDescriptionByName('upl_id');
            $fd->setType(FieldDescription::FIELD_TYPE_HIDDEN);

            $field = new FieldDescription('upl_path');
            $field->setType(FieldDescription::FIELD_TYPE_FILE);
            $field->setProperty('title', 'FIELD_IMG_FILENAME_IMG');
            $field->setProperty('customField', true);
            $dd->addFieldDescription($field);

            $field = new FieldDescription('upl_name');
            $field->setType(FieldDescription::FIELD_TYPE_CUSTOM);
            $field->setProperty('title', 'FIELD_IMG_FILENAME');
            $field->setProperty('customField', true);
            $dd->addFieldDescription($field);

        }

        return $dd;
    }

    /**
     * @copydoc Grid::loadDataDescription
     */
    // Отключаем FK для ul_id и связки с основной таблицей
    protected function loadDataDescription()
    {
        $r = parent::loadDataDescription();
        $r['upl_id']['key'] = false;
        $r[$this->getParam('pk')]['key'] = false;
        return $r;
    }

    /**
     * @copydoc Grid::loadData
     */
    // Дополняет набор данных значениями полей upl_path, upl_name и upl_duration
    protected function loadData()
    {
        $data = parent::loadData();

        if ($this->getState() == 'getRawData' && is_array($data)) {

            $inverted = inverseDBResult($data);
            $upl_ids = $inverted['upl_id'];

            $res = $this->dbh->select(
                'share_uploads',
                array('upl_id', 'upl_path', 'upl_title as upl_name', 'upl_duration'),
                array('upl_id' => $upl_ids)
            );

            if ($data) {
                foreach ($data as $i => $row) {
                    if ($res) {
                        $new_row = false;
                        foreach ($res as $row2) {
                            if ($row2['upl_id'] == $row['upl_id']) {
                                $new_row = $row2;
                            }
                        }
                        if ($new_row) {
                            $data[$i]['upl_path'] = $new_row['upl_path'];
                            $data[$i]['upl_name'] = $new_row['upl_name'];
                        }
                    }
                }
            }
        }

        return $data;
    }

    /**
     * Save quick upload.
     *
     * @throws \Exception
     * @throws SystemException
     */
    protected function savequickupload()
    {
        $transactionStarted = $this->dbh->beginTransaction();
        try {
            $upl_id = (isset($_POST['upl_id'])) ? intval($_POST['upl_id']) : false;
            $result = $this->dbh->modify(
                QAL::INSERT,
                $this->getTableName(),
                array(
                    $this->getParam('pk') => $this->getParam('linkedID'),
                    'session_id' => session_id(),
                    'upl_id' => $upl_id
                )
            );

            if ($result && $langTable = $this->dbh->getTranslationTablename($this->getTableName())) {
                // todo: вставка языковых данных
                $lang_columns = $this->dbh->getColumnsInfo($langTable);
                $fields = array(
                    $this->getPK() => $result
                );
                foreach ($lang_columns as $colName => $colProps) {
                    if (empty($colProps['index']) or $colProps['index'] != 'PRI') {
                        $fields[$colName] = '';
                    }
                }
                $langs = E()->getLanguage()->getLanguages();
                foreach ($langs as $lang_id => $lang_data) {
                    $this->dbh->modify(
                        QAL::INSERT,
                        $langTable,
                        array_merge($fields, array('lang_id' => $lang_id))
                    );
                }
            }

            $transactionStarted = !($this->dbh->commit());

            $b = new JSONCustomBuilder();
            $b->setProperties(array(
                'data' => (is_int($result)) ? $result : false,
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
     * @copydoc Grid::saveData
     */
    //Правильно сохраняет порядок в order_num полях
    protected function saveData()
    {
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

        //Если у нас режим вставки и определена колонка для порядка следования,
        // изменяем порядок следования
        if (($orderColumn = $this->getOrderColumn()) &&
            ($mode == self::COMPONENT_TYPE_FORM_ADD)
        ) {

            $linkedID = $this->getParam('linkedID');
            $pk = $this->getParam('pk');

            if ($linkedID) {
                $new_order_num = $this->dbh->getScalar(
                    'SELECT max(' . $orderColumn . ') as max_order_num
                    FROM ' . $this->getTableName() . ' WHERE `' . $pk . '` = %s',
                    $linkedID
                );
            } else {
                $new_order_num = $this->dbh->getScalar(
                    'SELECT max(' . $orderColumn . ') as max_order_num
                    FROM ' . $this->getTableName() . ' WHERE `' . $pk . '` IS NULL AND session_id = %s ',
                    session_id()
                );
            }

            $new_order_num = (!$new_order_num) ? 1 : $new_order_num + 1;

            $this->addFilterCondition(array($this->getPK() . '=' . $result));
            $request =
                'UPDATE ' . $this->getTableName() . ' SET ' . $orderColumn . ' = %s ' .
                $this->dbh->buildWhereCondition($this->getFilter());
            $this->dbh->modifyRequest($request, $new_order_num);

        }

        return $result;
    }

}