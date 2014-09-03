<?php
/**
 * @file
 * FormEditor
 *
 * It contains the definition to:
 * @code
class FormsEditor;
@endcode
 *
 * @author d.pavka
 * @copyright d.pavka@gmail.com
 *
 * @version 1.0.0
 */
namespace Energine\forms\components;
use Energine\share\components\Grid, Energine\forms\gears\FormsSaver, Energine\share\gears\FieldDescription, Energine\forms\gears\FormConstructor, Energine\share\gears\QAL;
/**
 * Create custom form.
 *
 * @code
class FormsEditor;
@endcode
 */
class FormsEditor extends Grid {
    /**
     * Form editor.
     * @var FormEditor $form
     */
    private $form;
    /**
     * Form results.
     * @var FormResults $results
     */
    private $results;

    /**
     * @copydoc Grid::__construct
     */
    public function __construct($name, $module, array $params = null) {
        parent::__construct($name, $module, $params);
        $this->setTableName('frm_forms');
        $this->setSaver(new FormsSaver());
        $this->setOrder(array('form_creation_date' => QAL::DESC));
    }

    /**
     * @copydoc Grid::createDataDescription
     */
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

    /**
     * Edit form.
     */
    protected function editForm() {
        list($formID) = $this->getStateParams();
        E()->getRequest()->shiftPath(2);
        $this->form = $this->document->componentManager->createComponent('form', 'forms', 'FormEditor', array('form_id' => $formID));
        $this->form->run();
    }

    /**
     * Show result.
     */
    protected function showResult() {
        list($formID) = $this->getStateParams();
        E()->getRequest()->shiftPath(2);
        $this->results = $this->document->componentManager->createComponent('form', 'forms', 'FormResults', array('form_id' => $formID));
        $this->results->run();
    }

    /**
     * @copydoc Grid::deleteData
     */
    protected function deleteData($id) {
        parent::deleteData($id);
        $res = $this->dbh->select('SHOW FULL TABLES FROM `' . FormConstructor::getDatabase() . '` LIKE "%form_' . $id . '%"');
        if (is_array($res)) {
            $tables = array_map(function($row) { return current($row); }, $res);
            $this->dbh->modifyRequest('SET FOREIGN_KEY_CHECKS=0;');
            foreach ($tables as $tableName) {
                $this->dbh->modifyRequest('DROP TABLE `' . FormConstructor::getDatabase() . '`.' . $tableName);
            }
        }
    }

    /**
     * @copydoc Grid::build
     */
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