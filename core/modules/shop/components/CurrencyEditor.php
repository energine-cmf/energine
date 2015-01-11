<?php
/**
 * Содержит класс CurrencyEditor
 *
 * @package energine
 * @subpackage shop
 * @author Andrii A
 * @copyright eggmengroup.com
 */

/**
 * Класс предназначен для редактирования перечня валюты
 *
 * @package energine
 * @subpackage shop
 * @author Andrii A
 */
class CurrencyEditor extends Grid {
    /**
     * Конструктор класса
     *
     * @param string $name
     * @param string $module
     * @param array $params
     */
    public function __construct($name, $module, array $params = null) {
		parent::__construct($name, $module, $params);
		$this->setTableName('shop_currency');
	}

	protected function createDataDescription() {
		$result = parent::createDataDescription();
		if (in_array($this->getAction(), array('add', 'edit'))) {
			$fd = $result->getFieldDescriptionByName('curr_abbr');
			$fd->setProperty('pattern', '/[a-zA-Z]{3}/');
			$fd->setProperty('message', $this->translate('MSG_BAD_CURR_ABBR'));
		}
		return $result;
	}

	protected function edit() {
		parent::edit();
		if($this->getData()->getFieldByName('curr_is_main')->getRowData(0)){
			$this->getDataDescription()->getFieldDescriptionByName('curr_is_main')->setMode(FieldDescription::FIELD_MODE_READ);
		}
		 
	}

	protected function saveData() {
		if(isset($_POST[$this->getTableName()]['curr_abbr'])){
			$_POST[$this->getTableName()]['curr_abbr'] = strtoupper($_POST[$this->getTableName()]['curr_abbr']);
		}
		
		if($_POST[$this->getTableName()]['curr_is_main']){
		  $this->dbh->modifyRequest('UPDATE '.$this->getTableName().' SET curr_is_main = 0');
		}
		else {
			$_POST[$this->getTableName()]['curr_is_main'] = '';
		}
		
		return parent::saveData();
	}
}