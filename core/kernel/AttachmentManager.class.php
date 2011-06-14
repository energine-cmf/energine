<?php 
/**
 * Содержит класс AttachmentManager
 *
 * @package energine
 * @subpackage share
 * @author d.pavka
 * @copyright d.pavka@gmail.com
 */

/**
 * Класс предназначен для автоматизации работы с присоединенными файлами
 *
 * @package energine
 * @subpackage share
 * @author d.pavka@gmail.com
 */
class AttachmentManager extends DBWorker {
    /**
     *
     */
    const ATTACH_TABLENAME = 'share_uploads';

    /**
     * Связанная таблица
     *
     * @access private
     * @var string
     */
    private $mapTableName;
    /**
     * Значения
     *
     * @access private
     * @var array
     */
    private $mapValue;

    /**
     * Конструктор класса
     *
     * @access public
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Создает описание поля
     *
     * @return FieldDescription
     * @access public
     */
    public function createFieldDescription() {
        $f = new FieldDescription('attachments');
        $f->setType(FieldDescription::FIELD_TYPE_CUSTOM);

        return $f;
    }

    /**
     * Возвращает поле
     *
     * @param mixed значение поля
     * @param string имя поля
     * @param string имя таблицы
     * @return Field
     * @access public
     */
    public function createField($mapValue, $mapFieldName, $mapTableName, $returnOnlyFirstAttachment = false) {
        //@todo в принципе имя филда можеть быть вычислено через ColumnInfo
        $f = new Field('attachments');
        if (!is_array($mapValue)) {
            $mapValue = array($mapValue);
        }

        if ($filteredMapValue = array_filter(array_values($mapValue))) {
            $request = 'SELECT spu.' . $mapFieldName .
                    ',spu.upl_id as id, '.
                    'upl_path as file, upl_name as name FROM share_uploads su ' .
                    'LEFT JOIN `' . $mapTableName .
                    '` spu ON spu.upl_id = su.upl_id ' .
                    //'WHERE '.$mapFieldName.' IN ('.implode(',', array_keys(array_flip($mapValue))).') '.
                    'WHERE ' . $mapFieldName . ' IN (' .
                    implode(',', $filteredMapValue) .
                    ') AND su.upl_is_ready=1 ' .
                    'ORDER by upl_order_num';
            $images = $this->dbh->selectRequest($request);

            if (is_array($images)) {
                foreach ($images as $row) {
                    $mapID = $row[$mapFieldName];
                    if ($returnOnlyFirstAttachment &&
                            isset($imageData[$mapID])) continue;

                    if (!isset($imageData[$mapID]))
                        $imageData[$mapID] = array();

                    array_push($imageData[$mapID], $row);
                }
                
                for ($i = 0; $i < sizeof($mapValue); $i++) {
                    if (isset($imageData[$mapValue[$i]])) {
                        $builder = new Builder();
                        $localData = new Data();
                        if(isset($imageData[$mapValue[$i]]))
                            $localData->load($imageData[$mapValue[$i]]);

                        $dataDescription = new DataDescription();
                        $fd = new FieldDescription('id');
                        $dataDescription->addFieldDescription($fd);

                        $fd = new FieldDescription('file');
                        $fd->setType(FieldDescription::FIELD_TYPE_MEDIA);
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
        return $f;
    }

}
