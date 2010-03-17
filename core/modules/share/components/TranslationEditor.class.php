<?php
/**
 * Содержит класс TranslationEditor
 *
 * @package energine
 * @subpackage share
 * @author dr.Pavka
 * @copyright Energine 2006
 * @version $Id$
 */

//require_once('core/modules/share/components/Grid.class.php');

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
     * @param Document $document
     * @param array $params
     * @access public
     */
	public function __construct($name, $module, Document $document,  array $params = null) {
        parent::__construct($name, $module, $document,  $params);
        $this->setTableName('share_lang_tags');
        $this->setOrder('ltag_name', QAL::ASC );
	}
	
	protected function prepare(){
		parent::prepare();
		if(in_array($this->getAction(), array('add', 'edit'))){
			$this->getDataDescription()->getFieldDescriptionByName('ltag_value_rtf')->setType(FieldDescription::FIELD_TYPE_TEXT);
		}
	}

	protected function saveData(){
		$_POST[$this->getTableName()]['ltag_name'] = strtoupper($_POST[$this->getTableName()]['ltag_name']);
		return parent::saveData();
	}
}