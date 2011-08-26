<?php
/**
 * Содержит класс FormEditor
 *
 * @package energine
 * @subpackage forms
 * @author d.pavka
 * @copyright d.pavka@gmail.com
 */

/**
 * Построитель пользовательских форм
 *
 * @package energine
 * @subpackage forms
 * @author d.pavka@gmail.com
 */
class FormsEditor extends Grid {
    /**
     * @var FormEditor
     */
    private $form;
    /**
     * @var FormResults
     */
    private $results;

    private $formComponent;

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
        $this->setTableName('frm_forms');
        $this->setSaver(new FormsSaver());
        $this->setOrder(array('form_creation_date' => QAL::DESC));
    }

    protected function createDataDescription() {
        $result = parent::createDataDescription();
        if (in_array($this->getState(), array('main', 'getRawData'))) {
            $result->getFieldDescriptionByName('form_id')->setType(FieldDescription::FIELD_TYPE_INT);
        }
        elseif (in_array($this->getState(), array('add', 'edit'))) {
            $result->getFieldDescriptionByName('form_creation_date')->setMode(FieldDescription::FIELD_MODE_READ);
            $result->getFieldDescriptionByName('form_email_adresses')->setType(FieldDescription::FIELD_TYPE_STRING);
        }
        return $result;
    }


    protected function editForm() {
        list($formID) = $this->getStateParams();
        E()->getRequest()->shiftPath(2);
        $this->form = $this->document->componentManager->createComponent('form', 'forms', 'FormEditor', array('form_id' => $formID));
        $this->form->run();
    }

    protected function showResult() {
        list($formID) = $this->getStateParams();
        E()->getRequest()->shiftPath(2);
        $this->results = $this->document->componentManager->createComponent('form', 'forms', 'FormResults', array('form_id' => $formID));
        $this->results->run();
    }

    protected function deleteData($id) {
        parent::deleteData($id);
        $res = $this->dbh->selectRequest('SHOW FULL TABLES FROM `' . $this->getConfigValue('forms.database') . '` LIKE "%form_' . $id . '%"');
        if (is_array($res)) {
            $tables = array_map(function($row) { return current($row); }, $res);
            $this->dbh->modifyRequest('SET FOREIGN_KEY_CHECKS=0;');
            foreach ($tables as $tableName) {
                $this->dbh->modifyRequest('DROP TABLE `' . $this->getConfigValue('forms.database') . '`.' . $tableName);
            }
        }
    }

    public function build() {
        if ($this->getState() == 'editForm') {
            $result = $this->form->build();
        }
        elseif ($this->getState() == 'showResult') {
            $result = $this->results->build();
        }
        else {
            $result = parent::build();
        }
        return $result;
    }
}