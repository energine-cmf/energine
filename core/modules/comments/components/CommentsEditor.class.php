<?php
/**
 *
 * Управление комментариями
 *
 * Вкладки грида соответствуют таблицам с комментариями заданными в параметре comment_tables
 * Клик на вкладке передаёт порядковый номер вкладки (0..n) который соответствует индексу таблицы
 * @see CommentsEditor::changeTableName
 *
 * @author sign
 *
 */
class CommentsEditor extends Grid {
    /**
     * Таблицы с комментариями
     * @see comments_editor.content.xml
     * @var array string[]
     */
    private $commentTables = array();

    /**
     * Индекс текущей таблицы
     * @var int
     */
    private $currTabIndex = 0;

    /**
     *
     * Enter description here ...
     * @param unknown_type $name
     * @param unknown_type $module
     * @param Document $document
     * @param array $params
     * @throws SystemException если параметер comment_tables не задан
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
     * Выбираем рабочую таблицу
     *
     * @param $index
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

    protected function approve() {

        if (!$this->document->user->isAuthenticated()) {
            throw new Exception('Add comment can auth user only');
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
     * ...
     *
     * Добавляем вкладки для всех таблиц кроме первой(для которой загружаются данные)
     * и меняем типы связанных полей для минимизации xml-а
     */
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
     * Возвращает данные о значения в связанной таблицы
     *
     * @param string $fkTableName
     * @param string $fkKeyName
     * @return array
     * @access protected
     *
     * @todo кажется здесь нужен фильтр
     */
    protected function getFKData($fkTableName, $fkKeyName) {
        return $this->getForeignKeyData($fkTableName, $fkKeyName, $this->document->getLang());
    }

    /**
     * Отображаемое поле в связанной таблице
     *
     * Если отображаемое поле имеет имя не типа 'PREFIX_name' то мы можем указать имя поля в комментарии
     * к первому полю первичного ключа (title=XXXX_title)
     * если в комментарии несколько пар свойств то они должны быть разделены символом '|'
     *
     *  Если в комментарии к первичному ключу нужного значения нет
     *  то будет возвращена строка с именем поля типа 'PREFIX_name'
     *
     * @param string $fkTableName
     * @param string $fkKeyName
     * @return string
     */
    protected function getForeinKeyFieldName($fkTableName, $fkKeyName) {
        // нам нужны первичные поля в таблице с флагом 'title' в комментарии
        $fields = $this->dbh->selectRequest(
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
     * ВОзвращает данные из таблицы связанной по внешнему ключу
     *
     * @see QAL::getForeignKeyData()
     * Копия метода QAL::getForeignKeyData()
     * отличается лишь определением имени поля в связанной таблице @see CommentsEditor::getForeinKeyFieldName()
     *
     * @param string $fkTableName Имя таблицы
     * @param string $fkKeyName имя ключа
     * @param int $currentLangID идентификатор текущего языка
     * @param mixed $filter ограничение на выборку
     * @access public
     * @return array
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
            $res = $this->dbh->selectRequest($request);
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
     * Сортировка для таблицы
     *
     * Метод для переопределенияв потомках для таблиц с нестандартной структурой без таблицы переводов
     * @see STBCommentsEditor::getOrderingByTable()
     *
     * @param  string $fkTableName
     * @param  string $fkValueName
     * @return array
     */
    protected function getOrderingByTable($fkTableName, $fkValueName) {
        return array($fkValueName => QAL::ASC);
    }

    /**
     * Определяет допустимые параметры компонента и их значения по-умолчанию
     * в виде массива array(paramName => defaultValue).
     *
     * Параметер comment_tables содержит имена комментируемых таблиц разделённых символом '|'
     *
     * @access protected
     * @return array
     */
    protected function defineParams() {
        $result = array_merge(parent::defineParams(),
            array(
                'comment_tables' => array()
            ));
        return $result;
    }

    protected function delete() {
        $tab = $this->getStateParams();
        if ($tab) {
            $tab = (int) array_pop($tab);
            $this->changeTableName($tab);
        }
        return parent::delete();
    }
}