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
        $tableName = $this->getParam('tableName');
        $origTableName = $this->getParam('origTableName');

        if ($this->getState() != 'save') {
            if ($linkedID) {
                $this->addFilterCondition(array($pk => $linkedID));
            } else {
                $this->addFilterCondition(array($pk => null, 'session_id' => session_id()));
            }
        }
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

        $f = $this->getData()->getFieldByName($this->getParam('pk'));
        $f->setRowData(0, $this->getParam('linkedID'));

        $f = $this->getData()->getFieldByName('session_id');
        $f->setRowData(0, session_id());
    }

    protected function edit() {
        parent::edit();

        $f = $this->getData()->getFieldByName($this->getParam('pk'));
        $f->setRowData(0, $this->getParam('linkedID'));

        $f = $this->getData()->getFieldByName('session_id');
        $f->setRowData(0, session_id());
    }

}