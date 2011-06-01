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
class FormResults extends Grid
{
    /**
     * Конструктор класса
     *
     * @param string $name
     * @param string $module
     * @param array $params
     * @access public
     */
    public function __construct($name, $module, array $params = null)
    {
        parent::__construct($name, $module, $params);
        if (!$this->getParam('form_id')) {
            throw new SystemException('ERR_BAD_FORM_ID');
        }
        $this->setTableName($this->getConfigValue('forms.database') . '.form_' .$this->getParam('form_id'));
    }

    protected function defineParams()
    {
        return array_merge(
            parent::defineParams(),
            array(
                 'form_id' => false
            )
        );
    }

    protected function loadDataDescription(){
        $result = parent::loadDataDescription();
        //Якщо у конфігі вказано обмеження на кількість полів, які мають відображатися у Grid'і (states: main, getRawData), то застосувати його.
        //Інакше відобразити всі поля.
        if(in_array($this->getState(), array('main', 'getRawData')))
            if (count($result) > $this->getConfigValue('forms.result_num_fields'))
                $result = array_splice($result, 0, $this->getConfigValue('forms.result_num_fields'));

        return $result;
    }

}