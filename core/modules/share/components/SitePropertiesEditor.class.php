<?php
/**
 * @file
 * SitePropertiesEditor
 *
 * It contains the definition to:
 * @code
class SitePropertiesEditor;
@endcode
 *
 * @author Andrii A
 * @copyright Energine 2014
 *
 * @version 1.0.0
 */
namespace Energine\share\components;
use Energine\share\gears\SitePropertiesSaver, Energine\share\gears\FieldDescription;
/**
 * Site properties editor.
 *
 * @code
class SitePropertiesEditor;
@endcode
 */
class SitePropertiesEditor extends Grid {
    /**
     * @copydoc Grid::__construct
     */
    public function __construct($name, $module, array $params = null) {
        parent::__construct($name, $module, $params);
        $this->setTableName('share_sites_properties');
        $this->setSaver(new SitePropertiesSaver());
        // Если передан параметр siteID,
        // то будем считать, что мы открыли редактор свойств конкретного сайта,
        // в противном случае просто открываем редактор.
        if(array_key_exists('siteID', $params)) {
            $this->addFilterCondition(
                    'site_id  = ' . intval($params['siteID']) . ' OR site_id IS NULL'
            );
        }
    }

    protected function createDataDescription() {
        $dd = parent::createDataDescription();
        if(in_array($this->getState(), array('add', 'edit'))) {
            $dd->getFieldDescriptionByName('site_id')->setType(FieldDescription::FIELD_TYPE_HIDDEN);
            if($this->getState() == 'edit') {
                $dd->getFieldDescriptionByName('prop_name')->setMode(FieldDescription::FIELD_MODE_READ);
            }
        }
        /*if(in_array($this->getState(), array('getRawData', 'main'))) {
            $fd = new FieldDescription('prop_is_default');
            $fd->setType(FieldDescription::FIELD_TYPE_BOOL);
            $fd->setProperty('customField', 1);
            $fd->setProperty('sort', 0);
            $dd->addFieldDescription($fd);
        }*/
        return $dd;
    }

    protected function createData() {
        $data = parent::createData();
        if($this->getState() == 'add') {
            $data->getFieldByName('site_id')->setData($this->getParam('siteID'), true);
        }
        return $data;
    }

    protected function loadData() {
        $data = parent::loadData();
        if(($this->getState() == 'getRawData')
            && ($this->getParam('siteID'))
            && is_array($data)) {
            $props = array();
            foreach($data as $key => $value) {
                $props[$value['prop_name']][$key] = intval($value['site_id']);
            }
            /**
             * При формировании массива $props, site_id = null
             * превращается в site_id = 0.
             */
            array_walk(
                $props,
                function($row) use (&$data) {
                    if(in_array(0, $row) && in_array($this->getParam('siteID'), $row)) {
                        $keyToUnset = array_search(0, $row);
                        if(false !== $keyToUnset) {
                            unset($data[$keyToUnset]);
                        }
                    }
                }
            );
            /**
             * Вычисляем значение prop_is_default.
             */
            $data = array_map(function($row) {
                $row['prop_is_default'] = is_null($row['site_id']);
                return $row;
            }, $data);
        }
        return $data;
    }

    protected function defineParams() {
        return array_merge(
            parent::defineParams(),
            array(
                'siteID' => null
            )
        );
    }
}