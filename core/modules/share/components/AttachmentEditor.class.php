<?php
/**
 * Содержит класс AttachmentEditor
 *
 * @package energine
 * @subpackage share
 * @author andy.karpov
 * @copyright Energine 2013
 */

/**
 * Редактор связанных аттачментов
 *
 * @package energine
 * @subpackage share
 * @author andy.karpov
 */
class AttachmentEditor extends Grid {
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
            foreach($columns as $colName => $colProps) {
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
     * Переопределенный метод
     * Дополняет принимаемые компонентом параметры
     *
     * @return array
     */
    protected function defineParams() {
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
     * Переопределенный метод
     * Делает поле upl_id типа INT в форме add/edit, чтобы исключить подтягивание значений по FK
     *
     */
    protected function prepare() {

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
        }
    }

    /**
     * Переопределенный метод
     * Дополняет описание данных доп полями из таблицы share_uploads,
     * а также исключает из вывода PK основной таблицы и поле session_id
     *
     * @return DataDescription
     */
    protected function createDataDescription() {

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
     * Отключаем FK для ul_id и связки с основной таблицей
     * @return array
     */
    protected function loadDataDescription(){
        $r = parent::loadDataDescription();
        $r['upl_id']['key'] = false;
        $r[$this->getParam('pk')]['key'] = false;
        return $r;
    }

    /**
     * Переопределенный метод
     * Дополняет набор данных значениями полей upl_path, upl_name и upl_duration
     *
     * @return mixed
     */
    protected function loadData() {
        $data = parent::loadData();

        if ($this->getState() == 'getRawData' && is_array($data)) {

            $inverted = inverseDBResult($data);
            $upl_ids = $inverted['upl_id'];

            $res = $this->dbh->select(
                'share_uploads',
                array('upl_id', 'upl_path', 'upl_name', 'upl_duration'),
                array('upl_id' => $upl_ids)
            );

            if ($data) {
                foreach ($data as $i => $row) {
                    if ($res) {
                        $new_row = false;
                        foreach($res as $row2) {
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

    protected function add() {
        parent::add();

        for($i=0; $i<$this->getData()->getRowCount(); $i++) {
            $f = $this->getData()->getFieldByName($this->getParam('pk'));
            $f->setRowData($i, $this->getParam('linkedID'));

            $f = $this->getData()->getFieldByName('session_id');
            $f->setRowData($i, session_id());
        }

    }

    protected function edit() {
        parent::edit();

        for($i=0; $i<$this->getData()->getRowCount(); $i++) {
            $f = $this->getData()->getFieldByName($this->getParam('pk'));
            $f->setRowData($i, $this->getParam('linkedID'));

            $f = $this->getData()->getFieldByName('session_id');
            $f->setRowData($i, session_id());
        }
    }

    protected function savequickupload() {

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

}