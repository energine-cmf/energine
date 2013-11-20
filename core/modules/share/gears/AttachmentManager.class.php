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

                $langMapTableName = $this->dbh->getTranslationTablename($mapTableName);
                $columns = $this->dbh->getColumnsInfo($mapTableName);
                $prefix = '';

                foreach($columns as $cname => $col) {
                    if (isset($col['index']) && $col['index'] == 'PRI') {
                        $prefix = str_replace('_id', '', $cname);
                    }
                }

                if ($langMapTableName) {
                    $lang_columns = $this->dbh->getColumnsInfo($langMapTableName);
                    $lang_pk = false;
                    foreach($lang_columns as $cname => $col) {
                        if (isset($col['index']) && $col['index'] == 'PRI' && $cname != 'lang_id') {
                            $lang_pk = $cname;
                        }
                    }
                }

                $additional_fields = array();
                foreach($columns as $cname => $col) {
                    if ($cname != 'session_id' && (empty($col['index'])  or ($col['index'] != 'PRI' and (empty($col['key']['tableName']))))) {
                        $new_cname = str_replace($prefix . '_', '', $cname);
                        if($new_cname != 'order_num')
                            $additional_fields[$cname] = $new_cname;
                    }
                }
                if ($langMapTableName) {
                    foreach($lang_columns as $cname => $col) {
                        if (empty($col['index']) or $col['index'] != 'PRI') {
                            $new_cname = str_replace($prefix . '_', '', $cname);
                            if ($new_cname != 'name') {
                                $additional_fields[$cname] = $new_cname;
                            }
                        }
                    }
                }

                $request = 'SELECT spu.' . $mapFieldName .
                           ',spu.upl_id as id, spu.*, ' .
                           'upl_path as file, upl_name as name, TIME_FORMAT(upl_duration, "%i:%s") as duration,
                            upl_internal_type as type,upl_mime_type as mime, upl_data as data, ' .
                            'upl_is_mp4 as is_mp4, upl_is_webm as is_webm, upl_is_flv as is_flv ' .
                            (($langMapTableName && $lang_pk) ? ', spt.*' : '') .
                           'FROM '.self::ATTACH_TABLENAME.' su ' .
                           'LEFT JOIN `' . $mapTableName .
                           '` spu ON spu.upl_id = su.upl_id ' .
                           (($langMapTableName && $lang_pk) ? 'LEFT JOIN `' . $langMapTableName . '` spt ON spu.' . $lang_pk . ' = spt.' . $lang_pk . ' AND spt.lang_id = ' . E()->getDocument()->getLang() : '') .
                           ' WHERE ' . $mapFieldName . ' IN (' .
                           implode(',', $filteredMapValue) .
                           ') AND (su.upl_is_ready=1) AND (su.upl_is_active = 1)';

                // получаем имя колонки _order_num и сортируем по этому полю, если оно есть
                if ($columns) {
                    foreach($columns as $col => $colInfo) {
                        if (strpos($col, '_order_num') !== false) {
                            $request .= ' ORDER BY ' . $col;
                        }
                    }
                }

                $images = $this->dbh->selectRequest($request);

                if (is_array($images)) {
                    foreach ($images as $row) {
                        $repoPath = E()->FileRepoInfo->getRepositoryRoot($row['file']);
                        $row['secure'] = (E()->getConfigValue('repositories.ftp.' . $repoPath . '.secure', 0)) ? true : false;

                        // делаем преобразование имен из $additional_fiels (отрезаем prefix)
                        if ($additional_fields) {
                            foreach($additional_fields as $old_field => $new_field) {
                                if (isset($row[$old_field])) {
                                    $val = $row[$old_field];
                                    unset($row[$old_field]);
                                    $row[$new_field] = $val;
                                }
                            }
                        }

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
                            $builder = new SimpleBuilder();
                            $localData = new Data();
                            if (isset($imageData[$mapValue[$i]])) {
                                $localData->load($imageData[$mapValue[$i]]);
                            }

                            $dataDescription = new DataDescription();
                            $fd = new FieldDescription('id');
                            $dataDescription->addFieldDescription($fd);

                            $fd = new FieldDescription('file');
                            $fd->setType(FieldDescription::FIELD_TYPE_STRING);
                            $base = pathinfo($imageData[$mapValue[$i]][0]['file'], PATHINFO_DIRNAME) . '/' . pathinfo($imageData[$mapValue[$i]][0]['file'], PATHINFO_FILENAME);
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

                            $fd_name = new FieldDescription('name');
                            $dataDescription->addFieldDescription($fd_name);

                            $fd = new FieldDescription('secure');
                            $fd->setType(FieldDescription::FIELD_TYPE_HIDDEN);
                            $dataDescription->addFieldDescription($fd);

                            $playlist = array();
                            foreach(array('mp4', 'webm', 'flv') as $fileType){
                                if ($imageData[$mapValue[$i]][0]['is_'.$fileType] == '1') {
                                    $playlist[] = array('id' => $base . '.'.$fileType, 'type' => $fileType);
                                }
                            }

                            if ($playlist && count($playlist) > 1) {
                                $fd = new FieldDescription('playlist');
                                $fd->setType(FieldDescription::FIELD_TYPE_SELECT);
                                $fd->loadAvailableValues($playlist, 'id', 'id');
                                $dataDescription->addFieldDescription($fd);
                            }

                            // дополнительные поля из основной и языковой таблицы _uploads
                            foreach($additional_fields as $new_name) {
                                if ($new_name != 'name') {
                                    $fd = new FieldDescription($new_name);
                                    $fd->setType(FieldDescription::FIELD_TYPE_STRING);
                                    $dataDescription->addFieldDescription($fd);
                                }
                            }

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

    protected function getUploadIdByUploadPath($path) {
        return $this->dbh->getScalar('SELECT upl_id FROM share_uploads WHERE upl_path=%s LIMIT 1', $path);
    }
}
