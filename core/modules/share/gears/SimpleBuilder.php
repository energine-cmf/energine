<?php
/**
 * @file
 * SimpleBuilder.
 *
 * It contains the definition to:
 * @code
class SimpleBuilder;
@endcode
 *
 * @author dr.Pavka
 * @copyright Energine 2010
 *
 * @version 1.0.0
 */
namespace Energine\share\gears;
/**
 * Simplified Builder.
 *
 * @code
class SimpleBuilder;
@endcode
 *
 * This is used for the cases when there is not necessary to view all filed attributes.
 */
class SimpleBuilder extends Builder {
    /**
     * @param string $title Recordset title.
     */
    public function __construct($title = '') {
        parent::__construct();
        $this->title = $title;
    }

    /**
     * @copydoc Builder::createField
     */
    protected function createField($fieldName, FieldDescription $fieldInfo, $fieldValue = false, $fieldProperties = false) {
        foreach(
            array(
                'nullable',
                'pattern',
                'message',
                'tabName',
                'tableName',
                'sort',
                'customField',
                //'deleteFileTitle',
                /*'msgOpenField',
                'msgCloseField',*/
                'default'
            ) as $propertyName
        ) {
            $fieldInfo->removeProperty($propertyName);
        }
        
        return parent::createField($fieldName, $fieldInfo, $fieldValue, $fieldProperties);
    }
}
