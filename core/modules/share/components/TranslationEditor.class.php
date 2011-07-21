<?php
/**
 * Содержит класс TranslationEditor
 *
 * @package energine
 * @subpackage share
 * @author dr.Pavka
 * @copyright Energine 2006
 */


/**
 * Редактор переводов
 *
 * @package energine
 * @subpackage share
 * @author dr.Pavka
 */
class TranslationEditor extends Grid {
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
        $this->setTableName('share_lang_tags');
        $this->setOrder(array('ltag_name' => QAL::ASC ));
	}
	
	protected function prepare(){
		parent::prepare();
		if(in_array($this->getState(), array('add', 'edit'))){
			$this->getDataDescription()->getFieldDescriptionByName('ltag_value_rtf')->setType(FieldDescription::FIELD_TYPE_TEXT);
		}
	}

	protected function saveData(){
        //обрезаем лишние незначащие пробелы и прочее в самих тегах и в переводах
        //в переводах - сделано на случай вывода в джаваскрипт
		$_POST[$this->getTableName()]['ltag_name'] = strtoupper(trim($_POST[$this->getTableName()]['ltag_name']));
		foreach(array_keys(E()->getLanguage()->getLanguages()) as $langID){
            if(isset($_POST[$this->getTranslationTableName()][$langID]['ltag_value_rtf'])){
                $_POST[$this->getTranslationTableName()][$langID]['ltag_value_rtf'] = trim($_POST[$this->getTranslationTableName()][$langID]['ltag_value_rtf']);
            }
        }
		return parent::saveData();
	}
}