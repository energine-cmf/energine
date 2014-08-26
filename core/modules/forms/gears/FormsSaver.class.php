<?php
/**
 * @file
 * FormsSaver
 *
 * It contains the definition to:
 * @code
class FormsSaver;
@endcode
 *
 * @version 1.0.0
 */
namespace forms\gears;
use share\gears\Saver, share\gears\QAL;
/**
 * Saver for forms.
 *
 * @code
class FormsSaver;
@endcode
 */
class FormsSaver extends Saver{
    /**
     * Copydoc Saver::save
     */
    public function save(){
        $result = parent::save();
        if($this->getMode() == QAL::INSERT){

        }
        return $result;
    }
}
