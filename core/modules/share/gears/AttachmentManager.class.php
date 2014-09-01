<?php 
/**
 * @file
 * AttachmentManager.
 *
 * It contains the definition to:
 * @code
class AttachmentManager;
@endcode
 *
 * @author d.pavka
 * @copyright d.pavka@gmail.com
 *
 * @version 1.0.0
 */
namespace Energine\share\gears;

/**
 * This class is designed to automate the work of attaching files.
 *
 * @code
class AttachmentManager;
@endcode
 */
class AttachmentManager extends DBWorker {
    /**
     * Attach table suffix.
     * @var string ATTACH_TABLE_SUFFIX
     */
    const ATTACH_TABLE_SUFFIX = '_uploads';
    /**
     * Name of the main table for uploads.
     * @var string ATTACH_TABLENAME
     */
    const ATTACH_TABLENAME = 'share_uploads';

    /**
     * Data.
     * @var Data $data.
     */
    private $data;
    /**
     * Activity flag.
     * It is defined by checking the existence of the table name with '_uploads' suffix.
     *
     * @var bool $isActive
     */
    private $isActive = false;
    /**
     * Table name for uploads.
     * Main table name + '_uploads' suffix.
     * @var bool $tableName
     */
    private $tableName = false;
    /**
     * Data description.
     * @var DataDescription $dataDescription
     */
    private $dataDescription;
    /**
     * Primary key in the main table.
     * @var FieldDescription $pk
     */
    private $pk;
    /**
     * Flag that shows the necessity to generate 'image' tags for OG.
     * @var bool $addOG
     */
    private $addOG;

    /**
     * Constructor.
     *
     * It checks the object's activity and fills the main properties.
     *
     * @param DataDescription $dataDescription Data description.
     * @param Data $data Data.
     * @param string $tableName Main table name.
     * @param bool $addToOG Flag that shows the necessity to generate 'image' tags for OG.
     */
    public function __construct(DataDescription $dataDescription, Data $data, $tableName, $addToOG = false) {
        parent::__construct();
        if ($this->isActive = $this->dbh->tableExists($this->tableName = $tableName . self::ATTACH_TABLE_SUFFIX)) {
            $this->dataDescription = $dataDescription;
            $this->data = $data;
            $this->addOG = $addToOG;

            foreach ($this->dataDescription as $fd) {
                if ($fd->getPropertyValue('key')) {
                    $this->pk = $fd;
                    break;
                }
            }
        }
    }

    /**
     * Create field description.
     * It is used in feeds and their derivatives.
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
     * Create field.
     * It is used in feeds and their derivatives.
     *
     * @param bool|string $mapFieldName Map field name.
     * @param bool $returnOnlyFirstAttachment Defines whether only the first attachment should be returned.
     * @param bool|array $mapValue Map values.
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
                                if (array_key_exists($old_field, $row)) {
                                    $val = $row[$old_field];
                                    unset($row[$old_field]);
                                    $row[$new_field] = $val;
                                }
                            }
                        }

                        $mapID = $row[$mapFieldName];
                        if ($returnOnlyFirstAttachment
                            && isset($imageData[$mapID])
                        ) continue;

                        if (!isset($imageData[$mapID]))
                            $imageData[$mapID] = array();

                        array_push($imageData[$mapID], $row);
                    }

                    for ($i = 0; $i < sizeof($mapValue); $i++) {
                        if (isset($imageData[$mapValue[$i]])) {
                            if($this->addOG){
                                foreach($imageData[$mapValue[$i]] as $row){
                                    E()->getOGObject()->addImage($row['file']);
                                }
                            }

                            $builder = new SimpleBuilder();
                            $localData = new Data();
                            $localData->load($imageData[$mapValue[$i]]);
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

    /**
     * Get upload ID by upload path.
     *
     * @param string $path Path
     * @return null|string
     */
    protected function getUploadIdByUploadPath($path) {
        return $this->dbh->getScalar('SELECT upl_id FROM share_uploads WHERE upl_path=%s LIMIT 1', $path);
    }
}
