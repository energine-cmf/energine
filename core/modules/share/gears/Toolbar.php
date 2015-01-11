<?php
/**
 * @file
 * Toolbar
 *
 * It contains the definition to:
 * @code
class Toolbar;
 * @endcode
 *
 * @author dr.Pavka
 * @copyright Energine 2006
 *
 * @version 1.0.0
 */
namespace Energine\share\gears;
/**
 * Toolbar.
 *
 * @code
class Toolbar;
 * @endcode
 */
class Toolbar extends Object {
    /**
     * Tag name.
     */
    const TAG_NAME = 'toolbar';

    /**
     * Document.
     * @var \DOMDocument $doc
     */
    private $doc;

    /**
     * Set of control elements.
     * @var array $controls
     */
    private $controls = array();

    /**
     * Toolbar name.
     * @var string $name
     */
    private $name;

    /**
     * Path to the directory with images.
     * @var string $imageDir
     */
    private $imageDir;

    /**
     * Additional properties.
     * @var array $properties
     */
    private $properties = array();

    /**
     * Component that holds toolbar.
     * @var Component $component
     */
    private $component;

    /**
     * @param string $name Toolbar name.
     * @param string|bool $imageDir Path to the directory with images.
     */
    public function __construct($name, $imageDir = false) {
        $this->name = $name;
        $this->doc = new \DOMDocument('1.0', 'UTF-8');
        $this->imageDir = $imageDir;
    }

    /**
     * Return toolbar name.
     *
     * @return string
     *
     * @final
     */
    final public function getName() {
        return $this->name;
    }

    /**
     * Attach toolbar to specific component.
     *
     * @param Component $component Component.
     */
    public function attachToComponent(Component $component) {
        $this->component = $component;
    }

    /**
     * Get component that holds toolbar.
     *
     * @return Component
     */
    public function getComponent() {
        return $this->component;
    }

    //todo VZ: Second argument is not used.
    /**
     * Attach control.
     *
     * @param Control $control Control element.
     * @param Control $position Control position. If it is not set than the control will be placed at the end.
     */
    public function attachControl(Control $control, Control $position = null) {
        $control->setIndex(arrayPush($this->controls, $control));
        $control->attach($this);
    }

    /**
     * Detach control element.
     *
     * @param Control $control Control.
     *
     * @throws SystemException 'ERR_DEV_NO_CONTROL_TO_DETACH'
     */
    public function detachControl(Control $control) {
        if (!isset($this->controls[$control->getIndex()])) {
            throw new SystemException('ERR_DEV_NO_CONTROL_TO_DETACH', SystemException::ERR_DEVELOPER);
        }
        unset($this->controls[$control->getIndex()]);
    }

    /**
     * Get control by his ID.
     *
     * @param int $id Control ID.
     * @return Control
     */
    public function getControlByID($id) {
        $result = false;
        foreach ($this->controls as $control) {
            if (method_exists($control, 'getID') && $control->getID() == $id) {
                $result = $control;
                break;
            }
        }
        return $result;
    }

    /**
     * Create toolbar from XML description.
     *
     * @param \SimpleXMLElement $toolbarDescription Toolbar description.
     *
     * @throws SystemException 'ERR_DEV_NO_CONTROL_TYPE'
     */
    public function loadXML(\SimpleXMLElement $toolbarDescription) {
        if (!empty($toolbarDescription))
            foreach ($toolbarDescription->control as $controlDescription) {

                if (!isset($controlDescription['type'])) {
                    throw new SystemException('ERR_DEV_NO_CONTROL_TYPE', SystemException::ERR_DEVELOPER);
                }

                $controlClassName = ucfirst((string)$controlDescription['type']);
                if ($controlClassName == 'Togglebutton') $controlClassName = 'Switcher'; // dirty hack
                $controlClassName = 'Energine\\share\\gears\\' . $controlClassName;
                if (!class_exists($controlClassName)) {
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
     * Get all controls.
     *
     * @return array
     */
    public function getControls() {
        return $this->controls;
    }

    /**
     * Set property.
     *
     * @param string $name Property name.
     * @param mixed $value Property value.
     */
    public function setProperty($name, $value) {
        $this->properties[$name] = $value;
    }


    /**
     * Get property.
     *
     * @param string $name Property name.
     * @return mixed|null
     */
    public function getProperty($name) {
        if (isset($this->properties[$name])) {
            return $this->properties[$name];
        }
        return null;
    }

    /**
     * Build toolbar.
     *
     * @return \DOMNode
     */
    public function build() {
        $result = false;

        if (count($this->controls) > 0) {
            $toolbarElem = $this->doc->createElement(self::TAG_NAME);
            $toolbarElem->setAttribute('name', $this->name);
            if (!empty($this->properties)) {
                $props = $this->doc->createElement('properties');
                foreach ($this->properties as $propName => $propValue) {
                    $prop = $this->doc->createElement('property');
                    $prop->setAttribute('name', $propName);
                    $prop->appendChild($this->doc->createTextNode($propValue));
                    $props->appendChild($prop);
                }
                $toolbarElem->appendChild($props);
            }
            foreach ($this->controls as $control) {
                $toolbarElem->appendChild($this->doc->importNode($control->build(), true));
            }
            $this->doc->appendChild($toolbarElem);
            $result = $this->doc->documentElement;
        }

        return $result;
    }

    /**
     * Translate toolbar.
     */
    public function translate() {
        foreach ($this->controls as $control) {
            $control->translate();
        }
    }
}
