<?php
/**
 * Содержит класс Button
 *
 * @package energine
 * @subpackage share
 * @author dr.Pavka
 * @copyright Energine 2006
 * @version $Id$
 */

//require_once('core/modules/share/components/Button.class.php');

/**
 * Кнопка отправки данных
 *
 * @package energine
 * @subpackage share
 * @author dr.Pavka
 */

class Submit extends Button {
    /**
     * Конструктор
     *
     * @return type
     * @access public
     */

    public function __construct($id, $action = false, $image = false, $title = false, $tooltip = false) {
        parent::__construct($id, $action, $image, $title, $tooltip);
        $this->type = 'submit';
    }
}
