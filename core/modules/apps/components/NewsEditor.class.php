<?php
/**
 * Содержит класс NewsEditor
 *
 * @package energine
 * @subpackage share
 * @author dr.Pavka
 * @copyright Energine 2007
 * @version $Id: NewsEditor.class.php,v 1.10 2008/08/27 15:39:16 chyk Exp $
 */

/**
 * Редактор новостей сайта
 *
 * @package energine
 * @subpackage share
 * @author dr.Pavka
 */
class NewsEditor extends FeedEditor {
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
        $this->setSaver(new NewsEditorSaver());
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

    protected function add() {
        parent::add();
        $this->getDataDescription()->getFieldDescriptionByName('news_segment')->setType(FieldDescription::FIELD_TYPE_HIDDEN);

        if ($this->getUploadsTablename())
            $this->addAttFilesField(
                $this->getUploadsTablename()
            );
    }

    protected function edit() {
        parent::edit();
        if ($this->getUploadsTablename()) {
            $entityID = $this->getData()->getFieldByName($this->getPK())->getRowData(0);
            $this->addAttFilesField(
                $this->getUploadsTablename(),
                $this->dbh->selectRequest('
                            SELECT files.upl_id, upl_path, upl_name
                            FROM `' . $this->getUploadsTablename() . '` s2f
                            LEFT JOIN `share_uploads` files ON s2f.upl_id=files.upl_id
                            WHERE ' . $this->getPK() . ' = %s
                            ORDER BY upl_order_num
                        ', $entityID)
            );
        }
    }
}