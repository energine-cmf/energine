<?php

/**
 * Класс JSONRepoBuilder
 *
 * @package energine
 * @subpackage kernel
 * @author dr.Pavka
 * @copyright Energine 2006
 */


/**
 * Построитель данных в формат JSON (JavaScript Object Notation).
 *
 * @package energine
 * @subpackage kernel
 * @author dr.Pavka
 */
class JSONRepoBuilder extends JSONBuilder {
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

        foreach ($this->dataDescription as $fieldName => $fieldInfo) {
            $result['meta'][$fieldName] = array(
                'title' => $fieldInfo->getPropertyValue('title'),
                'type' => $fieldInfo->getType(),
                'key' => $fieldInfo->getPropertyValue('key') &&
                    $fieldInfo->getPropertyValue('index') ==
                        'PRI' ? true : false,
                'visible' => true /*$fieldInfo->getPropertyValue('key') &&
                        $fieldInfo->getPropertyValue('index') ==
                                'PRI' ? false : true*/,
                'name' =>
                $fieldInfo->getPropertyValue('tableName') . "[$fieldName]",
                'rights' => $fieldInfo->getRights(),
                'field' => $fieldName,
                'sort' => $fieldInfo->getPropertyValue('sort')
            );

        }

        if (!$this->data->isEmpty()) {
            for ($i = 0; $i < $this->data->getRowCount(); $i++) {
                foreach ($this->dataDescription as $fieldName => $fieldInfo) {
                    $fieldType = $fieldInfo->getType();
                    $fieldValue = null;
                    if ($this->data->getFieldByName($fieldName)) {
                        $fieldValue =
                            $this->data->getFieldByName($fieldName)->getRowData($i);

                        if (is_null($fieldValue)) {
                            $fieldValue = '';
                        }
                        if($fieldName == 'upl_publication_date'){
                            if (!empty($fieldValue)) {
                                $fieldValue =
                                    self::enFormatDate($fieldValue, $fieldInfo->getPropertyValue('outputFormat'), FieldDescription::FIELD_TYPE_DATETIME);
                            }
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
