<?php 

/**
 * Содержит класс TagManager
 *
 * @package energine
 * @subpackage kernel
 * @author d.pavka
 * @copyright d.pavka@gmail.com
 */

/**
 * Класс реализующий функциональность по управлению тегами
 *
 * @package energine
 * @subpackage kernel
 * @author d.pavka@gmail.com
 */
class TagManager extends DBWorker {
    /**
     * Имя таблицы тегов
     */
    const TAG_TABLENAME = 'share_tags';

    const TAGS_TABLE_SUFFIX = '_tags';

    /**
     * Разделитель тегов
     */
    const TAG_SEPARATOR = ',';
    /**
     * @var DataDescription
     */
    private $dataDescription;
    /**
     * @var Data
     */
    private $data;
    /**
     * @var Имя таблицы тегов
     */
    private $tableName;
    /**
     * Флаг активности
     * @var bool
     */
    private $isActive;

    /**
     * @var FieldDescription
     */
    private $pk;

    /**
     * @param $dataDescription
     * @param $data
     * @param $tableName
     */
    public function __construct($dataDescription, $data, $tableName) {
        parent::__construct();
        if ($this->isActive = $this->dbh->tableExists($this->tableName = $tableName . '_tags')) {
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

    public function createFieldDescription() {
        if ($this->isActive) {
            if (!($fd = $this->dataDescription->getFieldDescriptionByName('tags'))) {
                $fd = new FieldDescription('tags');
                $this->dataDescription->addFieldDescription($fd);
            }
            $fd->setType(FieldDescription::FIELD_TYPE_TEXTBOX_LIST)->setProperty('url', 'tag-autocomplete')->setProperty('separator', TagManager::TAG_SEPARATOR);
        }
    }

    /**
     * @param $initialValue mixed начальное значение
     * @return void
     */
    public function createField($initialValue = null) {

        if ($this->isActive) {
            $field = new Field('tags');
            if (
                is_null($initialValue)
            ) {
                if (
                    !$this->data->isEmpty()
                    && ($currentData = $this->data->getFieldByName($this->pk->getName()))
                ) {
                    $field->setData($this->pull($currentData->getData(), $this->tableName));
                    $this->data->addField($field);
                }
            }
            else {
                for($i=0; $i<count(E()->getLanguage()->getLanguages()); $i++){
                    $field->setRowData($i, (is_array($initialValue))?$initialValue:array($initialValue));
                }
                $this->data->addField($field);
            }

        }

    }

    public function save($id) {
        if ($this->isActive && isset($_POST['tags'])) {
            $this->bind($_POST['tags'], $id, $this->tableName);
        }
    }

    /**
     * Связывание набора тегов с определенным полем
     *
     * @param $tags string строка тегов
     * @param $mapValue string имя поля-связки в связующей таблице
     * @param $mapTableName string имя связующей таблицы
     * @return array
     * @access public
     */
    public function bind($tags, $mapValue, $mapTableName) {
        if (!$this->dbh->tableExists($mapTableName)) {
            throw new SystemException('ERR_WRONG_TABLE_NAME', SystemException::ERR_DEVELOPER, $mapTableName);
        }
        $tags =
                array_filter(array_map(create_function('$tag', 'return mb_convert_case(trim($tag), MB_CASE_LOWER, "UTF-8");'), explode(self::TAG_SEPARATOR, $tags)));
        //Анализируем структуру таблицы
        $columns = array_keys($this->dbh->getColumnsInfo($mapTableName));
        unset($columns['tag_id']);
        list($mapFieldName) = $columns;
        $this->dbh->modify(QAL::DELETE, $mapTableName, null, array($mapFieldName => $mapValue));

        if (!empty($tags)) {
            foreach ($tags as $tag) {
                try {
                    $this->dbh->modify(QAL::INSERT, self::TAG_TABLENAME, array('tag_name' => $tag));
                }
                catch (Exception $e) {

                }
            }
            $tagIDs = array_keys(self::getID($tags));
            foreach ($tagIDs as $tagID) {
                $this->dbh->modify(QAL::INSERT, $mapTableName, array($mapFieldName => $mapValue, 'tag_id' => $tagID));
            }
        }
    }

    /**
     * Вытягивает имена тегов по переданной информации из связующей таблицы
     *
     * @throws SystemException
     * @param $mapValue
     * @param $mapTableName
     * @param bool $asString
     * @return array|mixed
     */
    public function pull($mapValue, $mapTableName, $asString = false) {
        if (!$this->dbh->tableExists($mapTableName)) {
            throw new SystemException('ERR_WRONG_TABLE_NAME', SystemException::ERR_DEVELOPER, $mapTableName);
        }

        $columns = array_keys($this->dbh->getColumnsInfo($mapTableName));
        $mapFieldName = '';
        unset($columns['tag_id']);
        list($mapFieldName) = $columns;
        $res =
                $this->dbh->select($mapTableName, array('tag_id', $mapFieldName), array($mapFieldName => $mapValue));

        if (is_array($res)) {
            $result = array();
            foreach ($res as $row) {
                if (!isset($result[$row[$mapFieldName]])) {
                    $result[$row[$mapFieldName]] = array();
                }
                $result[$row[$mapFieldName]][] = $row['tag_id'];
            }
        }

        foreach ((array)$mapValue as $targetID) {
            if (isset($result[$targetID])) {
                $data[] = self::getTags($result[$targetID], $asString);
            }
            else {
                $data[] = array();
            }
        }

        //return current($data);
        return $data;
    }

    /**
     * Возвращает идентификатор(ы) тегов по переданным значениям
     *
     * @param $tag mixed
     * @return array
     * @access public
     * @static
     */
    static public function getID($tag) {
        $result = null;
        if (!is_array($tag)) {
            $tag = explode(self::TAG_SEPARATOR, $tag);
        }
        $res = E()->getDB()->select(self::TAG_TABLENAME, true, array('tag_name' => $tag));
        if (is_array($res)) {
            foreach ($res as $row) {
                $result[$row['tag_id']] = $row['tag_name'];
            }
        }

        return $result;
    }

    /**
     * Возвращает перечень тегов
     *
     * @static
     * @param $str начальные буквы тега
     * @param bool | int $limit ограничение по количеству
     * @return array
     */
    static public function getTagStartedWith($str, $limit = false) {
        $result = array();
        $str = trim($str);

        if ($limit) {
            $limit = array($limit);
        }
        else $limit = null;

        $res =
                E()->getDB()->select(self::TAG_TABLENAME, 'tag_name', 'tag_name LIKE "%' . addslashes($str) . '%"', array('tag_name' => QAL::DESC), $limit);
        $result = simplifyDBResult($res, 'tag_name');

        return $result;
    }


    /**
     * Возвращает перечень тегов по переданным идентфикатором
     *
     * @param $tagID int[] | int идентфикатор(ы) тегов
     * @param bool $asSting вернуть как строку с разделителем
     *
     * @return array|mixed|string
     * @static
     */
    static public function getTags($tagID, $asSting = false) {

        if (!is_array($tagID)) {
            $tagID = array($tagID);
        }

        $res =
                E()->getDB()->select(self::TAG_TABLENAME, true, array('tag_id' => $tagID));
        if(is_array($res)){
            foreach ($res as $resVal){
                $result[$resVal['tag_id']] = $resVal['tag_name'];
            }
        }

        return ($asSting &&
                is_array($result)) ? implode(self::TAG_SEPARATOR, $result) : $result;
    }

    /**
     * @throws SystemException
     * @param $tags
     * @param $mapTableName
     * @return array|mixed
     * @static
     */
    static public function getFilter($tags, $mapTableName) {
        if (!E()->getDB()->tableExists($mapTableName)) {
            throw new SystemException('ERR_WRONG_TABLE_NAME', SystemException::ERR_DEVELOPER, $mapTableName);
        }
        $result = array();
        $tagInfo = self::getID($tags);
        if (!empty($tagInfo)) {
            $columns = array_keys(E()->getDB()->getColumnsInfo($mapTableName));
            unset($columns['tag_id']);
            list($mapFieldName) = $columns;
            $result =
                    simplifyDBResult(E()->getDB()->select($mapTableName, array($mapFieldName), array('tag_id' => array_keys($tagInfo))), $mapFieldName);
        }
        return $result;
    }
}