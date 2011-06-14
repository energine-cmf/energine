<?php 
/**
 * Содержит класс ForumThemeEditor
 *
 * @package energine
 * @subpackage forum
 * @author sign
 */

/**
 * Редактор категорий форумов
 *
 * @package energine
 * @subpackage forum
 * @author sign
 */
class ForumThemeEditor extends Grid {
    /**
     * Конструктор класса
     *
     * @param string $name
     * @param string $module

     * @param array $params
     * @access public
     */
    public function __construct($name, $module, array $params = null) {
        parent::__construct($name, $module, $params);
        $this->setTableName('forum_theme');
        $this->setOrder(array('theme_created' => QAL::DESC));
    }

    protected function loadDataDescription() {
        $result = parent::loadDataDescription();
        if (in_array($this->getState(), array('edit'))) {
            unset($result['comment_id']);
            $result['u_id']['key'] = false;
            $result['smap_id']['key'] = false;
        }
        return $result;

    }

    protected function loadData() {
        if ($this->getState() == 'getRawData') {
            $data = false;
            $this->applyUserFilter();
            $actionParams = $this->getStateParams(true);

            if (isset($actionParams['sortField']) &&
                    isset($actionParams['sortDir'])) {
                //подразумевается что sortDir - тоже существует
                $this->setOrder(array($actionParams['sortField'] => $actionParams['sortDir']));
            }

            if ($this->pager) {
                // pager существует -- загружаем только часть данных, текущую страницу
                $this->setLimit($this->pager->getLimit());
            }
            $this->addFilterCondition(array('sst.lang_id' => $this->document->getLang()));
            //$result = parent::loadData();
            $request = 'SELECT
             sql_calc_found_rows
             theme_id,
             theme_created,
             smap_name as smap_id ,
             theme_name,
             u_nick as u_id
             FROM `forum_theme` ft
             LEFT JOIN user_users u ON ft.u_id = u.u_id 
             LEFT JOIN share_sitemap_translation sst ON sst.smap_id = ft.smap_id ' .
                    $this->dbh->buildWhereCondition($this->getFilter()) . ' ' .
                    $this->dbh->buildOrderCondition($this->getOrder()) . ' ' .
                    $this->dbh->buildLimitStatement($this->getLimit()
                    );

            $res = $this->dbh->selectRequest($request);
            if (is_array($res)) {
                $data = $res;
                if ($this->pager) {
                    if (!($recordsCount =
                            simplifyDBResult($this->dbh->selectRequest('SELECT FOUND_ROWS() as c'), 'c', true))) {
                        $recordsCount = 0;
                    }
                    $this->pager->setRecordsCount($recordsCount);
                }
            }
        }
        else {
            $data = parent::loadData();
        }
        return $data;
    }

    protected function edit() {
        parent::edit();
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
        $this->getDataDescription()->getFieldDescriptionByName('smap_id')->setType(FieldDescription::FIELD_TYPE_STRING)->setMode(ACCESS_READ);
        $smapID = $this->getData()->getFieldByName('smap_id');
        $smapID->setRowData(0,
            simplifyDBResult($this->dbh->select('share_sitemap_translation', 'smap_name', array('smap_id'=>$smapID->getRowData(0),'lang_id' => $this->document->getLang())), 'smap_name', true)
        );
    }

    protected function deleteData($id){
        $this->dbh->modify(QAL::DELETE, 'forum_theme_comment', null, array('target_id' => $id));
        parent::deleteData($id);
    }
}