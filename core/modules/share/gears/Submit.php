<?php
/**
 * @file
 * Submit
 *
 * It contains the definition to:
 * @code
class Submit;
@endcode
 *
 * @author dr.Pavka
 * @copyright Energine 2006
 *
 * @version 1.0.0
 */
namespace Energine\share\gears;
/**
 * Submit button.
 *
 * @code
class Submit;
@endcode
 */
class Submit extends Button {
    /**
     * @copydoc Button::__construct
     */
    public function __construct($id, $action = false, $image = false, $title = false, $tooltip = false) {
        parent::__construct($id, $action, $image, $title, $tooltip);
        $this->type = 'submit';
    }
}
