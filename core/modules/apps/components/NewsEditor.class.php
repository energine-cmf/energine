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
class NewsEditor extends ExtendedFeedEditor {
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



    protected function add() {
        parent::add();
        $this->getDataDescription()->getFieldDescriptionByName('news_segment')->setType(FieldDescription::FIELD_TYPE_HIDDEN);
        $f = new Field('news_is_active');
        $f->setData(true, true);
        $this->getData()->addField($f);
    }

}