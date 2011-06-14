<?php

/**
 * Содержит класс MethodConfigElement
 *
 * @package energine
 * @subpackage core
 * @author dr.Pavka
 * @copyright Energine 2007
 */


/**
 * Класс
 *
 * @package energine
 * @subpackage core
 * @author dr.Pavka
 */
class ConfigElement extends SimpleXMLElement {
    /**
     * Возвращает аттрибут приведенный к string
     *
     * @param string имя аттрибута
     * @return string
     * @access public
     */

    public function getAttribute($name) {
        foreach($this->Attributes() as $key => $val) {
            if($key == $name)
                return (string)$val;
        }
        return null;
    }

    /**
     * Возвращает значение SimpleXML узла
     *
     * @return string
     * @access public
     */

    public function getValue() {
        return (string)$this;
    }

}