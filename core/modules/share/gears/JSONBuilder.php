<?php
/**
 * @file
 * JSONBuilder.
 *
 * It contains the definition to:
 * @code
class JSONBuilder;
@endcode
 *
 * @author dr.Pavka
 * @copyright Energine 2006
 *
 * @version 1.0.0
 */
namespace Energine\share\gears;
/**
 * Build data in JSON (JavaScript Object Notation) format.
 *
 * @code
class JSONBuilder;
@endcode
 */
class JSONBuilder extends AbstractBuilder {
    /**
     * Pager.
     * @var Pager $pager
     */
    private $pager = null;

    /**
     * List of errors.
     * @var array $errors
     * @todo зачем это!?
     */
    private $errors = array();

    //todo VZ: Why true is returned?
    /**
     * @copydoc IBuilder::build
     *
     * @throws SystemException 'ERR_DEV_NO_DATA_DESCRIPTION'
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
                        switch ($fieldType) {
                            case FieldDescription::FIELD_TYPE_DATETIME:
                            case FieldDescription::FIELD_TYPE_DATE:
                            case FieldDescription::FIELD_TYPE_TIME:
                                if (!empty($fieldValue)) {
                                    $fieldValue =
                                            self::enFormatDate($fieldValue, $fieldInfo->getPropertyValue('outputFormat'), $fieldType);
                                }
                                break;
                            case FieldDescription::FIELD_TYPE_SELECT:
                                $value = $fieldInfo->getAvailableValues();
                                if (isset($value[$fieldValue])) {
                                    $fieldValue = $value[$fieldValue]['value'];
                                }
                                break;
                            case FieldDescription::FIELD_TYPE_MULTI:
                                if (is_array($fieldValue) && !empty($fieldValue)) {
                                    $values = $fieldInfo->getAvailableValues();
                                    $res = array();
                                    foreach ($fieldValue as $val) {
                                        if (isset($values[$val])) {
                                            array_push($res, $values[$val]['value']);
                                        }
                                    }
                                    $fieldValue = implode(', ', $res);
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

    public function getResult() {
        $result = $this->result;
        if (!is_null($this->pager)) {
            $result['pager'] = array(
                'current' => $this->pager->getCurrentPage(),
                'count' => $this->pager->getNumPages(),
                'records' => $this->translate('TXT_TOTAL').': '.$this->pager->getRecordsCount()
            );
        }
        $result = json_encode($result, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
        return $result;
    }

    /**
     * Get the list of errors.
     *
     * @return string
     * @todo зачем это!?
     */
    public function getErrors() {
        return json_encode($this->errors, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
    }

    //todo VZ: Total amount of pages or Pager object?
    /**
     * Set amount of pages.
     *
     * @param int $pager Amount of pages.
     */
    public function setPager($pager) {
        $this->pager = $pager;
    }
}
