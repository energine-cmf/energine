<?php 
/**
 * @file
 * CalendarBuilder
 *
 * It contains the definition to:
 * @code
class CalendarBuilder;
@endcode
 *
 * @author d.pavka
 * @copyright d.pavka@gmail.com
 *
 * @version 1.0.0
 */

/**
 * Calendar builder.
 *
 * @code
class CalendarBuilder;
@endcode
 */
class CalendarBuilder extends Builder {
    //todo VZ: this can be removed.
    /**
     * @copydoc AbstractBuilder::__construct
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * @copydoc Builder::createField
     */
    protected function createField($fieldName, FieldDescription $fieldInfo, $fieldValue = false, $fieldProperties = false) {
        $result = $this->result->createElement('field');

        foreach ($fieldInfo as $propName => $propValue) {
            if(!in_array($propName, array('pattern', 'message', 'sort', 'outputFormat', 'tabName'))) {
                $result->setAttribute($propName, $propValue);
            }
        }

        if ($fieldProperties) {
            foreach ($fieldProperties as $propName => $propValue) {
                $result->setAttribute($propName, $propValue);
            }
        }

        if (!empty($fieldValue)) {
            $result->nodeValue = $fieldValue;
        }

        return $result;
    }
}