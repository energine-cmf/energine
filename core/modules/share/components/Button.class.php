<?php
/**
 * Содержит класс Button
 *
 * @package energine
 * @subpackage share
 * @author dr.Pavka
 * @copyright ColoCall 2006
 * @version $Id$
 */

//require_once('core/modules/share/components/Control.class.php');

/**
 * Кнопка панели инструментов
 *
 * @package energine
 * @subpackage share
 * @author dr.Pavka
 */
class Button extends Control {

    /**
     * Конструктор
     *
     * @return type
     * @access public
     */
    public function __construct($id, $action = false, $image = false, $title = false, $tooltip = false) {
        parent::__construct();
        $this->type = 'button';
        $this->setAttribute('id', $id);
        if ($action)  $this->setAttribute('action',  $action);
        if ($image)   $this->setAttribute('image',   $image);
        if ($title)   $this->setAttribute('title',   $title);
        if ($tooltip) $this->setAttribute('tooltip', $tooltip);
    }

    /**
     * Устанавливает название кнопки
     *
     * @return void
     * @access public
     */
    public function setTitle($title) {
        $this->setAttribute('title', $title);
    }

    /**
     * Возвращает название кнопки
     *
     * @return string
     * @access public
     */
    public function getTitle() {
        return $this->getAttribute('title');
    }

    /**
     * Возвращает идентификатор кнопки
     *
     * @return string
     * @access public
     */
    public function getID() {
        return $this->getAttribute('id');
    }

    /**
     * Возвращает имя действия
     *
     * @return string
     * @access public
     */
    public function getAction() {
        return $this->getAttribute('action');
    }

    /**
     * Возвращает путь к изображению
     *
     * @return string
     * @access public
     */
    public function getImage() {
        return $this->getAttribute('image');
    }

    /**
     * Устанавливает всплывающую подсказку
     *
     * @param string
     * @return string
     * @access public
     */
    public function setTooltip($tooltip) {
         $this->setAttribute('tooltip', $tooltip);
    }

    /**
     * Возвращает всплывающую подсказку
     *
     * @return string
     * @access public
     */
    public function getTooltip() {
        return $this->getAttribute('tooltip');
    }
}
