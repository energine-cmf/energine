<?php
/**
 * @file
 * FormResults
 *
 * It contains the definition to:
 * @code
class FormResults;
@endcode
 *
 * @author d.pavka
 * @copyright d.pavka@gmail.com
 *
 * @version 1.0.0
 */
namespace Energine\forms\components;
use Energine\share\components\Grid, Energine\forms\gears\FormConstructor, Energine\share\gears\QAL, Energine\share\gears\Field, Energine\share\gears\FieldDescription, Energine\share\gears\SimpleBuilder, Energine\share\gears\Data, Energine\share\gears\DataDescription;
/**
 * Form results.
 *
 * @code
class FormResults;
@endcode
 */
class FormResults extends Grid {
    /**
     * Form ID.
     * @var int $formID
     */
    private $formID;

    /**
     * @copydoc Grid::__construct
     */
    public function __construct($name, $module, array $params = null) {
        parent::__construct($name, $module, $params);
        //Якщо ідентифікатор форми вказаний невірно або не вказаний, то не вивалювати помилку, а красиво показати.
        if (!$this->formID = $this->getParam('form_id'))
            $this->formID = false;
        else
            $this->setTableName(FormConstructor::getDatabase() . '.form_' . $this->formID);

        $this->setOrder(array('pk_id' => QAL::DESC));
    }

    /**
     * @copydoc Grid::defineParams
     */
    protected function defineParams() {
        return array_merge(
            parent::defineParams(),
            array(
                 'form_id' => false
            )
        );
    }

    /**
     * @copydoc Grid::loadDataDescription
     */
    protected function loadDataDescription() {
        $result = parent::loadDataDescription();
        //Якщо у конфігі вказано обмеження на кількість полів, які мають відображатися у Grid'і (states: main, getRawData), то застосувати його.
        //Інакше відобразити всі поля.
        if (in_array($this->getState(), array('main', 'getRawData'))) {
            $numFields = $this->getConfigValue('forms.result_num_fields', 6);
            if (count($result) > $numFields)
                $result = array_splice($result, 0, $numFields);
        }

        return $result;
    }

    /**
     * @copydoc Grid::main
     */
    protected function main() {
        if (!$this->formID)
            $this->returnEmptyRecordset();
        else {
            parent::main();
        }
    }

    /**
     * @copydoc Grid::createDataDescription
     */
    protected function createDataDescription(){
        $result = parent::createDataDescription();
        if(in_array($this->getState(), array('main', 'getRawData') )){
            $result->getFieldDescriptionByName('pk_id')->setType(FieldDescription::FIELD_TYPE_INT);
        }
        return $result;
    }

    /**
     * Return empty recordset.
     */
    private function returnEmptyRecordset() {
        //Тип форми змінюється для того, щоб xslt опрацював помилку не в Grid'і.
        $this->setType(self::COMPONENT_TYPE_FORM_ALTER);
        $this->removeProperty('exttype');

        $f = new Field('error_msg');
        $fd = new FieldDescription('error_msg');
        $fd->setType(FieldDescription::FIELD_TYPE_STRING);
        $fd->setMode(FieldDescription::FIELD_MODE_READ);
        $f->setData($this->translate('ERROR_NO_FORM'), true);

        $d = new Data();
        $dd = new DataDescription();
        $d->addField($f);
        $dd->addFieldDescription($fd);

        $this->setData($d);
        $this->setDataDescription($dd);

        $this->setBuilder(new SimpleBuilder());
    }
}