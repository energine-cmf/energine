<?php 
/**
 * @file
 * TagManager
 *
 * It contains the definition to:
 * @code
class TagManager;
@endcode
 *
 * @author d.pavka
 * @copyright d.pavka@gmail.com
 *
 * @version 1.0.0
 */
namespace Energine\share\gears;
/**
 * Tag manager.
 *
 * @code
class TagManager;
@endcode
 */
class TagManager extends DBWorker {
    /**
     * Table name with tags.
     * @var string TAG_TABLENAME
     */
    const TAG_TABLENAME = 'share_tags';

    /**
     * Table suffix.
     * @var string TAGS_TABLE_SUFFIX
     */
    const TAGS_TABLE_SUFFIX = '_tags';

    /**
     * Translation table for tags.
     * @var string TAG_TABLENAME_TRANSLATION
     */
    const TAG_TABLENAME_TRANSLATION = 'share_tags_translation';

    /**
     * Tag separator.
     * @var string TAG_SEPARATOR
     */
    const TAG_SEPARATOR = ',';

    /**
     * Data descriptions.
     * @var DataDescription $dataDescription
     */
    private $dataDescription;
    /**
     * Data.
     * @var Data $data
     */
    private $data;
    /**
     * Table name.
     * @var string $tableName
     */
    private $tableName;
    /**
     * Activity flag.
     * @var bool $isActive
     */
    private $isActive;

    /**
     * Key field description.
     * @var FieldDescription $pk
     */
    private $pk;

    /**
     * @param DataDescription $dataDescription Data description.
     * @param Data $data Data.
     * @param string $tableName Table name.
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

    /**
     * Create field description.
     */
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
     * Create field.
     *
     * @param mixed $initialValue Initial value.
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
     * Bind set of tags with specific field.
     *
     * @param string $tags single string line of tags.
     * @param string $mapValue Name of ligaments filed in linked table.
     * @param string $mapTableName Name of a linked table.
     * @return array
     *
     * @throws SystemException 'ERR_WRONG_TABLE_NAME'
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
                    $tag_id = $this->dbh->getScalar(self::TAG_TABLENAME_TRANSLATION, 'tag_id', array('tag_name' => $tag, 'lang_id' => E()->getLanguage()->getCurrent()));
                    if (!$tag_id) {
                        $tag_id = self::insert($tag);
                    }
                }
                catch (\Exception $e) {

                }
            }
            $tagIDs = array_keys(self::getID($tags));
            foreach ($tagIDs as $tagID) {
                $this->dbh->modify(QAL::INSERT, $mapTableName, array($mapFieldName => $mapValue, 'tag_id' => $tagID));
            }
        }
    }

    /**
     * Pull tag names by passed information from linked table.
     *
     * @param string $mapValue Map value.
     * @param string $mapTableName Map table name.
     * @param bool $asString Return as string?
     * @return array|mixed
     *
     * @throws SystemException 'ERR_WRONG_TABLE_NAME'
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
     * Get tag IDs by passed values.
     *
     * @param mixed $tag Tags.
     * @return array
     */
    static public function getID($tag) {
        $result = null;
        if (!is_array($tag)) {
            $tag = explode(self::TAG_SEPARATOR, $tag);
        }

        $in = array();
        foreach($tag as $t) {
            $in[] = E()->getDB()->quote($t);
        }

        if (!empty($in)) {
            $res = E()->getDB()->select(
                'SELECT t.tag_id, tr.tag_name FROM ' . self::TAG_TABLENAME . ' as t '.
                'JOIN ' . self::TAG_TABLENAME_TRANSLATION . ' as tr ON t.tag_id = tr.tag_id AND tr.lang_id = %s ' .
                'WHERE tr.tag_name IN (' . implode(',', $in) . ')',
                E()->getLanguage()->getCurrent()
            );
        } else {
            $res = false;
        }
        if (is_array($res)) {
            foreach ($res as $row) {
                $result[$row['tag_id']] = $row['tag_name'];
            }
        }

        return $result;
    }

    /**
     * Get tags that begins from passed characters.
     *
     * @param string $str First tag characters.
     * @param bool|int $limit Limit the amount of matched tags.
     * @return array
     */
    static public function getTagStartedWith($str, $limit = false) {
        $res = E()->getDB()->select(
            'SELECT tr.tag_name FROM ' . self::TAG_TABLENAME . ' as t '.
            'JOIN ' . self::TAG_TABLENAME_TRANSLATION . ' as tr ON t.tag_id = tr.tag_id AND tr.lang_id = %s ' .
            'WHERE tr.tag_name LIKE ' . E()->getDB()->quote(trim($str) . '%%') . ' ' .
            'ORDER BY tr.tag_name DESC ' .
            (($limit) ? 'LIMIT ' . (int) $limit : ''),
            E()->getLanguage()->getCurrent()
        );

        $result = simplifyDBResult($res, 'tag_name');

        return $result;
    }


    /**
     * Get tags by IDs.
     *
     * @param array|int $tagID Tag ID(s).
     * @param bool $asSting Return as string?
     * @return array|string|mixed
     */
    static public function getTags($tagID, $asSting = false) {
        $result = array();

        if (empty($tagID)) {
            $tagID = array('-1');
        }

        if (!is_array($tagID)) {
            $tagID = array($tagID);
        }

        $res = E()->getDB()->select(
            'SELECT t.tag_id, tr.tag_name FROM ' . self::TAG_TABLENAME . ' as t '.
            'JOIN ' . self::TAG_TABLENAME_TRANSLATION . ' as tr ON t.tag_id = tr.tag_id AND tr.lang_id = %s ' .
            'WHERE tr.tag_id IN (' . implode(',', $tagID) . ')',
            E()->getLanguage()->getCurrent()
        );

        if(is_array($res)){
            foreach ($res as $resVal){
                $result[$resVal['tag_id']] = $resVal['tag_name'];
            }
        }

        return ($asSting &&
                is_array($result)) ? implode(self::TAG_SEPARATOR, $result) : $result;
    }

    /**
     * Get filter.
     *
     * @param mixed $tags
     * @param string $mapTableName Map table name.
     * @return array|mixed
     *
     * @throws SystemException 'ERR_WRONG_TABLE_NAME'
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

    /**
     * Insert tag.
     *
     * @param mixed $tag Tag.
     * @return bool|int
     */
    public static function insert($tag) {
        $tag_id = E()->getDB()->modify(QAL::INSERT, self::TAG_TABLENAME, array('tag_code' => $tag));
        $langs = E()->getLanguage()->getLanguages();
        if ($langs && $tag_id) {
            foreach($langs as $lang_id => $lang_info) {
                E()->getDB()->modify(QAL::INSERT_IGNORE, self::TAG_TABLENAME_TRANSLATION,
                    array('tag_id' => $tag_id, 'lang_id' => $lang_id, 'tag_name' => $tag));
            }
        }
        return $tag_id;
    }
}