<?php
/**
 * @file
 * FormConstructor
 *
 * It contains the definition to:
 * @code
class FormConstructor;
 * @endcode
 *
 * Version 1.0.0
 */
//todo Хреново получилось, так и хочется все переписать
namespace Energine\forms\gears;

use Energine\share\gears\DBWorker, Energine\share\gears\DBA, Energine\share\gears\DataDescription, Energine\share\gears\FieldDescription, Energine\share\gears\Data, Energine\share\components\Grid, Energine\share\gears\Object, Energine\share\gears\QAL;

/**
 * Form constructor.
 *
 * @code
class FormConstructor;
 * @endcode
 */
class FormConstructor extends DBWorker {
    /**
     * Table name prefix.
     */
    const TABLE_PREFIX = 'form_';
    /**
     * Table name.
     * @var string $tableName
     */
    private $tableName;
    /**
     * Database name.
     * @var string $fDBName
     */
    private $fDBName;

    /**
     * @param int|string $formID Form ID.
     */
    public function __construct($formID) {
        parent::__construct();
        $this->fDBName = FormConstructor::getDatabase();
        $this->tableName = DBA::getFQTableName(
            $this->fDBName . '.' . self::TABLE_PREFIX . $formID);
        $this->dbh->modifyRequest(
            'CREATE TABLE IF NOT EXISTS ' . $this->tableName .
            ' (pk_id int(10) unsigned NOT NULL AUTO_INCREMENT,form_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (`pk_id`), INDEX(form_date)) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ');

    }

