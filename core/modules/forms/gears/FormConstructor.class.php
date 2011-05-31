<?php
/**
 * Хреново получилось
 * так и хочется все переписать
 *
 *
 */
/**
 * @throws SystemException
 *
 */
class FormConstructor extends DBWorker
{
    /**
     * 
     */
    const TABLE_PREFIX = 'form_';
    /**
     * @var string
     */
    private $tableName;
    /**
     * @var string
     */
    private $fDBName;
    /**
     * @param  $formID
     */
    public function __construct($formID)
    {
        parent::__construct();
        $this->fDBName = $this->getConfigValue('forms.database');
        $this->tableName = DBA::getFQTableName(
            $this->fDBName . '.' . self::TABLE_PREFIX . $formID);
        $this->dbh->modifyRequest(
            'CREATE TABLE IF NOT EXISTS ' . $this->tableName .
            ' (pk_id int(10) unsigned NOT NULL AUTO_INCREMENT, PRIMARY KEY (`pk_id`)) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ');

    }
    /**
     * @return DataDescription
     */
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
                     'key' => false,
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
                     'tableName' => 'share_lang_tags_translation',
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
                     'key' => FieldDescription::FIELD_TYPE_MULTI,
                     'value' => $this->translate('FIELD_TYPE_MULTI')
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
    /**
     * @param  $langID
     * @param null $filter
     * @return Data
     */
    public function getData($langID, $filter = null)
    {
        $result = new Data();
        if (empty($filter)) {
            $dataArray = array();
            $i = 0;
            foreach ($this->dbh->getColumnsInfo($this->tableName) as $rowName => $rowValue) {
                array_push($dataArray,
                           array(
                                'field_id' => ++$i,
                                'lang_id' => $langID,
                                'field_type' => FieldDescription::convertType($rowValue['type'], $rowName, $rowValue['length'], $rowValue),
                                'field_name' => $this->translate($this->getFieldLTag($rowName), $langID),
                                'field_is_nullable' => $rowValue['nullable']
                           )
                );
            }
            $result->load($dataArray);
        }
        return $result;
    }
    /**
     * @param  $data
     * @return void
     */
    public function save($data)
    {
        $fieldType = $data['table_name']['field_type'];
        $fieldIsNullable = $data['table_name']['field_is_nullable'];
        $fieldIndex = sizeof($cols = array_keys($this->dbh->getColumnsInfo($this->tableName)));
        while(in_array($fieldName = 'field_' . $fieldIndex, $cols)){
            $fieldIndex ++;
        }
        $query = 'ALTER TABLE ' . $this->tableName . ' ADD ' . $fieldName . ' ';
        $query .= self::getFDAsSQLString($fieldType);
        $query .= ' ' . ((!$fieldIsNullable) ? ' NOT NULL ' : ' NULL ');
        $this->dbh->beginTransaction();
        if ($this->dbh->modifyRequest($query)) {
            $ltagID = $this->dbh->modify(QAL::INSERT, 'share_lang_tags', array('ltag_name' => $this->deleteFieldLTag($fieldName)));
            
            foreach($_POST['share_lang_tags_translation'] as $langID=>$value){
                $this->dbh->modify(QAL::INSERT, 'share_lang_tags_translation', array('ltag_value_rtf' => $value['field_name'], 'ltag_id' => $ltagID, 'lang_id' => $langID));

            }
            if($fieldType == FieldDescription::FIELD_TYPE_SELECT){
                $query = array();
                $query[] = 'ALTER TABLE '.$this->tableName.' ADD INDEX ( '.$fieldName.' ) ';


                //create foreign key table
                $query[] = 'CREATE TABLE IF NOT EXISTS ' . ($fkTableName =
                        DBA::getFQTableName(
                            $this->tableName . '_' . $fieldName)).'( fk_id int(11) unsigned NOT NULL AUTO_INCREMENT, fk_order_num int(10) UNSIGNED  DEFAULT \'1\', PRIMARY KEY (`fk_id`), KEY `fk_order_num`(`fk_order_num`)) ENGINE=InnoDB  DEFAULT CHARSET=utf8';

                //add fk info
                $query[] = 'ALTER TABLE '.$this->tableName.' ADD FOREIGN KEY ( '.$fieldName.' ) REFERENCES '.$fkTableName.' (fk_id) ON DELETE CASCADE ON UPDATE CASCADE ;';


                $query[] = 'CREATE TABLE IF NOT EXISTS ' . ($langTableName =
                        DBA::getFQTableName(
                            $fkTableName.'_translation')).'( fk_id int(11) unsigned NOT NULL , lang_id int(11) UNSIGNED  NOT NULL, fk_name VARCHAR(255) NOT NULL, PRIMARY KEY (`fk_id`, `lang_id`), KEY `lang_id` (`lang_id`)) ENGINE=InnoDB  DEFAULT CHARSET=utf8';
                //add fk info
                $query[] = 'ALTER TABLE '.$langTableName.' ADD FOREIGN KEY (`lang_id`) REFERENCES '.$this->getConfigValue('database.master.db').'.`share_languages` (`lang_id`) ON DELETE CASCADE ON UPDATE CASCADE, ADD FOREIGN KEY ( fk_id ) REFERENCES '.$fkTableName.' (fk_id) ON DELETE CASCADE ON UPDATE CASCADE';
                
                foreach($query as $request)
                    $this->dbh->modifyRequest($request);

            }
        }
        $this->dbh->commit();
    }
    /**
     * @throws SystemException  
     * @param  $fieldIndex
     * @return void
     */
    public function delete($fieldName){
        $this->dbh->beginTransaction();
        $this->deleteFieldLTag($fieldName);
        $query = 'ALTER TABLE '.$this->tableName.' DROP '.$fieldName;
        $this->dbh->modifyRequest($query);
        $this->dbh->commit();
    }
    /**
     * @param  $fieldName
     * @return string
     */
    private function getFieldLTag($fieldName){
        list(,$tblName) = DBA::getFQTableName($this->tableName, true);
        return 'FIELD_'.$tblName.'_'.$fieldName;
    }
    /**
     * @param  $fieldName
     * @return string
     */
    private function deleteFieldLTag($fieldName){
        $ltagName = $this->getFieldLTag($fieldName);
        $this->dbh->modifyRequest('DELETE FROM share_lang_tags WHERE ltag_name=%s', $ltagName);
        return $ltagName;
    }

