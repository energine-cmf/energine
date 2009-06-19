<?php
/**
 * Класс Toolbar
 *
 * @package energine
 * @subpackage share
 * @author dr.Pavka
 * @copyright Energine 2006
 * @version $Id$
 */

//require_once('core/framework/DBWorker.class.php');
//require_once('core/modules/share/components/Container.class.php');
//require_once('core/modules/share/components/Link.class.php');
//require_once('core/modules/share/components/Button.class.php');
//require_once('core/modules/share/components/Submit.class.php');
//require_once('core/modules/share/components/Separator.class.php');
//require_once('core/modules/share/components/Switcher.class.php');
//require_once('core/modules/share/components/Select.class.php');

/**
 * Панель инструментов
 *
 * @package energine
 * @subpackage share
 * @author dr.Pavka
 */
class Toolbar extends Object {
    /**
     * Имя тeга
     */
    const TAG_NAME = 'toolbar';

    /**
     * Документ
     *
     * @var DOMDocument
     * @access private
     */
    private $doc;

    /**
     * Набор элементов управления
     *
     * @access private
     * @var array
     */
    private $controls = array();

    /**
     * Имя панели инструментов
     *
     * @access private
     * @var string
     */
    private $name;

    /**
     * Путь к директории содержащей рисунки
     *
     * @access private
     * @var string
     */
    private $imageDir;

    /**
     * Дополнительные свойства панели инструментов
     *
     * @var array
     * @access private
     */
    private $properties = array();

    /**
     * Присоединяет панель управления к компоненту
     *
     * @var Component
     * @access private
     */
    private $component;

    /**
     * Конструктор
     *
     * @param string $name имя тулбара
     * @param string $imageDir путь к директории содержащей рисунки
     * @param string $module
     * @access public
     */
    public function __construct($name, $imageDir = false) {
        parent::__construct();
        $this->name = $name;
        $this->doc = new DOMDocument('1.0', 'UTF-8');
        $this->imageDir = $imageDir;
    }

    /**
     * Привязывает панель управления к компоненту
     *
     * @param Component
     * @return void
     * @access public
     */

    public function attachToComponent(Component $component) {
        $this->component = $component;
    }

    /**
     * Возвращает компонент к которому привязана панель управления
     *
     * @return Component
     * @access public
     */

    public function getComponent() {
        return $this->component;
    }

    /**
     * Присоединение элемента управления к панели
     *
     * @param Control $control
     * @param Control $position если не указан, добавляем контрол в конец тулбара, если указан, то он добавляется в указанное место
     * @return void
     * @access public
     */
    public function attachControl(Control $control, Control $position = null) {
        $control->setIndex(arrayPush($this->controls, $control));
        $control->attach($this);
    }

    /**
     * Отсоединение элемента управления от панели
     *
     * @param Control $control
     * @return void
     * @access public
     */
    public function detachControl(Control $control) {
        if (!isset($this->controls[$control->getIndex()])) {
        	throw new SystemException('ERR_DEV_NO_CONTROL_TO_DETACH', SystemException::ERR_DEVELOPER);
        }
        unset($this->controls[$control->getIndex()]);
    }

    /**
     * Получение элемента управления по его идентификатору
     *
     * @param int $id
     * @return Control
     * @access public
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
     * Построение панели инструментов по XML-описанию
     *
     * @param SimpleXMLElement $toolbarDescription
     * @return boolean
     * @access public
     */
    public function loadXML(SimpleXMLElement $toolbarDescription) {
        if(!empty($toolbarDescription))
        foreach ($toolbarDescription->control as $controlDescription) {
            if (!isset($controlDescription['type'])) {
                throw new SystemException('ERR_DEV_NO_CONTROL_TYPE', SystemException::ERR_DEVELOPER);
            }

            $controlClassName = ucfirst((string)$controlDescription['type']);
            if ($controlClassName == 'Togglebutton') $controlClassName = 'Switcher'; // dirty hack
            if (!class_exists($controlClassName, false)) {
            	//throw new SystemException('ERR_DEV_NO_CONTROL_CLASS', SystemException::ERR_DEVELOPER, $controlClassName);
            }

            $control = new $controlClassName(
                isset($controlDescription['id']) ? (string)$controlDescription['id'] : null
            );

            $this->attachControl($control);
            $control->loadFromXml($controlDescription);

        }
    }

    /**
     * Возвращает набор элементов управления
     *
     * @return array
     * @access public
     */
    public function getControls() {
        return $this->controls;
    }

    /**
     * Enter description here...
     *
     * @param string $name
     * @param mixed $value
     */
    public function setProperty($name, $value) {
        $this->properties[$name] = $value;
    }

    /**
     * Enter description here...
     *
     * @param string $name
     * @return mixed
     */
    public function getProperty($name) {
        if (isset($this->properties[$name])) {
            return $this->properties[$name];
        }
    }

    /**
     * Построение панели инструментов
     *
     * @return DOMNode
     * @access public
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
     * Переводит все элементы управления
     *
     * @return void
     * @access public
     */
    public function translate() {
        foreach ($this->controls as $control) {
            $control->translate();
        }
    }
}
