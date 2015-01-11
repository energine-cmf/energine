<?php
/**
 * @file
 * FormEditor
 *
 * It contains the definition to:
 * @code
class FormEditor;
 * @endcode
 *
 * @author d.pavka
 * @copyright d.pavka@gmail.com
 *
 * @version 1.0.0
 */
namespace Energine\forms\components;

use Energine\share\gears\SystemException, Energine\forms\gears\FormConstructor, Energine\share\gears\DataDescription, Energine\share\gears\FieldDescription, Energine\share\gears\MultiLanguageBuilder, Energine\share\gears\JSONBuilder, Energine\share\gears\Data, Energine\share\gears\Field, Energine\share\gears\QAL, Energine\share\gears\JSONCustomBuilder, Energine\share\components\Grid, Energine\share\gears\Cache, Energine\share\components\DataSet;

/**
 * Form editor.
 *
 * @code
class FormEditor;
 * @endcode
 */
class FormEditor extends DataSet {
    /**
     * Editor of selector values.
     * @var SelectorValuesEditor $SVEditor
     */
    private $SVEditor;

    /**
     * Form constructor.
     * @var FormConstructor $constructor
     */
    private $constructor;

    /**
     * @copydoc DataSet::__construct
     */
    public function __construct($name, $module, array $params = null) {
        parent::__construct($name, $module, $params);
        if (!$this->getParam('form_id')) {
            throw new SystemException('ERR_BAD_FORM_ID');
        }
        $this->constructor = new FormConstructor($this->getParam('form_id'));
        $this->setProperty('exttype', 'grid');
        $this->setType(self::COMPONENT_TYPE_LIST);

    }

    /**
     * @copydoc DataSet::defineParams
     */
    protected function defineParams() {
        return array_merge(
            parent::defineParams(),
            array(
                'form_id' => false,
                'active' => true
            )
        );
    }

    /**
     * @copydoc DataSet::createDataDescription
     */
    protected function createDataDescription() {
        //We work with other fields when we edit field - so create other DataDescription.
        if (in_array($this->getState(), array('edit'))) {
            $dd = new DataDescription();
            $dd->load(array(
                'ltag_id' => array(
                    'nullable' => false,
                    'length' => 10,
                    'default' => '',
                    'key' => true,
                    'type' => FieldDescription::FIELD_TYPE_INT,
                    'index' => 'PRI',
                    'tableName' => 'share_lang_tags',
                    'languageID' => false,
                ),
                'lang_id' => array(
                    'nullable' => false,
                    'length' => 10,
                    'default' => '',
                    'key' => false,
                    'type' => FieldDescription::FIELD_TYPE_INT,
                    'index' => 'PRI',
                    'tableName' => 'share_lang_tags_translation',
                    'languageID' => true,
                    'isMultilanguage' => true
                ),
                'field_type' => array(
                    'nullable' => false,
                    'length' => 255,
                    'default' => '',
                    'key' => false,
                    'type' => FieldDescription::FIELD_TYPE_HIDDEN,
                    'mode' => FieldDescription::FIELD_MODE_READ,
                    'index' => false,
                    'languageID' => false
                ),
                'ltag_name' => array(
                    'nullable' => false,
                    'length' => 255,
                    'default' => '',
                    'key' => false,
                    'type' => FieldDescription::FIELD_TYPE_STRING,
                    'index' => false,
                    'languageID' => false
                ),
                'ltag_value_rtf' => array(
                    'nullable' => false,
                    'length' => 1024,
                    'default' => '',
                    'key' => false,
                    'type' => FieldDescription::FIELD_TYPE_TEXT,
                    'index' => false,
                    'tableName' => 'share_lang_tags_translation',
                    'isMultilanguage' => true
                )));
            $dd->getFieldDescriptionByName('ltag_name')->setMode(FieldDescription::FIELD_MODE_READ);
            return $dd;
        } else {
            return $this->constructor->getDataDescription();
        }
    }

    /**
     * @copydoc DataSet::createBuilder
     */
    protected function createBuilder() {
        return new MultiLanguageBuilder();
    }

    /**
     * @copydoc DataSet::loadData
     */
    protected function loadData() {
        return false;
    }

    /**
     * @copydoc DataSet::createData
     */
    protected function createData() {
        return $this->constructor->getData((isset($_POST['languageID'])) ? $_POST['languageID']
            : E()->getLanguage()->getDefault());
    }

