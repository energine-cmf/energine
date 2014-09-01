<?php
/**
 * @file
 * CommentsEditor
 *
 * It contains the definition to:
 * @code
class CommentsEditor;
@endcode
 *
 * @author sign
 *
 * @version 1.0.0
 */
namespace Energine\comments\components;
use Energine\share\components\Grid, Energine\share\gears\QAL, Energine\share\gears\SystemException, Energine\share\gears\FieldDescription, Energine\share\gears\JSONCustomBuilder;
/**
 * Comments editor.
 *
 * @code
class CommentsEditor;
@endcode
 */
class CommentsEditor extends Grid {
    /**
     * Table names with comments.
     * @var array $commentTables
     *
     * @see comments_editor.content.xml
     */
    private $commentTables = array();

    /**
     * Current table ID.
     * @var int $currTabIndex
     */
    private $currTabIndex = 0;

    /**
     * @copydoc Grid::__construct
     *
     * @throws SystemException 'Please set `comment_tables` parameter in comments_editor.content.xml file'
     */
    public function __construct($name, $module, array $params = null) {
        parent::__construct($name, $module, $params);

        $this->commentTables = $this->getParam('comment_tables');
        if (!$this->commentTables) {
            throw new SystemException('Please set `comment_tables` parameter in comments_editor.content.xml file');
        }
        if (!is_array($this->commentTables)) {
            $this->commentTables = array($this->commentTables);
        }

        $this->changeTableName();
        $this->setOrder(array('comment_created' => QAL::DESC));
        $this->setParam('onlyCurrentLang', true);
    }

    /**
     * @copydoc Grid::loadDataDescription
     */
    protected function loadDataDescription() {
        $result = parent::loadDataDescription();
        if ($this->getState() == 'edit') {
            unset($result['comment_parent_id']);
            unset($result['target_id']);
            $result['u_id']['key'] = false;
            $result['u_id']['type'] = QAL::COLTYPE_STRING;
        }
        return $result;
    }

    /**
     * Change table name.
     *
     * @param int $index Table ID.
     */
    private function changeTableName($index = 0) {
        // для метода save имя таблицы ищем в $_POST
        if ($this->getState() == 'save') {
            if (isset($_POST['componentAction']) &&
                    $_POST['componentAction'] == 'edit') {
                foreach ($_POST as $key => $value) {
                    if (in_array($key, $this->commentTables) &&
                            is_array($value) && isset($value['comment_name'])) {
                        $index = array_search($key, $this->commentTables);
                    }
                }
            }
        }
        elseif (!$index) {
            $index =
                    isset($_POST['tab_index']) ? intval($_POST['tab_index']) : 0;
        }

        $this->currTabIndex = $index;
        $currTableName = $this->commentTables[$this->currTabIndex];

        $this->setTableName($currTableName);
        $this->setTitle($this->translate('TAB_' . $currTableName));
    }

    /**
     * @copydoc Grid::edit
     */
    protected function edit() {
        $tab = $this->getStateParams();
        if ($tab) {
            $tab = (int) array_pop($tab);
            $this->changeTableName($tab);
        }
        parent::edit();
        $this->getDataDescription()->getFieldDescriptionByName('comment_name')->setType(FieldDescription::FIELD_TYPE_TEXT);
        $this->getDataDescription()->getFieldDescriptionByName('u_id')->setMode(ACCESS_READ);

        $UID = $this->getData()->getFieldByName('u_id');
        $UID->setRowData(0,
            simplifyDBResult(
                $this->dbh->select(
                    'user_users',
                    'u_fullname',
                    array('u_id' => $UID->getRowData(0))
                ),
                'u_fullname',
                true));
    }

    /**
     * Approve comment.
     *
     * @throws \Exception 'Add comment can auth user only'
     */
    protected function approve() {

        if (!$this->document->user->isAuthenticated()) {
            throw new \Exception('Add comment can auth user only');
        }

        list($commentId) = $this->getStateParams();

        $tabIndex = intval($_POST['tab_index']);
        $currTableName = $this->commentTables[$tabIndex];
        $result = $this->dbh->modify('UPDATE',
            $currTableName,
            array('comment_approved' => 1),
            array('comment_id' => $commentId)
        );

        $b = new JSONCustomBuilder();
        $b->setProperties(array(
            'result' => $commentId,
        ));
        $this->setBuilder($b);

    }

    /**
     * @copydoc Grid::main
     */
    // Добавляем вкладки для всех таблиц кроме первой(для которой загружаются данные) и меняем типы связанных полей для минимизации xml-а
    protected function main() {
        parent::main();

        if ($f =
                $this->getDataDescription()->getFieldDescriptionByName('u_id'))$f->setType(FieldDescription::FIELD_TYPE_STRING);
        if ($f =
                $this->getDataDescription()->getFieldDescriptionByName('target_id'))$f->setType(FieldDescription::FIELD_TYPE_STRING);

        foreach ($this->commentTables as $i => $table) {
            //пропускаем текущую таблицу - для неё уже создана нулевая вкладка
            // посути $this->currTabIndex может быть равен только нулю
            if ($i == $this->currTabIndex) continue;

            $fd = new FieldDescription($table . '_edit');
            $fd->setType(FieldDescription::FIELD_TYPE_HIDDEN);
            $fd->setProperty('tabName', 'TAB_' . $table);
            $this->getDataDescription()->addFieldDescription($fd);
        }
    }

