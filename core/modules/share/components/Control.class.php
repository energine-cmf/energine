<?php
/**
 * Содержит класс Control
 *
 * @package energine
 * @subpackage share
 * @author dr.Pavka
 * @copyright Energine 2006
 */


/**
 * Элемент управления панели инструментов
 *
 * @package energine
 * @subpackage share
 * @abstract
 * @author dr.Pavka
 */
abstract class Control extends Object {
    /**
     * Имя тега элемента
     */
    const TAG_NAME = 'control';

    /**
     * Документ
     *
     * @var DOMDocument
     * @access protected
     */
    protected $doc;

    /**
     * Тип элемента
     *
     * @access protected
     * @var string
     */
    protected $type = false;

    /**
     * Доступность элемента
     *
     * @access private
     * @var boolean
     */
    private $disabled = false;

    /**
     * Дополнительные атрибуты
     *
     * @var array
     * @access private
     */
    private $attributes = array();

    /**
     * Панель управления к которому привязан элемент управления
     *
     * @var ToolBar
     * @access private
     */
    private $toolbar;

    /**
     * Индекс элемента.
     * Присваивается панелью инструментов после присоединения элемента.
     *
     * @var int
     * @access private
     */
    private $index = false;

    /**
     * Конструктор
     *
     * @param string $id
     * @param string $action
     * @param string $image
     * @param string $title
     * @param string $tooltip
     * @access public
     */
    public function __construct($id) {
        $this->setAttribute('id', $id);
        $this->doc = new DOMDocument('1.0', 'UTF-8');
    }

    /**
     * Привязываем элемент управления к панели управления
     *
     * @param Toolbar
     * @return void
     * @access public
     */

    public function attach($toolbar) {
        $this->toolbar = $toolbar;
    }

    /**
     * Возвращает панель управления
     *
     * @return Toolbar
     * @access protected
     */

    protected function getToolbar() {
        return $this->toolbar;
    }

    /**
     * Устанавливает индекс элемента.
     * Вызывается из панели инструментов (Toolbar).
     *
     * @param int
     * @return void
     * @access public
     */
    public function setIndex($index) {
        $this->index = $index;
    }

    /**
     * Возвращает индекс элемента.
     * Вызывается из панели инструментов (Toolbar).
     *
     * @return int
     * @access public
     */
    public function getIndex() {
        if ($this->index === false) {
            throw new SystemException('ERR_DEV_NO_CONTROL_INDEX', SystemException::ERR_DEVELOPER);
        }
        return $this->index;
    }

    /**
     * Загрузка элемента из XML-описания.
     *
     * @param SimpleXMLElement $description
     * @return void
     * @access public
     */
    public function loadFromXml(SimpleXMLElement $description) {
        if (!isset($description['type'])) {
            throw new SystemException('ERR_DEV_NO_CONTROL_TYPE', SystemException::ERR_DEVELOPER);
        }

        $attr = $description->attributes();

        $this->setAttribute('mode', 
            FieldDescription::computeRights(
                $this->getToolbar()->getComponent()->document->getRights(), 
                !is_null($attr['ro_rights'])?(int)$attr['ro_rights']:null, 
                !is_null($attr['fc_rights'])?(int)$attr['fc_rights']:null
            )
        );
        unset($attr['ro_rights']);
        unset($attr['fc_rights']);
        foreach ($attr as $key => $value) {
            if (isset($this->$key)) {
                $this->$key = (string)$value;
            }
            else {
                $this->setAttribute($key, (string)$value);
            }
        }
    }

    /**
     * Отключает элемент (делает его недоступным).
     *
     * @return void
     * @access public
     */
    public function disable() {
        $this->disabled = true;
    }

    /**
     * Включает элемент.
     *
     * @return void
     * @access public
     */
    public function enable() {
        $this->disabled = false;
    }

    /**
     * Возвращает тип элемента.
     *
     * @return string
     * @access public
     */
    public function getType() {
        if (!$this->type) {
            throw new SystemException('ERR_DEV_NO_CONTROL_TYPE', SystemException::ERR_DEVELOPER);
        }
        return $this->type;
    }

    /**
     * Устанавливает значение атрибута.
     *
     * @param string
     * @param mixed
     * @return void
     * @access public
     */

    public function setAttribute($attrName, $attrValue) {
        $this->attributes[$attrName] = $attrValue;
    }

    /**
     * Возвращает значение атрибута.
     *
     * @param string
     * @return mixed
     * @access public
     */
    public function getAttribute($attrName) {
        if (isset($this->attributes[$attrName])) {
            return $this->attributes[$attrName];
        }
        return false;
    }
    
    /**
     * Возвращает идентификатор
     *
     * @return string
     * @access public
     */
    public function getID() {
        return $this->getAttribute('id');
    }

    /**
     * Построение элемента управления.
     *
     * @return DOMNode
     * @access public
     */
    public function build() {
        $controlElem = $this->doc->createElement(self::TAG_NAME);
        foreach ($this->attributes as $attrName => $attrValue) {
            $controlElem->setAttribute($attrName, $attrValue);
        }
        if ($this->disabled) {
            $controlElem->setAttribute('disabled', 'disabled');
        }
        $controlElem->setAttribute('type', $this->getType());
        $this->doc->appendChild($controlElem);

        return $this->doc->documentElement;
    }

    /**
     * Переводит языко-зависимые атрибуты.
     *
     * @param array перечень атрибутов дялш перевода
     * @return type
     * @access public
     */
    public function translate($attrs = array('title', 'tooltip')) {
        foreach ($attrs as $attrName) {
            $attrValue = (string)$this->getAttribute($attrName);
            if ($attrValue) {
                $this->setAttribute($attrName, DBWorker::_translate($attrValue));
            }
        }
    }
}
