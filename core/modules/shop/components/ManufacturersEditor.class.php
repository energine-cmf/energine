<?php
/**
 * Содержит класс ManufacturersEditor
 *
 * @package energine
 * @subpackage shop
 * @author d.pavka
 * @copyright d.pavka@gmail.com
 */

 /**
  * Редактор производителей
  *
  * @package energine
  * @subpackage shop
  * @author d.pavka@gmail.com
  * @final 
  */
 final class ManufacturersEditor extends Grid {
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
        $this->setTableName('shop_producers');
        $this->setOrder(array('producer_name' => QAL::ASC));
    }
    
    protected function add(){
    	parent::add();
    	$f = $this->getDataDescription()->getFieldDescriptionByName('producer_segment');
    	$f->setProperty('nullable', 'nullable');
    	$f->setProperty('pattern', '/^[-_a-z0-9]*$/');	
    	$f->setProperty('message', $this->translate('TXT_BAD_SEGMENT_FORMAT'));
    }
        
    protected function saveData(){
        if(($this->getPreviousAction() == 'add') && (!isset($_POST[$this->getTableName()]['producer_segment']) || empty($_POST[$this->getTableName()]['producer_segment'])) ){
            $_POST[$this->getTableName()]['producer_segment'] = Translit::transliterate($_POST[$this->getTableName()]['producer_name'], '-', true);
        }
        $result = parent::saveData();

        return $result;
    }
}