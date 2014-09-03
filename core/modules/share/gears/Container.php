<?php
/**
 * @file
 * Container
 *
 * It contains the definition to:
 * @code
class Container;
@endcode
 *
 * @author dr.Pavka
 * @copyright Energine 2006
 *
 * @version 1.0.0
 */


/**
 * Drop-down menu.
 *
 * @code
class Container;
@endcode
 */
class Container extends Control {
    /**
     * Controls.
     * @var array $controls
     */
    private $controls = array();

    /**
     * @param string $id Control ID.
     * @param string|bool $action Action name.
     * @param string|bool $title Control title.
     * @param string|bool $tooltip Control tooltip.
     */
	public function __construct($id, $action = false, $title = false, $tooltip = false) {
		parent::__construct($id);
		$this->type = 'container';
        if ($action)  $this->setAttribute('action',  $action);
        if ($title)   $this->setAttribute('title',   $title);
        if ($tooltip) $this->setAttribute('tooltip', $tooltip);
	}

    /**
     * @copydoc Control::loadFromXml
     *
     * @throws SystemException 'ERR_DEV_NO_CONTROL_TYPE'
     * @throws SystemException 'ERR_DEV_NO_CONTROL_CLASS'
     */
    public function loadFromXml(\SimpleXMLElement $description) {
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

    /**
     * @copydoc Control::build
     */
    public function build() {
        parent::build();

        foreach ($this->controls as $control) {
        	$this->doc->documentElement->appendChild($this->doc->importNode($control->build(), true));
        }

        return $this->doc->documentElement;
    }

    /**
     * Attach control.
     *
     * @param Control $control
     */
    public function attachControl(Control $control) {
        $control->setIndex(arrayPush($this->controls, $control));
        $control->attach($this->getToolbar());
    }
}
