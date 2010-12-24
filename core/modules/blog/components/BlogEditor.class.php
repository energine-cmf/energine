<?php 
/**
 * Содержит класс BlogEditor
 *
 * @package energine
 * @subpackage blog
 * @author sign
 */

 /**
  * Редактор постов блога
  *
  * @package energine
  * @subpackage blog
  * @author sign
  */
 class BlogEditor extends Grid {
    /**
     * Конструктор класса
     *
     * @param string $name
     * @param string $module

     * @param array $params
     * @access public
     */
    public function __construct($name, $module,   array $params = null) {
        parent::__construct($name, $module,  $params);
        $this->setTableName('blog_title');
//        $this->setOrderColumn('blog_name');
    }
     /**
     * Устанавливаем дефолтные значения
     *
     * @access protected
     * @return void
     */
//    protected function add() {
//        parent::add();
//        $this->getData()->getFieldByName('vacancy_is_active')->setData(true, true);
//        $this->getData()->getFieldByName('vacancy_date')->setData(date('Y-m-d'), true);
//        $this->getDataDescription()->getFieldDescriptionByName('vacancy_url_segment')->setProperty('nullable','nullable');
//        $this->getDataDescription()->getFieldDescriptionByName('vacancy_url_segment')->removeProperty('pattern');
//    }

    /**
     * Устанавливаем необязательность vacancy_url_segment
     *
     * @access protected
     * @return void
     */
//    protected function edit() {
//        parent::edit();
//        $this->getDataDescription()->getFieldDescriptionByName('vacancy_url_segment')->setProperty('nullable','nullable');
//        $this->getDataDescription()->getFieldDescriptionByName('vacancy_url_segment')->removeProperty('pattern');
//    }

    /**
     * ПРи сохранении данных устанавливаем сегмент УРЛа
     *
     * @access protected
     * @return mixed
     */
//    protected function saveData() {
//        if (!isset($_POST[$this->getTableName()]['vacancy_url_segment']) || empty($_POST[$this->getTableName()]['vacancy_url_segment'])) {
//            $_POST[$this->getTableName()]['vacancy_url_segment'] = Translit::transliterate(
//                $_POST[$this->getTranslationTableName()][E()->getLanguage()->getDefault()]['vacancy_name']
//            , '-', true);
//        }
//        return parent::saveData();
//    }
}