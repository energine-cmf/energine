<?php
/**
 * Содержит класс Switcher
 *
 * @package energine
 * @subpackage share
 * @author dr.Pavka
 * @copyright Energine 2006
 */

/**
 * Переключатель
 *
 * @package energine
 * @subpackage share
 * @author dr.Pavka
 */
class Switcher extends Button {
    /**
     * Состояние переключателя
     * 
     * @var boolean
     * @access private
     */
	private $state = false;

    /**
     * @param string $id
     * @param bool $action
     * @param bool $image
     * @param bool $title
     * @param bool $tooltip
     */
    public function __construct($id, $action = false, $image = false, $title = false, $tooltip = false) {
        parent::__construct($id, $action, $image, $title, $tooltip);
 
        $this->type = 'switcher';
    }
    /**
     * Возвращает состояние переключателя
     * 
     * @access public
     * @return boolean
     */
    public function getState(){
    	return $this->state;
    }
    /**
     * Устанавливает состояние переключателя
     * 
     * @param boolean
     * @access public
     * @return void
     */
    public function setState($state){
        $this->state = (bool)$state;
    }
    /**
     * Переключает состояние
     * 
     * @access public
     * @return boolean
     */
    public function toggle(){
    	return ($this->state = !$this->state);
    }

    /**
     * @return DOMNode
     */
    public function build(){
    	$this->setAttribute('state', (int)$this->state);
    	return parent::build();
    	
    }
    
    
}