    //todo VZ: Where is return?
    /**
     * Get raw data.
     */
    protected function getRawData() {
        $this->setBuilder(new JSONBuilder());
        $this->setDataDescription($this->createDataDescription());
        $this->createPager();

        //$this->applyUserFilter();
        //$this->applyUserSort();

        $this->setData($this->createData());

        if ($this->pager) $this->getBuilder()->setPager($this->pager);
    }

    //todo VZ: What to add?
    /**
     * Add.
     */
    protected function add() {
        $this->setType(self::COMPONENT_TYPE_FORM_ADD);
        $this->setBuilder($this->createBuilder());
        $this->setDataDescription($this->createDataDescription());
        $this->setData(new Data());
        $toolbars = $this->createToolbar();
        if (!empty($toolbars)) {
            $this->addToolbar($toolbars);
        }
        $this->js = $this->buildJS();
    }

    /**
     * Edit.
     * Get information about selected field to modal form, so user can edit it.
     */
    protected function edit() {
        $this->setType(self::COMPONENT_TYPE_FORM_ALTER);
        $this->setBuilder($this->createBuilder());

        $fieldID = $this->getStateParams();
        if (!is_array($fieldID) || !$fieldID[0]) {
            throw new SystemException('ERR_WRONG_FIELD_ID');
        }
        $fieldName = $this->getFieldInfoByIndex($fieldID[0]);
        //Get field translations

        $this->setDataDescription($this->createDataDescription());
        $this->setData(new Data());

        //Load information about field type.
        $fieldsInfo = $this->dbh->getColumnsInfo(FormConstructor::getDatabase() . '.form_' . $this->getParam('form_id'));
        $fieldInfo = $fieldsInfo[strtolower($fieldName)];
        $fieldInfo = FieldDescription::convertType($fieldInfo['type'], $fieldName, $fieldInfo['length']);
        $fieldInfo = 'FIELD_TYPE_' . strtoupper($fieldInfo);
        //Destroy unused variables.

        //Create field FIELD_TYPE.
        $f = new Field('field_type');
        for ($i = 0, $l = sizeof(E()->getLanguage()->getLanguages()); $i < $l; $i++) {
            $f->setRowData($i, $this->translate($fieldInfo));
        }
        $this->getData()->addField($f);
        $this->getDataDescription()->getFieldDescriptionByName('field_type')->setProperty('tabName', 'TXT_PROPERTIES');
        $this->getDataDescription()->getFieldDescriptionByName('ltag_name')->setType(FieldDescription::FIELD_TYPE_HIDDEN);

        $result = $this->dbh->select(
            'SELECT * FROM share_lang_tags lt
                    LEFT JOIN share_lang_tags_translation ltt ON lt.ltag_id=ltt.ltag_id
                    WHERE lt.ltag_name = %s', $ltagName = 'FIELD_' . strtoupper($fieldName));

        if (!is_array($result)) {
            $result = array();
            $ltagID = $this->dbh->modify(QAL::INSERT, 'share_lang_tags', array('ltag_name' => $ltagName));
            foreach (array_keys(E()->getLanguage()->getLanguages()) as $langID) {
                $this->dbh->modify(QAL::INSERT, 'share_lang_tags_translation', array('lang_id' => $langID, 'ltag_id' => $ltagID, 'ltag_value_rtf' => $ltagName));
                $result[] = array('ltag_id' => $ltagID, 'lang_id' => $langID, 'ltag_value_rtf' => $ltagName, 'ltag_name' => $ltagName);
            }
        }

        unset($fieldsInfo, $fieldName, $fieldID);

        if (is_array($result)) {
            //Load field translations, translation tag and ID.
            $this->getData()->load($result);
        }

        //Create toolbar and JS.
        $toolbars = $this->createToolbar();
        if (!empty($toolbars)) {
            $this->addToolbar($toolbars);
        }
        $this->js = $this->buildJS();
        //inspect($this->getDataDescription());
    }

    /**
     * @copydoc DataSet::main
     */
    protected function main() {
        $this->setBuilder($this->createBuilder());
        $this->setDataDescription($this->createDataDescription());
        $this->createPager();
        $this->setData(new Data());
        $toolbars = $this->createToolbar();
        if (!empty($toolbars)) {
            $this->addToolbar($toolbars);
        }
        $this->js = $this->buildJS();
        //$this->addTranslation('TXT_FILTER', 'BTN_APPLY_FILTER', 'TXT_RESET_FILTER', 'TXT_FILTER_SIGN_BETWEEN', 'TXT_FILTER_SIGN_CONTAINS', 'TXT_FILTER_SIGN_NOT_CONTAINS');
    }

    //todo VZ: What up?
    /**
     * Up.
     */
    protected function up() {
        list($fieldIndex) = $this->getStateParams();
        $this->constructor->changeOrder(Grid::DIR_UP, $fieldIndex);
        $b = new JSONCustomBuilder();
        $b->setProperties(array(
            'result' => true,
            'dir' => Grid::DIR_UP
        ));
        $this->setBuilder($b);
    }

    //todo VZ: What down?
    /**
     * Down.
     */
    protected function down() {
        list($fieldIndex) = $this->getStateParams();
        $this->constructor->changeOrder(Grid::DIR_DOWN, $fieldIndex);
        $b = new JSONCustomBuilder();
        $b->setProperties(array(
            'result' => true,
            'dir' => Grid::DIR_DOWN
        ));
        $this->setBuilder($b);
    }

    /**
     * Edit selector.
     */
    protected function editSelector() {
        list($fieldIndex) = $this->getStateParams();
        E()->getRequest()->shiftPath(2);
        $fieldInfo = $this->getFieldInfoByIndex($fieldIndex, true);
        $fieldName = key($fieldInfo);
        $tableName = current($fieldInfo);
        if (!strpos($fieldName, '_multi')) {
            $tableName = $tableName['key']['tableName'];
        } else {
            $tableName = FormConstructor::getDatabase() . '.' . $fieldName . '_values';
        }

        $this->SVEditor = $this->document->componentManager->createComponent('form', 'forms', 'SelectorValuesEditor', array('table_name' => $tableName));
        $this->SVEditor->run();

    }

    //todo VZ: What delete?
    /**
     * Delete.
     */
    protected function delete() {
        list($fieldIndex) = $this->getStateParams();
        $this->constructor->delete($this->getFieldInfoByIndex($fieldIndex));

        $this->setBuilder(new JSONCustomBuilder());
    }

    protected function save() {
        if ($_POST['componentAction'] != 'edit') {
            $this->constructor->save($_POST);
        } else {
            $this->saveField();
        }

        $c = E()->getCache();
        if ($c->isEnabled()) {
            $c->dispose(Cache::TRANSLATIONS_KEY);
        }

        $this->setBuilder(new JSONCustomBuilder());
    }

    /**
     * Save field.
     */
    protected function saveField() {
        $tableName = 'share_lang_tags_translation';
        if (isset($_POST) && isset($_POST[$tableName]) && isset($_POST['share_lang_tags'])) {
            $translations = $_POST[$tableName];
            if (is_array($translations)) {
                foreach ($translations as $value) {
                    $this->dbh->modify(
                        QAL::UPDATE,
                        $tableName,
                        array('ltag_value_rtf' => $value['ltag_value_rtf']),
                        array('ltag_id' => $_POST['share_lang_tags'], 'lang_id' => $value['lang_id']));
                }
            }

        }
    }

    /**
     * @copydoc DataSet::build
     */
    public function build() {
        if ($this->getState() == 'editSelector') {
            $result = $this->SVEditor->build();
        } else {
            $result = parent::build();
        }

        return $result;
    }

    /**
     * Get field information by his ID
     *
     * @param int $fieldIndex Field ID.
     * @param bool $asArray Return as an array?
     * @return array
     *
     * @throws SystemException 'ERR_BAD_REQUEST'
     */
    private function getFieldInfoByIndex($fieldIndex, $asArray = false) {
        if ($fieldIndex == 1) {
            throw new SystemException('ERR_BAD_REQUEST', SystemException::ERR_WARNING);
        }
        $colsInfo = $this->dbh->getColumnsInfo($this->constructor->getTableName());
        $cols = array_keys($colsInfo);
        if (!isset($cols[$fieldIndex - 1])) {
            throw new SystemException('ERR_BAD_REQUEST', SystemException::ERR_WARNING);
        }

        if ($asArray) {
            return array(
                $cols[$fieldIndex - 1] => $colsInfo[$cols[$fieldIndex - 1]]
            );
        } else {
            return $cols[$fieldIndex - 1];
        }
    }

}