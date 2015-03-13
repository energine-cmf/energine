<?php
/**
 * @file
 * CommentsJSONBuilder
 *
 * It contains the definition to:
 * @code
class CommentsJSONBuilder;
@endcode
 *
 * @author d.pavka
 * @copyright d.pavka@gmail.com
 *
 * @version 1.0.0
 */
namespace Energine\comments\gears;

use Energine\share\gears\JSONBuilder, Energine\share\gears\SystemException, Energine\share\gears\FieldDescription;
/**
 * Builder for comments.
 *
 * @code
class CommentsJSONBuilder;
@endcode
 */
 class CommentsJSONBuilder extends JSONBuilder {
    /**
     * @copydoc JSONBuilder::build
     *
     * @throws SystemException 'ERR_DEV_NO_DATA_DESCRIPTION'
     */
     public function build() {
        $result = false;

        if ($this->dataDescription == false) {
            throw new SystemException('ERR_DEV_NO_DATA_DESCRIPTION', SystemException::ERR_DEVELOPER);
        }

        if (!$this->data->isEmpty()) {
            for ($i = 0; $i < $this->data->getRowCount(); $i++) {
                foreach ($this->dataDescription as $fieldName => $fieldInfo) {
                    $fieldType = $fieldInfo->getType();
                    $fieldValue = null;
                    if ($this->data->getFieldByName($fieldName)) {
                        $fieldValue =
                                $this->data->getFieldByName($fieldName)->getRowData($i);
                        switch ($fieldType) {
                            case FieldDescription::FIELD_TYPE_DATETIME:
                            case FieldDescription::FIELD_TYPE_DATE:
                                if (!empty($fieldValue)) {
                                    $fieldValue =
                                            self::enFormatDate($fieldValue, $fieldInfo->getPropertyValue('outputFormat'));
                                }
                                break;
                            default: // not used
                        }
                        if (is_null($fieldValue)) {
                            $fieldValue = '';
                        }
                    }
                    $result['data'][$i][$fieldName] = $fieldValue;
                }
            }
        }

        $result['result'] = true;
        $result['mode'] = 'select';

        $this->result = $result;

        return true;
    }
}