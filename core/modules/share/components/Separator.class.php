<?php
/**
 * Содержит класс Separator
 *
 * @package energine
 * @subpackage share
 * @author dr.Pavka
 * @copyright Energine 2006
 */

/**
 * Разделитель элементов управления на панели инструментов
 *
 * @package energine
 * @subpackage share
 * @author dr.Pavka
 *
 */
class Separator extends Control {

    /**
     * Конструктор класса
     *
     * @access public
     */
    public function __construct($id) {
        parent::__construct($id);
        $this->type = 'separator';
    }
}