    /**
     * Get data description.
     *
     * @return DataDescription
     */
    public function getDataDescription() {
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
                    'type' => FieldDescription::FIELD_TYPE_HIDDEN,
                    'index' => true,
                    'tableName' => 'table_name'
                ),
                'field_type_real' => array(
                    'nullable' => false,
                    'length' => 255,
                    'default' => '',
                    'key' => false,
                    'type' => FieldDescription::FIELD_TYPE_STRING,
                    'index' => true
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
                    'key' => FieldDescription::FIELD_TYPE_EMAIL,
                    'value' => $this->translate('FIELD_TYPE_EMAIL')
                ),
                array(
                    'key' => FieldDescription::FIELD_TYPE_PHONE,
                    'value' => $this->translate('FIELD_TYPE_PHONE')
                ),
                /*array(
                    'key' => FieldDescription::FIELD_TYPE_INT,
                    'value' => $this->translate('FIELD_TYPE_INT')
                ),*/
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
                array(
                    'key' => FieldDescription::FIELD_TYPE_FILE,
                    'value' => $this->translate('FIELD_TYPE_FILE')
                ),
                array(
                    'key' => FieldDescription::FIELD_TYPE_INFO,
                    'value' => $this->translate('FIELD_TYPE_INFO')
                ),
            ),
            'key',
            'value'
        );

        return $result;
    }

    /**
     * Get data.
     *
     * @param string|int $langID Language ID.
     * @param mixed $filter filter.
     * @return Data
     */
    public function getData($langID, $filter = null) {
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
                        'field_type_real' => FieldDescription::convertType($rowValue['type'], $rowName, $rowValue['length'], $rowValue),
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
     * Save.
     *
     * @param array $data Data.
     */
    public function save($data) {
        $fieldType = $data['table_name']['field_type'];
        $fieldIsNullable = $data['table_name']['field_is_nullable'];
        $fieldIndex = sizeof(
            $cols = array_keys($this->dbh->getColumnsInfo($this->tableName)));
        list(, $tblName) = DBA::getFQTableName($this->tableName, true);
        $fieldSuffix = '';
        if ($fieldType == FieldDescription::FIELD_TYPE_MULTI) {
            $fieldSuffix = '_multi';
            $fieldIsNullable = true;
        } elseif ($fieldType == FieldDescription::FIELD_TYPE_FILE) {
            $fieldSuffix = '_file';
        } elseif ($fieldType == FieldDescription::FIELD_TYPE_INFO) {
            $fieldSuffix = '_info';
            $fieldIsNullable = true;
        } elseif ($fieldType == FieldDescription::FIELD_TYPE_PHONE) {
            $fieldSuffix = '_phone';
        } elseif ($fieldType == FieldDescription::FIELD_TYPE_EMAIL) {
            $fieldSuffix = '_email';
        }

        while (in_array(
            $fieldName = $tblName . '_field_' . $fieldIndex . $fieldSuffix, $cols)) {
            $fieldIndex++;
        }


        $query = 'ALTER TABLE ' . $this->tableName . ' ADD ' . $fieldName . ' ';
        $query .= self::getFDAsSQLString($fieldType);
        $query .= ' ' . ((!$fieldIsNullable) ? ' NOT NULL ' : ' NULL ');

        $this->dbh->beginTransaction();

        if ($this->dbh->modifyRequest($query)) {
            $ltagID =
                $this->dbh->modify(QAL::INSERT, 'share_lang_tags', array('ltag_name' => $this->deleteFieldLTag($fieldName)));

            foreach ($_POST['share_lang_tags_translation'] as $langID => $value) {
                $this->dbh->modify(QAL::INSERT, 'share_lang_tags_translation', array('ltag_value_rtf' => $value['field_name'], 'ltag_id' => $ltagID, 'lang_id' => $langID));

            }
            if ($fieldType == FieldDescription::FIELD_TYPE_SELECT) {
                $this->createSelectField($fieldName);
            } elseif ($fieldType == FieldDescription::FIELD_TYPE_MULTI) {
                $this->createMultiField($fieldName);
            }
        }
        $this->dbh->commit();
    }

    /**
     * Create multi field.
     *
     * @param string $fieldName Field name.
     */
    private function createMultiField($fieldName) {
        $query = array();
        //Добавляем индекс
        $query[] = 'ALTER TABLE ' . $this->tableName . ' ADD INDEX ( ' .
            $fieldName . ' ) ';


        //Создаем таблицу связку
        $query[] = 'CREATE TABLE IF NOT EXISTS ' . ($fkTableName =
                DBA::getFQTableName(
                    $this->fDBName . '.' . $fieldName)) .
            '( pk_id int(11) unsigned NOT NULL , fk_id int(11) UNSIGNED  , PRIMARY KEY (`pk_id`, `fk_id`), KEY `fk_id`(`fk_id`)) ENGINE=InnoDB  DEFAULT CHARSET=utf8';

        //Создаем таблицу с значениями
        $query[] = 'CREATE TABLE IF NOT EXISTS ' . ($fkValuesFQTableName =
                DBA::getFQTableName(
                    $this->fDBName . '.' . ($fkValuesTableName = $fieldName . '_values'))) .
            '( fk_id int(11) unsigned NOT NULL AUTO_INCREMENT, fk_order_num int(10) UNSIGNED  DEFAULT \'1\', PRIMARY KEY (`fk_id`), KEY `fk_order_num`(`fk_order_num`)) ENGINE=InnoDB  DEFAULT CHARSET=utf8';

        //Добавляем фейковую связку в основную таблицу
        $query[] = 'ALTER TABLE ' . $this->tableName .
            ' ADD FOREIGN KEY ( ' . $fieldName .
            ' ) REFERENCES ' . $fkTableName .
            ' (pk_id) ON DELETE NO ACTION ON UPDATE NO ACTION ;';

        //СВязываем таблицу-связку с основной таблицей
        $query[] = 'ALTER TABLE ' . $fkTableName .
            ' ADD FOREIGN KEY ( pk_id ) REFERENCES ' . $this->tableName .
            ' (pk_id) ON DELETE CASCADE ON UPDATE CASCADE ;';

        //СВязываем таблицу-связку с таблицей значений
        $query[] = 'ALTER TABLE ' . $fkTableName .
            ' ADD FOREIGN KEY (fk_id) REFERENCES ' . $fkValuesFQTableName .
            ' (fk_id) ON DELETE CASCADE ON UPDATE CASCADE ;';

        //Создаем таблицу с переводами для значений
        $query[] = 'CREATE TABLE IF NOT EXISTS ' . ($langTableName =
                DBA::getFQTableName(
                    $this->fDBName . '.' . $fkValuesTableName . '_translation')) .
            '( fk_id int(11) unsigned NOT NULL , lang_id int(11) UNSIGNED  NOT NULL, fk_name VARCHAR(255) NOT NULL, PRIMARY KEY (`fk_id`, `lang_id`), KEY `lang_id` (`lang_id`)) ENGINE=InnoDB  DEFAULT CHARSET=utf8';

        //add fk info
        $query[] = 'ALTER TABLE ' . $langTableName .
            ' ADD FOREIGN KEY (`lang_id`) REFERENCES ' .
            $this->getConfigValue('database.db') .
            '.`share_languages` (`lang_id`) ON DELETE CASCADE ON UPDATE CASCADE, ADD FOREIGN KEY ( fk_id ) REFERENCES ' .
            $fkValuesFQTableName .
            ' (fk_id) ON DELETE CASCADE ON UPDATE CASCADE';

        foreach ($query as $request)
            $this->dbh->modifyRequest($request);
    }

    /**
     * Create "select" field.
     *
     * @param string $fieldName Field name.
     */
    private function createSelectField($fieldName) {
        $query = array();
        $query[] = 'SET FOREIGN_KEY_CHECKS=0;';

        $query[] = 'ALTER TABLE ' . $this->tableName . ' ADD INDEX ( ' .
            $fieldName . ' ) ';


        //create foreign key table
        $query[] = 'CREATE TABLE IF NOT EXISTS ' . ($fkTableName =
                DBA::getFQTableName(
                    $this->fDBName . '.' . $fieldName)) .
            '( fk_id int(11) unsigned NOT NULL AUTO_INCREMENT, fk_order_num int(10) UNSIGNED  DEFAULT \'1\', PRIMARY KEY (`fk_id`), KEY `fk_order_num`(`fk_order_num`)) ENGINE=InnoDB  DEFAULT CHARSET=utf8';

        //add fk info
        $query[] = 'ALTER TABLE ' . $this->tableName .
            ' ADD FOREIGN KEY ( ' . $fieldName .
            ' ) REFERENCES ' . $fkTableName .
            ' (fk_id) ON DELETE CASCADE ON UPDATE CASCADE ;';


        $query[] = 'CREATE TABLE IF NOT EXISTS ' . ($langTableName =
                DBA::getFQTableName(
                    $this->fDBName . '.' . $fieldName . '_translation')) .
            '( fk_id int(11) unsigned NOT NULL , lang_id int(11) UNSIGNED  NOT NULL, fk_name VARCHAR(255) NOT NULL, PRIMARY KEY (`fk_id`, `lang_id`), KEY `lang_id` (`lang_id`)) ENGINE=InnoDB  DEFAULT CHARSET=utf8';

        //add fk info
        $query[] = 'ALTER TABLE ' . $langTableName .
            ' ADD FOREIGN KEY (`lang_id`) REFERENCES ' .
            $this->getConfigValue('database.db') .
            '.`share_languages` (`lang_id`) ON DELETE CASCADE ON UPDATE CASCADE, ADD FOREIGN KEY ( fk_id ) REFERENCES ' .
            $fkTableName .
            ' (fk_id) ON DELETE CASCADE ON UPDATE CASCADE';
        //stop($query);
        foreach ($query as $request)
            $this->dbh->modifyRequest($request);
    }

    /**
     * Delete field.
     *
     * @param string $fieldName Field name.
     */
    public function delete($fieldName) {
        $info = $this->dbh->getColumnsInfo($this->tableName);
        if (isset($info[$fieldName])) {
            $queries = array();
            $this->dbh->beginTransaction();
            $this->deleteFieldLTag($fieldName);

            $info = $info[$fieldName];
            //Если есть инфа о связях 
            //значит связи нужно подчистить перед тем как удалять само поле
            if (isset($info['key']) && is_array($info['key'])) {
                //в любом случае удаляем внешний ключ
                $queries[] = 'ALTER TABLE ' . $this->tableName . ' DROP FOREIGN KEY ' . $info['key']['constraint'];

                if (strpos($fieldName, '_multi')) {
                    //мультиселект
                    //мы можем тупо по имени поля получить имя таблицы значений
                    //но это как то неспортивно

                    //удаляем m2m таблицу
                    $queries[] = 'DROP TABLE ' . DBA::getFQTableName($info['key']['tableName']);
                    //удаляем переводы
                    $queries[] = 'DROP TABLE ' . DBA::getFQTableName($info['key']['tableName'] . '_values_translation');
                    //значения
                    $queries[] = 'DROP TABLE ' . DBA::getFQTableName($info['key']['tableName'] . '_values');
                } else {
                    //селект
                    //удаляем таблицу с переводами
                    $queries[] = 'DROP TABLE ' . DBA::getFQTableName($info['key']['tableName'] . '_translation');
                    //удаляем таблицу с значениями
                    $queries[] = 'DROP TABLE ' . DBA::getFQTableName($info['key']['tableName']);
                }
            }
            $queries[] = 'ALTER TABLE ' . $this->tableName . ' DROP ' . $fieldName;
            foreach ($queries as $query) {
                $this->dbh->modifyRequest($query);
            }

            $this->dbh->commit();
        }
    }

    /**
     * Get field tag.
     *
     * @param string $fieldName Field name.
     * @return string
     */
    private function getFieldLTag($fieldName) {
        list(, $tblName) = DBA::getFQTableName($this->tableName, true);
        return 'FIELD_' . $fieldName;
    }

    /**
     * Delete field tag.
     *
     * @param string $fieldName Field name.
     * @return string
     */
    private function deleteFieldLTag($fieldName) {
        $ltagName = $this->getFieldLTag($fieldName);
        $this->dbh->modifyRequest('DELETE FROM share_lang_tags WHERE ltag_name=%s', $ltagName);
        return $ltagName;
    }

    /**
     * Get table name.
     *
     * @return string
     */
    public function getTableName() {
        return $this->tableName;
    }

    /**
     * Change order.
     *
     * @param string $direction Order direction.
     * @param int $fieldIndex Field index.
     */
    public function changeOrder($direction, $fieldIndex) {
        $fieldIndex--;
        $cols = array_keys(
            $colsInfo = $this->dbh->getColumnsInfo($this->tableName));
        $srcField = $cols[$fieldIndex];
        $destFieldIndex = $fieldIndex + (($direction == Grid::DIR_UP) ? -2 : 1);
        if (($destFieldIndex <= 0) || ($destFieldIndex == sizeof($cols))) {
            return;
        }

        $destColField = $cols[$destFieldIndex];
        $query = 'ALTER TABLE ' . $this->tableName . ' MODIFY ' . $srcField .
            ' ' .
            self::getFDAsSQLString(FieldDescription::convertType($colsInfo[$srcField]['type'], $srcField, $colsInfo[$srcField]['length'], $colsInfo[$srcField])) .
            ((!$colsInfo[$srcField]['nullable']) ? ' NOT NULL ' : '') .
            ' AFTER ' . $destColField;
        $this->dbh->modifyRequest($query);
    }

    /**
     * Wrapper for Object::_getConfigValue.
     * This is for the cases when database name is not set in the configurations. In this case the current database will be used.
     *
     * @return string
     */
    public static function getDatabase() {
        return Object::_getConfigValue('forms.database', Object::_getConfigValue('database.db'));
    }

    /**
     * Get field as SQL string.
     *
     * @param string $fieldType Field type.
     * @return string
     */
    private static function getFDAsSQLString($fieldType) {

        switch ($fieldType) {
            case FieldDescription::FIELD_TYPE_INT:
            case FieldDescription::FIELD_TYPE_MULTI:
            case FieldDescription::FIELD_TYPE_SELECT:
                $result = 'INT(11) UNSIGNED ';
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
            case FieldDescription::FIELD_TYPE_INFO:
                $result = 'TINYINT(1)';
                break;
            case FieldDescription::FIELD_TYPE_STRING:
            case FieldDescription::FIELD_TYPE_FILE:
            default:
                $result = 'VARCHAR(255)';
        }
        return $result;
    }
}
