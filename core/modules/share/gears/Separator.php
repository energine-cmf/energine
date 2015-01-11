<?php
/**
 * @file
 * Separator.
 *
 * It contains the definition to:
 * @code
class Separator;
@endcode
 *
 * @author dr.Pavka
 * @copyright Energine 2006
 *
 * @version 1.0.0
 */
namespace Energine\share\gears;
/**
 * Separator element on toolbar.
 *
 * @code
class Separator;
@endcode
 */
class Separator extends Control {
    /**
     * @copydoc Control::__construct
     */
    public function __construct($id) {
        parent::__construct($id);
        $this->type = 'separator';
    }
}
