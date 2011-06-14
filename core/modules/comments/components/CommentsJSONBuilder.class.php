<?php
/**
 * Содержит класс CommentsJSONBuilder
 *
 * @package energine
 * @subpackage comments
 * @author d.pavka
 * @copyright d.pavka@gmail.com
 */

 /**
  * Построитель для для комментариев
  *
  * @package energine
  * @subpackage comments
  * @author d.pavka@gmail.com
  */
 class CommentsJSONBuilder extends JSONBuilder {
    /**
     * Создает результирующий JSON-объект.
     *
     * @access public
     * @return bool
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