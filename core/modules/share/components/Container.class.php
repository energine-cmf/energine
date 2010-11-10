<?php
/**
 * Содержит класс Container
 *
 * @package energine
 * @subpackage share
 * @author dr.Pavka
 * @copyright Energine 2006
 * @version $Id$
 */


/**
 * Выпадающее меню
 *
 * @package energine
 * @subpackage share
 * @author dr.Pavka
 */
class Container extends Control {

    private $controls = array();

    /**
     * Конструктор класса
     *
     * @return void
     */
	public function __construct($id, $action = false, $title = false, $tooltip = false) {
		parent::__construct($id);
		$this->type = 'container';
        if ($action)  $this->setAttribute('action',  $action);
        if ($title)   $this->setAttribute('title',   $title);
        if ($tooltip) $this->setAttribute('tooltip', $tooltip);
	}

	public function loadFromXml(SimpleXMLElement $description) {
	    parent::loadFromXml($description);

	    foreach ($description->control as $controlDescription) {
            if (!isset($controlDescription['type'])) {
                throw new SystemException('ERR_DEV_NO_CONTROL_TYPE', SystemException::ERR_DEVELOPER);
            }

            $controlClassName = ucfirst((string)$controlDescription['type']);
            if (!class_exists($controlClassName, false)) {
            	throw new SystemException('ERR_DEV_NO_CONTROL_CLASS', SystemException::ERR_DEVELOPER, $controlClassName);
            }

            $control = new $controlClassName(
                isset($controlDescription['id']) ? (string)$controlDescription['id'] : null
            );

            $this->attachControl($control);
            $control->loadFromXml($controlDescription);
        }
    }

	public function build() {
        parent::build();

        foreach ($this->controls as $control) {
        	$this->doc->documentElement->appendChild($this->doc->importNode($control->build(), true));
        }

        return $this->doc->documentElement;
    }

    public function attachControl(Control $control) {
        $control->setIndex(arrayPush($this->controls, $control));
        $control->attach($this->getToolbar());
    }
}
