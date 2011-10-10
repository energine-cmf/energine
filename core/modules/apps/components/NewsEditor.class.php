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
         $this->getData()->getFieldByName('news_status')->setData(1,true);


        if ($this->getUploadsTablename())
            $this->addAttFilesField(
                $this->getUploadsTablename()
            );
    }

    protected function edit() {
        parent::edit();
        $entityID = $this->getData()->getFieldByName($this->getPK())->getRowData(0);
        if ($this->getUploadsTablename()) {
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
        $field = new Field('tags');
        $fieldData = E()->TagManager->pull($entityID, 'apps_news_tags');
        //$fieldData = array_keys(E()->TagManager->pull($newsID, 'stb_news_tags'));
        for ($i = 0, $langs = count(E()->getLanguage()->getLanguages());
             $i < $langs; $i++) {
            $field->setRowData($i, $fieldData);
        }
        $this->getData()->addField($field);
    }
    /**
     * Для формы добавляем поле тегов
     *
     * @return DataDescription
     * @access protected
     */

    protected function createDataDescription() {
        $result = parent::createDataDescription();
        if (in_array($this->getState(), array('add', 'edit'))) {
            $fd = new FieldDescription('tags');
            $fd->setType(FieldDescription::FIELD_TYPE_TEXTBOX_LIST);
            $result->addFieldDescription($fd);
        }

        // NOER: change data desc for field news_status to bool
        $news_status = $result->getFieldDescriptionByName('news_status');
        $news_status->setType(FieldDescription::FIELD_TYPE_BOOL);
        // END NOER 

        return $result;

    }    
}