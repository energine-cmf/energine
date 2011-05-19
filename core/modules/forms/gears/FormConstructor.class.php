<?php


class FormConstructor extends DBWorker
{
    const TABLE_PREFIX = 'form_';
    /**
     * @var string
     */
    private $tableName;
    /**
     * @var string
     */
    private $db;

    public function __construct($formID)
    {
        parent::__construct();
        $this->db = $this->getConfigValue('forms.database');
        $this->tableName = DBA::getFQTableName(
            $this->db . '.' . self::TABLE_PREFIX . $formID);
        $this->dbh->modifyRequest(
            'CREATE TABLE IF NOT EXISTS ' . $this->tableName .
            ' (pk_id int(10) unsigned NOT NULL AUTO_INCREMENT, PRIMARY KEY (`pk_id`)) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ');

    }

    public function getDataDescription()
    {
        $result = new DataDescription();
        $result->load(
            array(
                 'field_id' => array(
                     'nullable' => false,
                     'length' => 10,
                     'default' => '',
                     'key' => true,
                     'type' => FieldDescription::FIELD_TYPE_INT,
                     'index' => 'PRI',
                     'tableName' => 'table_name'
                 ),
                 'lang_id' => array(
                     'nullable' => false,
                     'length' => 10,
                     'default' => '',
                     'key' => true,
                     'type' => FieldDescription::FIELD_TYPE_INT,
                     'index' => 'PRI',
                     'tableName' => 'table_name',
                     'languageID' => true,
                 ),
                 'field_name' => array(
                     'nullable' => false,
                     'length' => 255,
                     'default' => '',
                     'key' => false,
                     'type' => FieldDescription::FIELD_TYPE_STRING,
                     'index' => false,
                     'tableName' => 'share_translations',
                     'isMultilanguage' => true,
                 ),
                 'field_type' => array(
                     'nullable' => false,
                     'length' => 255,
                     'default' => '',
                     'key' => false,
                     'type' => FieldDescription::FIELD_TYPE_STRING,
                     'index' => true,
                     'tableName' => 'table_name'
                 ),
                 'field_is_nullable' => array(
                     'nullable' => false,
                     'length' => 1,
                     'default' => '',
                     'key' => false,
                     'type' => FieldDescription::FIELD_TYPE_BOOL,
                     'index' => false,
                     'tableName' => 'table_name'
                 ),
            )
        );
        $f = $result->getFieldDescriptionByName('field_type');
        $f->setType(FieldDescription::FIELD_TYPE_SELECT);
        $f->loadAvailableValues(
            array(
                 array(
                     'key' => FieldDescription::FIELD_TYPE_STRING,
                     'value' => $this->translate('FIELD_TYPE_STRING')
                 ),
                 array(
                     'key' => FieldDescription::FIELD_TYPE_INT,
                     'value' => $this->translate('FIELD_TYPE_INT')
                 ),
                 array(
                     'key' => FieldDescription::FIELD_TYPE_BOOL,
                     'value' => $this->translate('FIELD_TYPE_BOOL')
                 ),
                 array(
                     'key' => FieldDescription::FIELD_TYPE_TEXT,
                     'value' => $this->translate('FIELD_TYPE_TEXT')
                 ),
                 array(
                     'key' => FieldDescription::FIELD_TYPE_SELECT,
                     'value' => $this->translate('FIELD_TYPE_SELECT')
                 ),
                 array(
                     'key' => FieldDescription::FIELD_TYPE_DATE,
                     'value' => $this->translate('FIELD_TYPE_DATE')
                 ),
                 array(
                     'key' => FieldDescription::FIELD_TYPE_DATETIME,
                     'value' => $this->translate('FIELD_TYPE_DATETIME')
                 ),
            ),
            'key',
            'value'
        );

        return $result;
    }

    public function getData($langID, $filter = null)
    {
        $result = new Data();
        if (empty($filter)) {
            $dataArray = array();
            foreach ($this->dbh->getColumnsInfo($this->tableName) as $rowName => $rowValue) {
                array_push($dataArray,
                           array(
                                'field_id' => $rowName,
                                'lang_id' => $langID,
                                'field_type' => FieldDescription::convertType($rowValue['type'], $rowName, $rowValue['length'], $rowValue),
                                'field_name' => $this->translate(
                                    'FIELD_' . $rowName, $langID),
                                'field_is_nullable' => $rowValue['nullable']
                           )
                );
            }
            $result->load($dataArray);
        }
        return $result;
    }

    public function save($data)
    {
        $fieldType = $data['table_name']['field_type'];
        $fieldIsNullable = $data['table_name']['field_is_nullable'];

        $fieldName =
                'field_' . sizeof($this->dbh->getColumnsInfo($this->tableName));
        $query = 'ALTER TABLE ' . $this->tableName . ' ADD ' . $fieldName . ' ';
        switch ($fieldType) {
            case FieldDescription::FIELD_TYPE_STRING:
                $query .= 'VARCHAR(255)';
                break;
            case FieldDescription::FIELD_TYPE_INT:
                $query .= 'INT(10)';
                break;
            case FieldDescription::FIELD_TYPE_BOOL:
                $query .= 'BOOL';
                break;
            case FieldDescription::FIELD_TYPE_TEXT:
                $query .= 'TEXT';
                break;
            case FieldDescription::FIELD_TYPE_DATE:
                $query .= 'DATE';
                break;
            case FieldDescription::FIELD_TYPE_DATETIME:
                $query .= 'DATETIME';
                break;
        }
        $query .= ' ' . ((!$fieldIsNullable) ? ' NOT NULL ' : ' NULL ');

        if (!$this->dbh->modifyRequest($query)) {

        }

    }
}
