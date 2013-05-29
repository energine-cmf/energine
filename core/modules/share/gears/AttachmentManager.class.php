<?php 
/**
 * Содержит класс AttachmentManager
 *
 * @package energine
 * @subpackage kernel
 * @author d.pavka
 * @copyright d.pavka@gmail.com
 */

/**
 * Класс предназначен для автоматизации работы с присоединенными файлами
 *
 * @package energine
 * @subpackage kernel
 * @author d.pavka@gmail.com
 */
class AttachmentManager extends DBWorker {
    const ATTACH_TABLE_SUFFIX = '_uploads';
    /**
     * Имя базовой таблицы аплоадсов
     */
    const ATTACH_TABLENAME = 'share_uploads';
    /**
     * @var Data
     */
    private $data;
    /**
     * Флаг активности
     * Определяется путем проверки наличия таблицы с суффиксом _uploads
     *
     * @var bool
     */
    private $isActive = false;
    /**
     * Имя таблицы аплоадсов
     * Имя основной таблицы + суффикс _uploads
     * @var bool
     */
    private $tableName = false;
    /**
     * @var DataDescription
     */
    private $dataDescription;
    /**
     * Первичный ключ в основной таблице
     *
     * @var FieldDescription
     */
    private $pk;

    /**
     * Проверяет активность объекта
     * заполняет основные свойства
     *
     * @param DataDescription $dataDescription
     * @param Data $data
     * @param $tableName имя основной таблицы
     */
    public function __construct(DataDescription $dataDescription, Data $data, $tableName) {
        parent::__construct();
        if ($this->isActive = $this->dbh->tableExists($this->tableName = $tableName . self::ATTACH_TABLE_SUFFIX)) {
            $this->dataDescription = $dataDescription;
            $this->data = $data;

            foreach ($this->dataDescription as $fd) {
                if ($fd->getPropertyValue('key')) {
                    $this->pk = $fd;
                    break;
                }
            }
        }
    }

    /**
     * Создает описание поля
     * используется в фидах и их производных
     *
     * @return void
     * @access public
     */
    public function createFieldDescription() {
        if ($this->isActive) {
            if (!($f = $this->dataDescription->getFieldDescriptionByName('attachments'))) {
                $f = new FieldDescription('attachments');
                $this->dataDescription->addFieldDescription($f);
            }
            $f->setType(FieldDescription::FIELD_TYPE_CUSTOM);
        }
    }


    /**
     * Возвращает поле
     * Используется в фидах и их производных
     *
     * @param $mapFieldName
     * @param bool $returnOnlyFirstAttachment
     * @param bool $mapValue
     * @return void
     */
    public function createField($mapFieldName = false, $returnOnlyFirstAttachment = false, $mapValue = false) {
        if ($this->isActive && !$this->data->isEmpty()) {
            if(!$mapFieldName){
                $mapFieldName = $this->pk->getName();
            }
            if (!$mapValue) {
                if (!$f = $this->data->getFieldByName($mapFieldName)) return;
                $mapValue = $f->getData();
            }


            $mapTableName = $this->tableName;

            //@todo в принципе имя филда можеть быть вычислено через ColumnInfo
            $f = new Field('attachments');
            $this->data->addField($f);

            if (!is_array($mapValue)) {
                $mapValue = array($mapValue);
            }

            if ($filteredMapValue = array_filter(array_values($mapValue))) {
                $request = 'SELECT spu.' . $mapFieldName .
                           ',spu.upl_id as id, ' .
                           'upl_path as file, upl_name as name, TIME_FORMAT(upl_duration, "%i:%s") as duration, upl_internal_type as type,upl_mime_type as mime, upl_data as data FROM '.self::ATTACH_TABLENAME.' su ' .
                           'LEFT JOIN `' . $mapTableName .
                           '` spu ON spu.upl_id = su.upl_id ' .
                           //'WHERE '.$mapFieldName.' IN ('.implode(',', array_keys(array_flip($mapValue))).') '.
                           'WHERE ' . $mapFieldName . ' IN (' .
                           implode(',', $filteredMapValue) .
                           ') AND (su.upl_is_ready=1) AND (su.upl_is_active = 1)' .
                           'ORDER by upl_order_num';

                $images = $this->dbh->selectRequest($request);

                if (is_array($images)) {
                    foreach ($images as $row) {
                        $mapID = $row[$mapFieldName];
                        if ($returnOnlyFirstAttachment &&
                            isset($imageData[$mapID])
                        ) continue;

                        if (!isset($imageData[$mapID]))
                            $imageData[$mapID] = array();

                        array_push($imageData[$mapID], $row);
                    }

                    for ($i = 0; $i < sizeof($mapValue); $i++) {
                        if (isset($imageData[$mapValue[$i]])) {
                            $builder = new Builder();
                            $localData = new Data();
                            if (isset($imageData[$mapValue[$i]]))
                                $localData->load($imageData[$mapValue[$i]]);

                            $dataDescription = new DataDescription();
                            $fd = new FieldDescription('id');
                            $dataDescription->addFieldDescription($fd);

                            $fd = new FieldDescription('file');
                            $fd->setType(FieldDescription::FIELD_TYPE_STRING);
                            $dataDescription->addFieldDescription($fd);

                            $fd = new FieldDescription('type');
                            $fd->setType(FieldDescription::FIELD_TYPE_STRING);
                            $dataDescription->addFieldDescription($fd);

                            $fd = new FieldDescription('duration');
                            $fd->setType(FieldDescription::FIELD_TYPE_STRING);
                            $dataDescription->addFieldDescription($fd);

                            $fd = new FieldDescription('mime');
                            $fd->setType(FieldDescription::FIELD_TYPE_STRING);
                            $dataDescription->addFieldDescription($fd);

                            $fd = new FieldDescription('data');
                            $fd->setType(FieldDescription::FIELD_TYPE_STRING);
                            $dataDescription->addFieldDescription($fd);

                            $fd = new FieldDescription('name');
                            $dataDescription->addFieldDescription($fd);
                            $builder->setData($localData);
                            $builder->setDataDescription($dataDescription);

                            $builder->build();

                            $f->setRowData($i, $builder->getResult());
                        }
                    }
                }
            }
        }
    }

