<?php
/**
 * @file
 * Select
 *
 * It contains the definition to:
 * @code
class Select;
@endcode
 *
 * @author dr.Pavka
 * @copyright Energine 2006
 *
 * @version 1.0.0
 */
namespace Energine\share\gears;
/**
 * Drop-down list.
 *
 * @code
class Select;
@endcode
 */
class Select extends Control {
    /**
     * List items.
     * @var array $items
     */
    private $items;

    /**
     * @param string $id Control ID.
     * @param string|bool $action Action name.
     * @param string|bool $title Control title.
     */
    public function __construct($id, $action = false, $title = false) {
        parent::__construct($id);
        $this->type = 'select';
        if ($title)   $this->setAttribute('title',   $title);
        if ($action)  $this->setAttribute('action',  $action);
    }

    /**
     * @copydoc Control::loadFromXml
     */
    public function loadFromXml(\SimpleXMLElement $description) {
        parent::loadFromXml($description);
        if($description->options){
        	foreach ($description->options->option as $item){
                $this->addItem((string)$item['id'],(string)$item);		
        	}
        }
    }    

    /**
     * Add item.
     *
     * @param string $id Item ID.
     * @param string $value Item value.
     * @param array $itemProperties Item properties in form @code array($attr_name => attr_value) @endcode.
     */
    public function addItem($id, $value, $itemProperties = array()) {
    	$this->items[$id] = array(
    	   'value' => DBWorker::_translate($value),
    	   'properties' => $itemProperties
    	);
    }

    /**
     * @copydoc Control::build
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
     * Remove item from list.
     *
     * @param string $id Item ID.
     */
    public function removeItem($id) {
    	if(isset($this->items[$id])){
    		unset($this->items[$id]);
    	}
    }

    /**
     * Get item.
     *
     * @param string $id
     * @return array
     */
    public function getItem($id) {
    	$result = null;
    	if(isset($this->items[$id])){
    	   	$result = $this->items[$id];
    	}
    	return $result;
    }
}
