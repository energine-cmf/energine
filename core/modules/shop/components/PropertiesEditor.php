<?php
/**
 * @file
 * PropertiesEditor
 *
 *
 * @code
class PropertiesEditor;
 * @endcode
 *
 * @author Pavel Dubenko
 * @copyright Energine 2014
 *
 * @version 1.0.0
 */


/**
 * Product Types editor
 *
 * Just a grid for product types CRUD
 */
class PropertiesEditor extends Grid {
    public function __construct($name, $module, array $params = null) {
        parent::__construct($name, $module, $params);
        $this->setTableName('shop_product_properties');
        $linkedID = $this->getParam('typeID');

        if ($this->getState() != 'save') {
            if ($linkedID) {
                $this->addFilterCondition(array('pt_id' => $linkedID));
            } else {
                $this->addFilterCondition(array('pt_id' => null, 'session_id' => session_id()));
            }
        }
    }

    protected function defineParams() {
        return array_merge(
            parent::defineParams(),
            array(
                'typeID' => false
            )
        );
    }

    protected function prepare(){
        parent::prepare();
        if(in_array($this->getType(), array(self::COMPONENT_TYPE_FORM_ADD, self::COMPONENT_TYPE_FORM_ALTER))){
            $this->getDataDescription()->getFieldDescriptionByName('pt_id')->setType(FieldDescription::FIELD_TYPE_HIDDEN);
            $this->getDataDescription()->getFieldDescriptionByName('session_id')->setType(FieldDescription::FIELD_TYPE_HIDDEN);

            $f = $this->getData()->getFieldByName('pt_id');
            $f->setData($this->getParam('typeID'), true);

            $f = $this->getData()->getFieldByName('session_id');
            $f->setData(session_id(), true);
        }
    }

}

