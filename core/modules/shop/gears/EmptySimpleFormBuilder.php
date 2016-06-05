<?php
/**
 * Created by PhpStorm.
 * User: pavka
 * Date: 4/24/15
 * Time: 11:12 AM
 */

namespace Energine\shop\gears;


use Energine\share\gears\Builder;
use Energine\share\gears\FieldDescription;
use Energine\share\gears\SimpleBuilder;

class EmptySimpleFormBuilder extends SimpleBuilder {
    protected function run() {
        parent::run();

        if(!$this->dataDescription->isEmpty())
            $this->getResult()->removeAttribute('empty');
    }
    /**
     * @copydoc Builder::createField
     */
    protected function createField($fieldName, FieldDescription $fieldInfo, $fieldValue = false, $fieldProperties = false) {
        foreach(
            [
                'nullable',
                'pattern',
                'message',
                'tabName',
                'sort',
                'customField',
                //'deleteFileTitle',
                /*'msgOpenField',
                'msgCloseField',*/
                'default'
            ] as $propertyName
        ) {
            $fieldInfo->removeProperty($propertyName);
        }

        return Builder::createField($fieldName, $fieldInfo, $fieldValue, $fieldProperties);
    }
}