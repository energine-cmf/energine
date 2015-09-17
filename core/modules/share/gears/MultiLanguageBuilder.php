<?php
/**
 * @file
 * MultiLanguageBuilder.
 *
 * It contains the definition to:
 * @code
class MultiLanguageBuilder;
@endcode
 *
 * @author dr.Pavka
 * @copyright Energine 2006
 *
 * @version 1.0.0
 */

namespace Energine\share\gears;

/**
 * Builder for multilingual components.
 *
 * @code
class MultiLanguageBuilder;
@endcode
 */
class MultiLanguageBuilder extends Builder {
    /**
     * @copydoc Builder::run
     */
    protected function run() {
        $lang = E()->Language;

        $records = array();
        $correlation = array();

        // для режима списка и режима редактирования (когда есть данные)
        if (!$this->data->isEmpty()) {
            foreach ($this->dataDescription as $fieldName => $fieldInfo) {
                $fieldData = $this->data->getFieldByName($fieldName);
                // если это первичный ключ
                if ($fieldInfo->getPropertyValue('key') === true) {
                    $fieldInfo->setProperty('tabName', E()->Utils->translate('TXT_PROPERTIES'));
                    $i = 0;
                    while ($i < $fieldData->getRowCount()) {
                        $rowData = $fieldData->getRowData($i);
                        $index = (is_null($rowData))?0:$rowData;

                        $correlation[$i] = $index;
                        if (!isset($records[$index])) {
                            $records[$index][] = $this->createField($fieldName, $fieldInfo, $rowData, $fieldData->getRowProperties($i));
                        }
                        $i++;
                    }
                }
                // если это мультиязычное поле
                elseif ($fieldInfo->isMultilanguage()) {
//                    $title = $fieldInfo->getPropertyValue('title');
                    foreach ($fieldData->getData() as $key => $data) {
                        $langID = $this->data->getFieldByName('lang_id')->getRowData($key);
                        $dataProperties = $fieldData->getRowProperties($key);
                        $fieldInfo->setProperty('language', $langID);
                        $fieldInfo->setProperty('tabName', $lang->getNameByID($langID));

						// языковое поле типа select
						if (in_array($fieldInfo->getType(), array(FieldDescription::FIELD_TYPE_MULTI, FieldDescription::FIELD_TYPE_SELECT))) {
							if ($fieldInfo->getType() == FieldDescription::FIELD_TYPE_SELECT) {
								$data = array($data);
							}
							$fieldValue = $this->createOptions($fieldInfo, $data);
							$dom_field = $this->createField($fieldName, $fieldInfo, $fieldValue, $dataProperties);
						}
						// другие поля
						else {
							$dom_field = $this->createField($fieldName, $fieldInfo, $data, $dataProperties);
						}

                        $records[$correlation[$key]][] = $dom_field;
                    }
                }
                // все остальные поля
                elseif (!$fieldInfo->getPropertyValue('languageID')) {
                    $i = 0;
                    $tmp = array_flip($correlation);
                    foreach ($tmp as $key => $value) {
                        $fieldValue = false;
                        if (in_array($fieldInfo->getType(), array(FieldDescription::FIELD_TYPE_MULTI, FieldDescription::FIELD_TYPE_SELECT))) {
                            if ($this->data && $this->data->getFieldByName($fieldName)) {
                                if ($fieldInfo->getType() == FieldDescription::FIELD_TYPE_SELECT) {
                                	$data = array($this->data->getFieldByName($fieldName)->getRowData($i));
                                }
                                else {
                                    $data = $this->data->getFieldByName($fieldName)->getRowData($i);
                                }
                            }
                            else {
                            	$data = false;
                            }
                            $fieldValue = $this->createOptions($fieldInfo, $data);
                        }
                        elseif (!$this->data) {
                            $fieldValue = false;
                        }
                        elseif ($this->data->getFieldByName($fieldName)) {
                            $fieldValue = $fieldData->getRowData($value);
                        }

                        $dataProperties = ($fieldData)?$fieldData->getRowProperties($value):false;
                        if (is_null($fieldInfo->getPropertyValue('tabName'))) {
                            $fieldInfo->setProperty('tabName', E()->Utils->translate('TXT_PROPERTIES'));
                        }
                        /*else {
                        	$fieldInfo->setProperty('tabName', $fieldInfo->getPropertyValue('tabName'));
                        }*/

                        $dom_field = $this->createField($fieldName, $fieldInfo, $fieldValue, $dataProperties);
                        $records[$correlation[$value]][] = $dom_field;
                        $i++;
                    }
                }
            }
            foreach ($records as $key => $value) {
                $dom_record = $this->document->createElement('record');
                foreach ($value as $val) {
                    $dom_record->appendChild($val);
                }
                $this->getResult()->appendChild($dom_record);
            }
        }
        // для режима вставки (когда данные отсутствуют)
        else {
            $dom_record = $this->document->createElement('record');
            foreach ($this->dataDescription as $fieldName => $fieldInfo) {
                if ($fieldInfo->isMultilanguage()) {
                    //$title = $fieldInfo->getPropertyValue('title');
                    foreach (array_keys($lang->getLanguages()) as $langID) {
                        $fieldInfo->setProperty('language', $langID);
                        $fieldInfo->setProperty('tabName', $lang->getNameByID($langID));
                        $dom_record->appendChild($this->createField($fieldName, $fieldInfo, ''));
                    }
                }
                elseif (!$fieldInfo->getPropertyValue('languageID')){
                    if (in_array($fieldInfo->getType(), array(FieldDescription::FIELD_TYPE_MULTI, FieldDescription::FIELD_TYPE_SELECT))) {
                        $fieldValue = $this->createOptions($fieldInfo);
                    }
                    else {
                        $fieldValue = false;
                    }

                    if (is_null($fieldInfo->getPropertyValue('tabName'))) {
                        $fieldInfo->setProperty('tabName', E()->Utils->translate('TXT_PROPERTIES'));
                    }
                    /*else {
                    	$fieldInfo->setProperty('tabName', $fieldInfo->getPropertyValue('tabName'));
                    }*/

                    $dom_record->appendChild($this->createField($fieldName, $fieldInfo, $fieldValue));
                }
            }
            $this->getResult()->setAttribute('empty', translate('MSG_EMPTY_RECORDSET'));
            $this->getResult()->appendChild($dom_record);
        }
    }
}
