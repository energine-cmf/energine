<?php
/**
 * Содержит класс JSONPCustomBuilder
 *
 * @package energine
 * @subpackage kernel
 * @author andy.karpov
 */

/**
 * JSONP билдер
 *
 * @package energine
 * @subpackage kernel
 * @author andy.karpov@gmail.com
 */

class JSONPCustomBuilder extends JSONCustomBuilder {

    public $callback = 'undefined';

    /**
     * Добавляем свойство
     * @param $propName
     * @param $propValue
     * @return $this
     */
    public function setCallback($callback) {
        $this->callback = (string) $callback;
        return $this;
    }

    /**
     * Возвращение результата
     * @return string
     */
    public function getResult() {
        return
            $this->callback .
            '(' .
            json_encode($this->properties, JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP) .
            ');'
        ;
    }
}
