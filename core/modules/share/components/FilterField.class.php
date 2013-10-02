<?php
/**
 * Содержит класс FilterField
 *
 * @package energine
 * @subpackage share
 * @author andy.karpov
 * @copyright Energine 2013
 */


/**
 * Элемент управления фильтра
 *
 * @package energine
 * @subpackage share
 * @abstract
 * @author andy.karpov
 */
class FilterField extends Object {

    /**
     * Имя тега элемента
     */
    const TAG_NAME = 'field';

    /**
     * Документ
     *
     * @var DOMDocument
     */
    protected $doc;

    /**
     * Тип элемента
     *
     * @var string
     */
    protected $type = false;

    /**
     * Дополнительные атрибуты
     *
     * @var array
     */
    private $attributes = array();

    /**
     * Фильтр к которому привязан элемент управления
     *
     * @var Filter
     */
    private $filter;

    /**
     * Индекс элемента.
     * Присваивается фильтром после присоединения элемента.
     *
     * @var int
     */
    private $index = false;

    /**
     * Конструктор
     *
     * @param string $name
     */
    public function __construct($name) {
        $this->setAttribute('name', $name);
        $this->doc = new DOMDocument('1.0', 'UTF-8');
    }

    /**
     * Привязываем элемент управления к фильтру
     *
     * @param Filter
     */

    public function attach($filter) {
        $this->filter = $filter;
    }

    /**
     * Возвращает фильтр
     *
     * @return Filter
     */
    protected function getFilter() {
        return $this->filter;
    }

    /**
     * Устанавливает индекс элемента.
     * Вызывается из фильтра (Filter).
     *
     * @param int
     */
    public function setIndex($index) {
        $this->index = $index;
    }

    /**
     * Возвращает индекс элемента.
     * Вызывается из фильтра (Filter).
     *
     * @return int
     * @throws SystemException
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
     * @throws SystemException
     */
    public function loadFromXml(SimpleXMLElement $description) {

        if (!isset($description['type'])) {
            throw new SystemException('ERR_DEV_NO_CONTROL_TYPE', SystemException::ERR_DEVELOPER);
        }

        $attr = $description->attributes();

        $this->setAttribute('mode',
            FieldDescription::computeRights(
                $this->getFilter()->getComponent()->document->getRights(),
                !is_null($attr['ro_rights']) ? (int)$attr['ro_rights'] : null,
                !is_null($attr['fc_rights']) ? (int)$attr['fc_rights'] : null
            )
        );
        unset($attr['ro_rights']);
        unset($attr['fc_rights']);
        foreach ($attr as $key => $value) {
            if (isset($this->$key)) {
                $this->$key = (string)$value;
            } else {
                $this->setAttribute($key, (string)$value);
            }
        }
    }

    /**
     * Возвращает тип элемента.
     *
     * @return string
     * @throws SystemException
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
     * @param string $attrName
     * @param mixed $attrValue
     */
    public function setAttribute($attrName, $attrValue) {
        $this->attributes[$attrName] = $attrValue;
    }

    /**
     * Возвращает значение атрибута.
     *
     * @param string $attrName
     * @return mixed
     */
    public function getAttribute($attrName) {
        if (isset($this->attributes[$attrName])) {
            return $this->attributes[$attrName];
        }
        return false;
    }

    /**
     * Построение DOM элемента
     *
     * @return DOMNode
     */
    public function build() {

        $controlElem = $this->doc->createElement(self::TAG_NAME);

        foreach ($this->attributes as $attrName => $attrValue) {
            $controlElem->setAttribute($attrName, $attrValue);
        }
        $controlElem->setAttribute('type', $this->getType());
        $this->doc->appendChild($controlElem);

        return $this->doc->documentElement;
    }

    /**
     * Переводит языко-зависимые атрибуты.
     *
     * @param array перечень атрибутов для перевода
     */
    public function translate($attrs = array('title')) {
        foreach ($attrs as $attrName) {
            $attrValue = (string)$this->getAttribute($attrName);
            if ($attrValue) {
                $this->setAttribute($attrName, DBWorker::_translate($attrValue));
            }
        }
    }
}
