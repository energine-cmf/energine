<?php
/**
 * Содержит класс Select
 *
 * @package energine
 * @subpackage share
 * @author dr.Pavka
 * @copyright Energine 2006
 * @version $Id$
 */

/**
 * Выпадающий список
 *
 * @package energine
 * @subpackage share
 * @author dr.Pavka
 */
class Select extends Control {

    /**
     * Элементы списка
     *
     * @access private
     * @var array
     */
    private $items;

    /**
     *
     *
     * @access public
     */
    public function __construct() {
        parent::__construct();
        $this->type = 'select';
    }

    /**
     *
     *
     * @param array $itemProperties array(
     *                                  'name' => имя свойства,
     *                                  'value' => значение свойства,
     *                                  'properties' => дополнительные аттрибуты array(name => value)
     *                              )
     * @return void
     * @access public
     */
    public function addItem($itemProperties) {
    }

    /**
     *
     *
     * @param string $name
     * @return void
     * @access public
     */
    public function removeItem($name) {
    }

    /**
     *
     *
     * @param string $name
     * @return array
     * @access public
     */
    public function getItem($name) {
    }
}
