<?php
/**
 * @file
 * JSONPCustomBuilder.
 *
 * It contains the definition to:
 * @code
class JSONPCustomBuilder;
@endcode
 *
 * @author andy.karpov
 *
 * @version 1.0.0
 */
namespace Energine\share\gears;
/**
 * JSONP builder.
 *
 * @code
class JSONPCustomBuilder;
@endcode
 */
class JSONPCustomBuilder extends JSONCustomBuilder {
    /**
     * Callback.
     * @var string $callback
     */
    public $callback = 'undefined';

    /**
     * Set callback.
     *
     * @param string $callback Callback.
     * @return $this
     */
    public function setCallback($callback) {
        $this->callback = (string) $callback;
        return $this;
    }

    public function getResult() {
        return
            $this->callback .
            '(' .
            json_encode($this->properties, JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP) .
            ');'
        ;
    }
}
