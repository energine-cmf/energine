<?php

/**
 * @file
 * PropertiesValuesEditor
 *
 *
 * @code
class PropertiesValuesEditor;
 * @endcode
 *
 * @author Pavel Dubenko
 * @copyright Energine 2014
 *
 * @version 1.0.0
 */
class PropertiesValuesEditor extends Grid {

    public function __construct($name, $module, array $params = null) {
        parent::__construct($name, $module, $params);
        $this->setTableName('shop_product_properties_values');
    }

    protected function defineParams() {
        return array_merge(
            parent::defineParams(),
            array(
                'typeID' => false
            )
        );
    }

    /**
     * @copydoc DataSet::loadData
     */
    protected function loadData() {
        if ($this->pager) {
            // pager существует -- загружаем только часть данных, текущую страницу
            $this->setLimit($this->pager->getLimit());
        }

        if ($this->getState() == 'getRawData') {
            $data = $this->dbh->select('select pp.prop_id, lang_id, prop_name
            FROM shop_product_properties pp
              LEFT JOIN shop_product_properties_translation ppt ON (pp.prop_id=ppt.prop_id) AND (lang_id=%s)
              WHERE pt_id=%s
            ORDER BY prop_order_num', E()->getLanguage()->getCurrent(), $this->getParam('typeID'));
        } else {
            $data = parent::loadData();
        }


        return $data;
    }

    /*protected function prepare() {
        parent::prepare();
        if (in_array($this->getType(), array(self::COMPONENT_TYPE_FORM_ADD, self::COMPONENT_TYPE_FORM_ALTER))) {
            $this->getDataDescription()->getFieldDescriptionByName('pt_id')->setType(FieldDescription::FIELD_TYPE_HIDDEN);
            $this->getDataDescription()->getFieldDescriptionByName('session_id')->setType(FieldDescription::FIELD_TYPE_HIDDEN);

            $f = $this->getData()->getFieldByName('pt_id');
            $f->setData($this->getParam('typeID'), true);

            $f = $this->getData()->getFieldByName('session_id');
            $f->setData(session_id(), true);
        }
    }*/
} 