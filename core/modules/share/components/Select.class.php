<?php
/**
 * Содержит класс Select
 *
 * @package energine
 * @subpackage share
 * @author dr.Pavka
 * @copyright Energine 2006
 */

/**
 * Выпадающий список
 *
 * @package energine
 * @subpackage share
 * @author dr.Pavka
 */
class Select extends Control {

    /**
     * Элементы списка
     *
     * @access private
     * @var array
     */
    private $items;

    /**
     *
     *
     * @access public
     */
    public function __construct($id, $action = false, $title = false) {
        parent::__construct($id);
        $this->type = 'select';
        if ($title)   $this->setAttribute('title',   $title);
        if ($action)  $this->setAttribute('action',  $action);
    }
    /**
     * Перегружаем родительский метод для получения возможности загрузить значения опций 
     *
     * @param SimpleXMLElement $description
     * @return void
     * @access public
     */
    public function loadFromXml(SimpleXMLElement $description) {
        parent::loadFromXml($description);
        if($description->options){
        	foreach ($description->options->option as $item){
                $this->addItem((string)$item['id'],(string)$item);		
        	}
        }
    }    

    /**
     * Добавляет item
     * @param string 
     * @param string 
     * @param array $itemProperties array(
     *                                  $attr_name => attr_value 
     *                              )
     * @return void
     * @access public
     */
    public function addItem($id, $value, $itemProperties = array()) {
    	$this->items[$id] = array(
    	   'value' => DBWorker::_translate($value),
    	   'properties' => $itemProperties
    	);
    }
    /**
     * Переопределенный вывод елемента 
     * 
     * @return DOMNode
     * @access public 
     */
    public function build(){
        $result = parent::build();
        if(!empty($this->items)){
        	$options = $this->doc->createElement('options');
        	foreach ($this->items as $itemID=>$itemData){
        	   	$option = $this->doc->createElement('option', $itemData['value']);
        	   	$option->setAttribute('id', $itemID);
        	   	if(!empty($itemData['properties'])){
        	   		foreach ($itemData['properties'] as $key=>$value){
        	   			$option->setAttribute($key, $value);
        	   		}
        	   	}
        	   	$options->appendChild($option);
        	}
        	$result->appendChild($options);
        	
        }
        return $result;
    }

    /**
     *
     *
     * @param string $id
     * @return void
     * @access public
     */
    public function removeItem($id) {
    	if(isset($this->items[$id])){
    		unset($this->items[$id]);
    	}
    }

    /**
     *
     *
     * @param string $id
     * @return array
     * @access public
     */
    public function getItem($id) {
    	$result = null;
    	if(isset($this->items[$id])){
    	   	$result = $this->items[$id];
    	}
    	return $result;
    }
}
