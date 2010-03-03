<?php
/**
 * Содержит класс редактора вакансий.
 *
 * @package energine
 * @subpackage misc
 * @author d.pavka
 * @copyright d.pavka@gmal.com
 * @version $Id$
 */

 /**
 * Редактор вакансий, интегрирующийся в список вакансий
 *
 * @package energine
 * @subpackage misc
 * @author d.pavka
 * @
 */
 class VacancyFeedEditor extends FeedEditor {
    /**
     * Конструктор класса
     *
     * @param string $name
     * @param string $module
     * @param Document $document
     * @param array $params
     * @access public
     */
    public function __construct($name, $module, Document $document,  array $params = null) {
        parent::__construct($name, $module, $document,  $params);
        $this->setTableName('hrm_vacancies');
    }


    /**
     * Устанавливаем дефолтные значения
     *
     * @access protected
     * @return void
     */
    protected function add() {
        parent::add();
        $this->getData()->getFieldByName('vacancy_is_active')->setData(true, true);
        $this->getData()->getFieldByName('vacancy_date')->setData(date('Y-m-d'), true);
        $this->getDataDescription()->getFieldDescriptionByName('vacancy_url_segment')->setProperty('nullable','nullable');
        $this->getDataDescription()->getFieldDescriptionByName('vacancy_url_segment')->removeProperty('pattern');
    }

    /**
     * Устанавливаем необязательность vacancy_url_segment
     *
     * @access protected
     * @return void
     */
    protected function edit() {
        parent::edit();
        $this->getDataDescription()->getFieldDescriptionByName('vacancy_url_segment')->setProperty('nullable','nullable');
        $this->getDataDescription()->getFieldDescriptionByName('vacancy_url_segment')->removeProperty('pattern');
    }

    /**
     * ПРи сохранении данных устанавливаем сегмент УРЛа
     *
     * @access protected
     * @return mixed
     */
    protected function saveData() {
        if (!isset($_POST[$this->getTableName()]['vacancy_url_segment']) || empty($_POST[$this->getTableName()]['vacancy_url_segment'])) {
            $_POST[$this->getTableName()]['vacancy_url_segment'] = Translit::transliterate(
                $_POST[$this->getTranslationTableName()][Language::getInstance()->getDefault()]['vacancy_name']
            , '-', true);
        }
        return parent::saveData();
    }

}