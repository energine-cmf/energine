<?php
/**
 * @file
 * SimpleBuilder.
 *
 * It contains the definition to:
 * @code
class SimpleBuilder;
 * @endcode
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
 * @endcode
 *
 * This is used for the cases when there is not necessary to view all filed attributes.
 */
class SimplestBuilder extends Builder {

    /**
     * @param string $fieldName
     * @param FieldDescription $fieldInfo
     * @param bool $fieldValue
     * @param bool $fieldProperties
     * @return \DOMElement
     */
    protected function createField($fieldName, FieldDescription $fieldInfo, $fieldValue = false, $fieldProperties = false) {
        foreach (
            [
                'nullable',
                'pattern',
                'message',
                'tabName',
                'tableName',
                'sort',
                'customField',
                'url',
                'separator',
                'title',
                'key',
                'default'
            ] as $propertyName
        ) {
            $fieldInfo->removeProperty($propertyName);
        }

        return parent::createField($fieldName, $fieldInfo, $fieldValue, $fieldProperties);
    }
}
