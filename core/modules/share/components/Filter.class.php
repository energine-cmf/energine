<?php
/**
 * Класс Filter
 *
 * @package energine
 * @subpackage share
 * @author andy.karpov
 * @copyright Energine 2013
 */

/**
 * Фильтры
 *
 * @package energine
 * @subpackage share
 * @author andy.karpov
 */
class Filter extends Object {

    /**
     * Имя тeга
     */
    const TAG_NAME = 'filter';

    /**
     * Документ
     *
     * @var DOMDocument
     */
    private $doc;

    /**
     * Набор полей
     *
     * @var FilterField[]
     */
    private $fields = array();

    /**
     * Дополнительные свойства фильтра
     *
     * @var array
     */
    private $properties = array();

    /**
     * Присоединяет фильтр к компоненту
     *
     * @var Component
     */
    private $component;

    /**
     * Конструктор
     *
     */
    public function __construct() {
        $this->doc = new DOMDocument('1.0', 'UTF-8');
    }

    /**
     * Привязывает фильтр к компоненту
     *
     * @param Component
     */

    public function attachToComponent(Component $component) {
        $this->component = $component;
    }

    /**
     * Возвращает компонент к которому привязан фильтр
     *
     * @return Component
     */

    public function getComponent() {
        return $this->component;
    }

    /**
     * Присоединение филда к фильтру
     *
     * @param FilterField $field
     */
    public function attachField(FilterField $field) {
        $field->setIndex(arrayPush($this->fields, $field));
        $field->attach($this);
    }

    /**
     * Отсоединение филда от фильтра
     *
     * @param FilterField $field
     * @throws SystemException
     */
    public function detachField(FilterField $field) {
        if (!isset($this->fields[$field->getIndex()])) {
            throw new SystemException('ERR_DEV_NO_CONTROL_TO_DETACH', SystemException::ERR_DEVELOPER);
        }
        unset($this->fields[$field->getIndex()]);
    }

    /**
     * Построение фильтра по XML-описанию
     *
     * @param SimpleXMLElement $filterDescription
     * @return mixed
     * @throws Exception|SystemException
     */
    public function loadXML(SimpleXMLElement $filterDescription) {
        if(!empty($filterDescription))
            foreach ($filterDescription->field as $fieldDescription) {
                if (!isset($fieldDescription['type'])) {
                    throw new SystemException('ERR_DEV_NO_CONTROL_TYPE', SystemException::ERR_DEVELOPER);
                }

                $field = new FilterField(
                    isset($fieldDescription['name']) ? (string)$fieldDescription['name'] : null
                );

                $this->attachField($field);
                $field->loadFromXml($fieldDescription);
            }
    }

    /**
     * Возвращает набор элементов управления
     *
     * @return array
     */
    public function getFields() {
        return $this->fields;
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
     * @return array|null
     */
    public function getProperty($name) {
        if (isset($this->properties[$name])) {
            return $this->properties[$name];
        }
        return null;
    }

    /**
     * Построение DOM фильтра
     *
     * @return DOMNode
     */
    public function build() {
        $result = false;

        if (count($this->fields) > 0) {
            $filterElem = $this->doc->createElement(self::TAG_NAME);
            if (!empty($this->properties)) {
                $props = $this->doc->createElement('properties');
                foreach ($this->properties as $propName => $propValue) {
                    $prop = $this->doc->createElement('property');
                    $prop->setAttribute('name', $propName);
                    $prop->appendChild($this->doc->createTextNode($propValue));
                    $props->appendChild($prop);
                }
                $filterElem->appendChild($props);
            }
            foreach ($this->fields as $field) {
                $filterElem->appendChild($this->doc->importNode($field->build(), true));
            }
            $this->doc->appendChild($filterElem);
            $result = $this->doc->documentElement;
        }

        return $result;
    }

    /**
     * Переводит все поля фильтра
     *
     */
    public function translate() {
        foreach ($this->fields as $field) {
            $field->translate();
        }
    }
}
