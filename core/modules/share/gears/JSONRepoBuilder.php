<?php
/**
 * @file
 * JSONRepoBuilder.
 *
 * It contains the definition to:
 * @code
class JSONRepoBuilder;
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
class JSONRepoBuilder;
@endcode
 */
class JSONRepoBuilder extends JSONBuilder {
    /**
     * Bread crumbs.
     * @var array $breadcrumbs
     */
    private $breadcrumbs;

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
        $result['breadcrumbs'] = $this->breadcrumbs;
        $result['result'] = true;
        $result['mode'] = 'select';

        $this->result = $result;

        return true;
    }

    /**
     * Set brad crumbs.
     *
     * @param array $repoBreadCrumbs Brad crumbs.
     */
    public function setBreadcrumbs(array $repoBreadCrumbs){
        $this->breadcrumbs = $repoBreadCrumbs;
    }
}