    /**
     * Сохранение данных
     * Используется в гридах
     *
     * @param $id идентфикатор
     * @return void
     */
    public function save($id) {
        if ($this->isActive) {
            //Удаляем предыдущие записи из таблицы связей с дополнительными файлам
            $this->dbh->modify(QAL::DELETE, $this->tableName, null, array($this->pk->getName() => $id));
            if (isset($_POST['uploads']['upl_id'])) {
                foreach ($_POST['uploads']['upl_id'] as $uplOrderNum => $uplID) {
                    $this->dbh->modify(QAL::INSERT, $this->tableName, array('upl_order_num' => ($uplOrderNum + 1), $this->pk->getName() => $id, 'upl_id' => $uplID));
                }
            }
        }
    }

    /**
     * Создание поля(вкладки) для работы с приаттаченными полями
     * Используется в гридах
     *
     * @param bool $data
     * @return void
     */
    public function createAttachmentTab($data = true) {
        if ($this->isActive) {
            $tableName = $this->tableName;
            $field = new FieldDescription('attached_files');
            $field->setType(FieldDescription::FIELD_TYPE_CUSTOM);
            $field->setProperty('tabName', $this->translate('TAB_ATTACHED_FILES'));
            $field->setProperty('tableName', $tableName);

            $this->dataDescription->addFieldDescription($field);

            //Добавляем поле с дополнительными файлами
            $field = new Field('attached_files');
            if (!$data && !$this->data->isEmpty()) {
                $data = $this->data->getFieldByName($this->pk->getName())->getRowData(0);
                $request = 'SELECT files.upl_id, upl_path, upl_name, upl_title, upl_internal_type, upl_data
                      FROM `' . $this->tableName . '` s2f
                    LEFT JOIN `' . self::ATTACH_TABLENAME . '` files ON s2f.upl_id=files.upl_id
                    WHERE ' . $this->pk->getName() . ' = %s  
                    ORDER BY upl_order_num';
                //AND upl_is_ready=1
                $data = $this->dbh->select($request, $data);
            }

            $attachedFilesData = $this->buildAttachmentTab($data);
            for ($i = 0; $i < count(E()->getLanguage()->getLanguages()); $i++) {
                $field->addRowData($attachedFilesData);
            }

            $this->data->addField($field);
        }
    }

    /**
     * Построение данных для поля
     *
     * @param $data
     * @return DOMNode
     */
    private function buildAttachmentTab($data) {
        $builder = new Builder();
        $dd = new DataDescription();
        $f = new FieldDescription('upl_id');
        $dd->addFieldDescription($f);
        /*
        $f = new FieldDescription('upl_is_main');
        $f->setType(FieldDescription::FIELD_TYPE_BOOL);
        $dd->addFieldDescription($f);
*/
        $f = new FieldDescription('upl_title');
        $dd->addFieldDescription($f);

        $f = new FieldDescription('upl_name');
        $f->setType(FieldDescription::FIELD_TYPE_HIDDEN);
        $dd->addFieldDescription($f);

        $f = new FieldDescription('upl_internal_type');
        $f->setType(FieldDescription::FIELD_TYPE_HIDDEN);
        $dd->addFieldDescription($f);

        $f = new FieldDescription('upl_path');
        $f->setType(FieldDescription::FIELD_TYPE_STRING);
        //$f->setProperty('title', $this->translate('FIELD_UPL_FILE'));
        $f->setProperty('title', 'FIELD_UPL_FILE');
        $dd->addFieldDescription($f);

        $d = new Data();

        if (is_array($data)) {
            $d->load($data);
        }

        $builder->setData($d);
        $builder->setDataDescription($dd);

        $builder->build();

        return $builder->getResult();
    }

}
