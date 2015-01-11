<?php
/**
 * @file
 * Switcher
 *
 * It contains the definition to:
 * @code
class Switcher;
@endcode
 *
 * @author dr.Pavka
 * @copyright Energine 2006
 *
 * @version 1.0.0
 */
namespace Energine\share\gears;
/**
 * Switcher control element.
 *
 * @code
class Switcher;
@endcode
 */
class Switcher extends Button {
    /**
     * Switcher state.
     * @var boolean $state
     */
	private $state = false;

    /**
     * @copydoc Button::__construct
     */
    public function __construct($id, $action = false, $image = false, $title = false, $tooltip = false) {
        parent::__construct($id, $action, $image, $title, $tooltip);
        $this->type = 'switcher';
    }

    /**
     * Get state.
     *
     * @return boolean
     */
    public function getState(){
    	return $this->state;
    }

    /**
     * Set state.
     *
     * @param boolean $state New switcher state.
     */
    public function setState($state){
        $this->state = (bool)$state;
    }

    /**
     * Toggle switcher.
     * 
     * @return boolean
     */
    public function toggle(){
    	return ($this->state = !$this->state);
    }

    /**
     * @copydoc Control::build
     */
    public function build(){
    	$this->setAttribute('state', (int)$this->state);
    	return parent::build();
    }
}
