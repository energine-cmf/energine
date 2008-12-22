<?php
/**
 * Содержит класс Separator
 *
 * @package energine
 * @subpackage share
 * @author dr.Pavka
 * @copyright ColoCall 2006
 * @version $Id$
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
        parent::__construct();
        $this->type = 'separator';
    }

    /**
     * Возвращает идентификатор разделителя
     *
     * @return string
     * @access public
     */
    public function getID() {
        return $this->getAttribute('id');
    }
}
