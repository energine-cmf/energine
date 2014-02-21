<?php
/**
 * Fake document class for component test proposes
 *
 * @class Document
 */
class Document {

    public $componentManager;

    public function __construct() {
        $this->componentManager = new ComponentManager($this);
    }

    /**
     * @return bool
     */
    public function getRights() {
        return true;
    }

    /**
     * Should return false, for inner
     *@code
     class Component
     *@endcode
     * structure
     * @return bool
     */
    public function getProperty() {
        return false;
    }

}