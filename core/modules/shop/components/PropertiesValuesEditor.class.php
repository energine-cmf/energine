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

    protected function createDataDescription() {
        if ($this->getType() == self::COMPONENT_TYPE_LIST) {
            $result = new DataDescription();
            $fd = new FieldDescription('pval_id');
            $fd->setType(FieldDescription::FIELD_TYPE_HIDDEN);
            $fd->setProperty('key', true);
            $fd->setProperty('index', 'PRI');
            $result->addFieldDescription($fd);

            $fd = new FieldDescription('prop_id');
            $fd->setType(FieldDescription::FIELD_TYPE_HIDDEN);


            $result->addFieldDescription($fd);
            $fd = new FieldDescription('prop_name');
            $result->addFieldDescription($fd);

            $fd = new FieldDescription('pval_name');
            $result->addFieldDescription($fd);
        } else {
            $result = parent::createDataDescription();
        }
        return $result;
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
            $data = $this->dbh->select('select pp.prop_id as pval_id, pp.prop_id, ppt.lang_id, prop_name , pval_name
            FROM shop_product_properties pp
              LEFT JOIN shop_product_properties_translation ppt ON (pp.prop_id=ppt.prop_id) AND (ppt.lang_id=%s)
              LEFT JOIN shop_product_properties_values ppv ON (pp.prop_id=ppv.prop_id)
              LEFT JOIN shop_product_properties_values_translation ppvt ON (ppv.pval_id=ppvt.pval_id) AND (ppvt.lang_id=%1$s)
              WHERE pt_id=%s
            ORDER BY prop_order_num', E()->getLanguage()->getCurrent(), $this->getParam('typeID'));
        } else {
            $data = parent::loadData();
        }


        return $data;
    }
}