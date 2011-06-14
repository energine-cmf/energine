<?php

class NewsEditorSaver extends Saver {
    /**
     * Имя первичного ключа таблицы
     *
     * @access private
     * @var string
     */
    private $pk;
    /**
     * Имя таблиці аплоадсов
     *
     * @access private
     * @var string
     */
    private $uploadsTableName;

    public function setData(Data $data) {
        parent::setData($data);
        if (!$data->getFieldByName('news_segment')->getRowData(0)) {
            $f = new Field('news_segment');
            $translitedTitle = Translit::asURLSegment($data->getFieldByName('news_title')->getRowData(0));
            for ($i = 0, $l = sizeof(E()->getLanguage()->getLanguages()); $i < $l; $i++)
                $f->setRowData($i, $translitedTitle);

            $data->addField($f);
        }
    }

    /**
     * Устанавливает имя таблицы аплоадсов
     *
     * @return void
     * @access public
     */
    public function setDataDescription(DataDescription $dd) {
        parent::setDataDescription($dd);
        foreach ($dd as $fieldName => $fieldInfo) {
            if ($fieldInfo->getPropertyValue('key') === true) {
                $this->pk = $fieldName;
                $this->uploadsTableName = $fieldInfo->getPropertyValue('tableName') . '_uploads';
                if (!$this->dbh->tableExists($this->uploadsTableName)) {
                    $this->uploadsTableName = false;
                }
            }
        }
    }

    public function save() {
        $result = parent::save();
        $entityID = ($this->getMode() == QAL::INSERT) ? $result : $this->getData()->getFieldByName($this->pk)->getRowData(0);
        //Записываем информацию в таблицу тегов
        if (isset($_POST['tags'])) {
            E()->TagManager->bind($_POST['tags'], $entityID, 'apps_news_tags');
        }
        if ($this->uploadsTableName) {
            //Удаляем предыдущие записи из таблицы связей с дополнительными файлам
            $this->dbh->modify(QAL::DELETE, $this->uploadsTableName, null, array($this->pk => $entityID));

            //записываем данные в таблицу share_sitemap_uploads
            if (isset($_POST['uploads']['upl_id'])) {
                foreach ($_POST['uploads']['upl_id'] as $uplOrderNum => $uplID) {
                    $this->dbh->modify(QAL::INSERT, $this->uploadsTableName, array('upl_order_num' => ($uplOrderNum + 1), $this->pk => $entityID, 'upl_id' => $uplID));
                }
            }
        }
        return $result;
    }
}
