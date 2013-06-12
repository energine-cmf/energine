<?php
/**
 * Содержит класс JSONCustomBuilder
 *
 * @package energine
 * @subpackage kernel
 * @author d.pavka
 * @copyright d.pavka@gmail.com
 */

 /**
  * Упрощенный JSON билдер
  *
  * @package energine
  * @subpackage kernel
  * @author d.pavka@gmail.com
  */
 
class JSONCustomBuilder extends Object implements IBuilder{
    /**
     * Перечень дополнительных свойств
     * @var array
     */
    public $properties = array();

    /**
     * @return bool
     */
    public function build() {
        if(!isset($this->properties['result'])){
            $this->properties['result'] = true;
        }

        if(!isset($this->properties['mode'])){
            $this->properties['mode'] = QAL::SELECT;
        }

        return true;
    }

    /**
     * Добавляем свойство
     * @param $propName
     * @param $propValue
     * @return $this
     */
    public function setProperty($propName, $propValue){
        $this->properties[$propName] = $propValue;
        return $this;
    }

    /**
     * Добавляем сразу несколько свойств
     * @param array $properties
     * @return $this
     */
    public function setProperties(array $properties){
        foreach($properties as $propName => $propValue){
            $this->setProperty($propName, $propValue);
        }
        return $this;
    }

    /**
     * Возвращение результата
     * @return string
     */
    public function getResult() {
        return json_encode($this->properties, JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP);
    }
}