    /**
     * @copydoc Grid::getFKData
     */
     //todo кажется здесь нужен фильтр
    protected function getFKData($fkTableName, $fkKeyName) {
        return $this->getForeignKeyData($fkTableName, $fkKeyName, $this->document->getLang());
    }

    /**
     * Get field name in foreign table.
     *
     * If displayed field name is not like @c "PREFIX_name" then we can set field name in the comment to the first field of primary key (title=XXXX_title).@n
     * If displayed field name has several properties then they should be separated by @c "|" sign.
     *
     * If in the comment to the primary key there are not necessary value then the string with field name like @c "PREFIX_name" will be returned.
     *
     * @param string $fkTableName Foreign table name.
     * @param string $fkKeyName Foreign key name.
     * @return string
     */
    protected function getForeinKeyFieldName($fkTableName, $fkKeyName) {
        // нам нужны первичные поля в таблице с флагом 'title' в комментарии
        $fields = $this->dbh->select(
            "SHOW FULL COLUMNS FROM `$fkTableName`
			WHERE `Key`='PRI' AND `Comment` LIKE '%title=%'"
        );
        // первое поле первичного ключа
        if ($fields && isset($fields[0]['Comment']) &&
                ($field = $fields[0]['Comment'])) {
            $properties = explode('|', $field);
            foreach ($properties as $property) {
                list($key, $value) = explode('=', $property);
                if ($key == 'title') {
                    return $value;
                }
            }
        }
        return substr($fkKeyName, 0, strpos($fkKeyName, '_')) . '_name';
    }

    /**
     * @copydoc QAL::getForeignKeyData
     *
     * This is an overwritten QAL::getForeignKeyData() method.
     *
     * @todo Исключать поля типа текст из результатов выборки для таблицы с переводами
     * @todo Подключить фильтрацию
     */
    protected function getForeignKeyData($fkTableName, $fkKeyName, $currentLangID, $filter = null) {
        //        $fkValueName = substr($fkKeyName, 0, strpos($fkKeyName, '_')).'_name';
        $fkValueName = $this->getForeinKeyFieldName($fkTableName, $fkKeyName);

        //если существует таблица с переводами для связанной таблицы
        //нужно брать значения оттуда
        if (
        $transTableName = $this->dbh->getTranslationTablename($fkTableName)) {
            if ($filter) {
                $filter = ' AND ' .
                        str_replace('WHERE', '', $this->dbh->buildWhereCondition($filter));
            }
            else {
                $filter = '';
            }

            $request = sprintf(
                'SELECT 
                    %2$s.*, %3$s.%s 
                    FROM %s %2$s 
                    LEFT JOIN %s %3$s on %3$s.%s = %2$s.%s 
                    WHERE lang_id =%s' . $filter,
                $fkValueName,
                $fkTableName,
                $transTableName,
                $fkKeyName,
                $fkKeyName,
                $currentLangID
            );
            $res = $this->dbh->select($request);
        }
        else {
            $columns = $this->dbh->getColumnsInfo($fkTableName);
            $columns = array_filter($columns,
                create_function('$value', 'return !($value["type"] == QAL::COLTYPE_TEXT);')
            );
            $ordering = $this->getOrderingByTable($fkTableName, $fkValueName);
            $res =
                    $this->dbh->select($fkTableName, array_keys($columns), $filter, $ordering);
        }

        return array($res, $fkKeyName, $fkValueName);
    }

    /**
     * Get ordering by table.
     *
     * @param string $fkTableName Foreign table name.
     * @param string $fkValueName Foreign value name.
     * @return array
     *
     * @note This is for overwriting/extending in child classes wor tables with non-standard structure and without translations.
     *
     * @see STBCommentsEditor::getOrderingByTable()
     */
    protected function getOrderingByTable($fkTableName, $fkValueName) {
        return array($fkValueName => QAL::ASC);
    }

    /**
     * @copydoc Grid::defineParams
     */
    // Параметер comment_tables содержит имена комментируемых таблиц разделённых символом '|'
    protected function defineParams() {
        $result = array_merge(parent::defineParams(),
            array(
                'comment_tables' => array()
            ));
        return $result;
    }

    /**
     * @copydoc Grid::delete
     */
    protected function delete() {
        $tab = $this->getStateParams();
        if ($tab) {
            $tab = (int) array_pop($tab);
            $this->changeTableName($tab);
        }
        return parent::delete();
    }
}