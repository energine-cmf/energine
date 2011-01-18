<?php
/**
 * Содержит класс NewsFeed
 *
 * @package energine
 * @subpackage share
 * @author dr.Pavka
 * @copyright Energine 2007
 * @version $Id$
 */

/**
 * Лента новостей
 *
 * @package energine
 * @subpackage share
 * @author dr.Pavka
 */
class NewsFeed extends Feed {
    /**
     * Таблица приаттаченных файлов
     *
     * @access private
     * @var string
     */
    private $uploadsTable;

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
        $this->setTableName('apps_news');
        $this->setOrder(array('news_date' => QAL::DESC));
    }

    protected function defineParams() {
        return array_merge(
            parent::defineParams(),
            array(
                'editable' => true,
            )
        );
    }

    protected function setParam($name, $value) {
        if ($name == 'tableName') {
            if ($this->dbh->tableExists($value . '_uploads')) {
                $this->uploadsTable = $value . '_uploads';
            }
        }
        parent::setParam($name, $value);
    }

    /**
     * Возвращает имя таблицы аттачментов
     *
     * @return string
     * @access protected
     */
    protected function getUploadsTablename() {
        return $this->uploadsTable;
    }

    protected function createDataDescription() {
        $res = DBDataSet::createDataDescription();
        if(!$res->getFieldDescriptionByName('smap_id')){
            $f = new FieldDescription('smap_id');
            $f->setType(FieldDescription::FIELD_TYPE_INT)->setProperty('tableName', $this->getTableName());
            $res->addFieldDescription($f);
        }
        if(!$res->getFieldDescriptionByName('category')){
            $f = new FieldDescription('category');
            $f->setType(FieldDescription::FIELD_TYPE_STRING);
            $res->addFieldDescription($f);
        }
        return $res;
    }

    protected function loadDataDescription() {
        $res = parent::loadDataDescription();
        if (isset($res['smap_id'])) {
            $res['smap_id']['key'] = false;
        }
        return $res;
    }

    protected function createData() {
        if ($this->document->getRights() < ACCESS_EDIT) {
            $this->addFilterCondition(
                'news_date <= NOW()'
            );
        }
        $res = parent::createData();
        if(!($categoryField = $res->getFieldByName('category'))){
            $categoryField = new Field('category');
            $res->addField($categoryField);
        }
        
        if($f = $res->getFieldByName('smap_id')){
            $map = E()->getMap();
            foreach($f as $i => $row){
                $catInfo = $map->getDocumentInfo($row);
                $categoryField->setRowData($i, $catInfo['Name']);
                $categoryField->setRowProperty($i, 'url', $map->getURLByID($row));
            }
        }
        return $res;
    }

    protected function main() {
        $ap = $this->getStateParams(true);
        foreach (array(
            'year' => 'YEAR(%s)',
            'month' => 'MONTH(%s)',
            'day' => 'DAY(%s)'
        ) as $parameterName => $SQLFuncName) {
            if (isset($ap[$parameterName])) {
                $this->addFilterCondition(
                    array(
                        sprintf($SQLFuncName, 'news_date') => $ap[$parameterName]
                    )
                );
            }
        }
        parent::main();
        if ($this->getUploadsTablename()) {
            $this->getDataDescription()->addFieldDescription(E()->AttachmentManager->createFieldDescription());
            if (!$this->getData()->isEmpty()) {
                $this->getData()->addField(E()->AttachmentManager->createField($this->getData()->getFieldByName($this->getPK())->getData(), $this->getPK(), $this->getUploadsTablename(), true));
            }
        }
    }


    /**
     * View
     *
     * @return type
     * @access protected
     */

    protected function view() {
        $ap = $this->getStateParams(true);

        $this->addFilterCondition(
            array(
                $this->getTableName() . '.' . $this->getPK() => $ap['id'],
                'news_segment' => $ap['segment'],
            )
        );
        $this->setType(self::COMPONENT_TYPE_FORM);
        $this->setDataDescription($this->createDataDescription());
        $this->setBuilder($this->createBuilder());
        $this->createPager();
        $this->setData($this->createData());
        if (!$this->getData()->isEmpty()) {
            list($newsTitle) = $this->getData()->getFieldByName('news_title')->getData();
            $this->document->componentManager->getBlockByName('breadCrumbs')->addCrumb('', $newsTitle);
        }
        else {
            throw new SystemException('ERR_404', SystemException::ERR_404);
        }

        $this->addToolbar($this->createToolbar());

        foreach ($this->getDataDescription() as $fieldDescription) {
            $fieldDescription->setMode(FieldDescription::FIELD_MODE_READ);
        }
        if ($this->getUploadsTablename()) {

            $this->getDataDescription()->addFieldDescription(E()->AttachmentManager->createFieldDescription());
            $this->getData()->addField(E()->AttachmentManager->createField($this->getData()->getFieldByName($this->getPK())->getData(), $this->getPK(), $this->getUploadsTablename()));
        }

    }
}