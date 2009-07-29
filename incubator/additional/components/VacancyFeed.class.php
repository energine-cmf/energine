<?php
/**
 * Класс Содержит класс списка вакансий.
 *
 * @package energine
 * @subpackage misc
 * @author d.pavka
 * @copyright d.pavka@gmal.com
 * @version $Id$
 */

 /**
 * Спиоск вакансий
 *
 * @package energine
 * @subpackage misc
 * @author d.pavka
 */
class VacancyFeed extends Feed {
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
        $this->setTableName('aux_vacancies');

        //Если у текущего пользователя нет прав на редактирование - убираем все все неактивные вакансии
        if ($this->document->getRights() < ACCESS_FULL) {
            $this->setFilter(array('vacancy_is_active'=>1));
        }

        $this->setOrder(array('vacancy_end_date'=>QAL::DESC, 'vacancy_date'=>QAL::ASC));
    }
    
    protected function createDataDescription(){
        $result = parent::createDataDescription();
        foreach ($result as $fieldDescription){
            if($fieldDescription->getType() == FieldDescription::FIELD_TYPE_DATE){
                $fieldDescription->addProperty('outputFormat', '%d/%m/%Y');
            }
        }
        return $result;
    }

    /**
     * Убираем текстовый блок
     *
     * @access protected
     * @return void
     */
    protected function view() {
        $this->setType(self::COMPONENT_TYPE_FORM);
        $id = $this->getActionParams();
        list($id) = $id;
        $this->setFilter(array('vacancy_url_segment' => $id));
        $this->prepare();
        foreach ($this->getDataDescription()->getFieldDescriptions() as $fieldDescription) {
            $fieldDescription->setMode(FieldDescription::FIELD_MODE_READ);
        }
        $this->document->componentManager->getComponentByName('vacancyTextBlock')->disable();
    }
}