    public function changeOrder($direction, $fieldIndex){
        $fieldIndex --;
        $cols = array_keys($colsInfo = $this->dbh->getColumnsInfo($this->tableName));
        $srcField = $cols[$fieldIndex];
        $destFieldIndex = $fieldIndex +(($direction == Grid::DIR_UP)?-2:1);
        if(($destFieldIndex <= 0) || ($destFieldIndex==sizeof($cols))){
    	    return;
        }

        $destColField = $cols[$destFieldIndex];
        $query = 'ALTER TABLE '.$this->tableName.' MODIFY '.$srcField.' '.
         self::getFDAsSQLString(FieldDescription::convertType($colsInfo[$srcField]['type'], $srcField, $colsInfo[$srcField]['length'], $colsInfo[$srcField])).
        ' AFTER '.$destColField;
        $this->dbh->modifyRequest($query);
    }

    private static function getFDAsSQLString($fieldType){

        switch ($fieldType) {
            case FieldDescription::FIELD_TYPE_INT:
            case FieldDescription::FIELD_TYPE_SELECT:
                $result = 'INT(11) UNSIGNED';
                break;
            case FieldDescription::FIELD_TYPE_BOOL:
                $result = 'BOOL';
                break;
            case FieldDescription::FIELD_TYPE_TEXT:
                $result = 'TEXT';
                break;
            case FieldDescription::FIELD_TYPE_DATE:
                $result = 'DATE';
                break;
            case FieldDescription::FIELD_TYPE_DATETIME:
                $result = 'DATETIME';
                break;
            case FieldDescription::FIELD_TYPE_STRING:
            default:
                $result = 'VARCHAR(255)';
        }
        return $result;
    }
}
