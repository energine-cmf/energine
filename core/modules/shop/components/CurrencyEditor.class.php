<?php
/**
 * Содержит класс CurrencyEditor
 *
 * @package energine
 * @subpackage shop
 * @author dr.Pavka
 * @copyright ColoCall 2006
 * @version $Id$
 */

//require_once('core/modules/share/components/Grid.class.php');

/**
 * Класс предназначен для редактирования перечня валюты
 *
 * @package energine
 * @subpackage shop
 * @author dr.Pavka
 */
class CurrencyEditor extends Grid {
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
		$this->setTableName('shop_currency');
		$this->setOrderColumn('curr_order_num');
		$this->setOrder(array('curr_order_num'=>QAL::ASC));
	}

	protected function createDataDescription() {
		$result = parent::createDataDescription();
		if (in_array($this->getAction(), array('add', 'edit'))) {
			$fd = $result->getFieldDescriptionByName('curr_abbr');
			$fd->setProperty('pattern', '/[a-zA-Z]{3}/');
			$fd->setProperty('message', $this->translate('MSG_BAD_CURR_ABBR'));
			
			$fd = $result->getFieldDescriptionByName('curr_is_main')->removeProperty('nullable');
		}
		return $result;
	}

	protected function edit(){
		parent::edit();
		if($this->getData()->getFieldByName('curr_is_main')->getRowData(0)){
			$this->getDataDescription()->getFieldDescriptionByName('curr_is_main')->setMode(FieldDescription::FIELD_MODE_READ);
		}
		 
	}

	protected function saveData(){
		if(isset($_POST[$this->getTableName()]['curr_abbr'])){
			$_POST[$this->getTableName()]['curr_abbr'] = strtoupper($_POST[$this->getTableName()]['curr_abbr']);
		}
		
		if($_POST[$this->getTableName()]['curr_is_main']){
		  $this->dbh->modifyRequest('UPDATE '.$this->getTableName().' SET curr_is_main = NULL');	
		}
		else {
			$_POST[$this->getTableName()]['curr_is_main'] = '';
		}
		
		return parent::saveData();
	}
}