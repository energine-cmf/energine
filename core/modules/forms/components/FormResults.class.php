<?php
/**
 * Содержит класс FormConstuctor
 *
 * @package energine
 * @subpackage forms
 * @author d.pavka
 * @copyright d.pavka@gmail.com
 */

/**
 * Конструктор формы
 *
 * @package energine
 * @subpackage forms
 * @author d.pavka@gmail.com
 */
class FormResults extends Grid {
    /*
     * @var formID
     */
    private $formID;

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
        //Якщо ідентифікатор форми вказаний невірно або не вказаний, то не вивалювати помилку, а красиво показати.
        if (!$this->formID = $this->getParam('form_id'))
            $this->formID = false;
        else
            $this->setTableName($this->getConfigValue('forms.database') . '.form_' .$this->formID);

        $this->setOrder(array('pk_id' => QAL::DESC));
    }

    protected function defineParams() {
        return array_merge(
            parent::defineParams(),
            array(
                 'form_id' => false
            )
        );
    }

    protected function loadDataDescription() {
        $result = parent::loadDataDescription();
        //Якщо у конфігі вказано обмеження на кількість полів, які мають відображатися у Grid'і (states: main, getRawData), то застосувати його.
        //Інакше відобразити всі поля.
        if (in_array($this->getState(), array('main', 'getRawData'))) {
            $numFields = ($this->getConfigValue('forms.result_num_fields'))
                    ? $this->getConfigValue('forms.result_num_fields') : 6;
            if (count($result) > $numFields)
                $result = array_splice($result, 0, $numFields);
        }

        return $result;
    }

    protected function main(){
        if(!$this->formID)
            $this->returnEmptyRecordset();
        else{
            parent::main();
        }
    }

    private function returnEmptyRecordset(){
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