<?php
/**
 * @file
 * JSONCustomBuilder.
 *
 * It contains the definition to:
 * @code
class JSONCustomBuilder;
@endcode
 *
 * @author d.pavka
 * @copyright d.pavka@gmail.com
 *
 * @version 1.0.0
 */
namespace Energine\share\gears;
 /**
  * Custom JSON builder.
  *
  * This is simplified JSON builder.
  *
  * @code
 class JSONCustomBuilder;
 @endcode
  */
 
class JSONCustomBuilder extends Object implements IBuilder{
    /**
     * Set of the additional properties.
     * @var array $properties
     */
    public $properties = array();

    //todo VZ: Why only true is returned?
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
     * Set property.
     * @param string $propName Property name.
     * @param mixed $propValue Property value.
     * @return JSONCustomBuilder
     */
    public function setProperty($propName, $propValue){
        $this->properties[$propName] = $propValue;
        return $this;
    }

    /**
     * Set multiple properties.
     *
     * @param array $properties Array of property names and values.
     * @return JSONCustomBuilder
     *
     * @see JSONCustomBuilder::setProperty
     */
    public function setProperties(array $properties){
        foreach($properties as $propName => $propValue){
            $this->setProperty($propName, $propValue);
        }
        return $this;
    }

    /**
     * Get build result.
     * @return string
     */
    public function getResult() {
        return json_encode($this->properties, JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP);
    }
}
