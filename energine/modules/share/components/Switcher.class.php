<?php
/**
 * Содержит класс Switcher
 *
 * @package energine
 * @subpackage share
 * @author dr.Pavka
 * @copyright ColoCall 2006
 * @version $Id$
 */

//require_once('core/modules/share/components/Button.class.php');

/**
 * Переключатель
 *
 * @package energine
 * @subpackage share
 * @author dr.Pavka
 */
class Switcher extends Button {

    public function __construct($id, $action = false, $image = false, $title = false, $tooltip = false) {
        parent::__construct($id, $action = false, $image = false, $title = false, $tooltip = false);
        $this->type = 'switcher';
    }
}
