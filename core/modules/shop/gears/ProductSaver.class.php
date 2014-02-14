<?php
/**
 * @file
 * ProductSaverEditor
 *
 *
 * @code
class ProductSaverEditor;
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
class ProductSaver extends Saver {
    /**
     * @copydoc Saver::setData
     */
    public function setData(Data $data) {
        parent::setData($data);
        if (($f = $data->getFieldByName('product_add_date')) && ($this->getMode() == QAL::INSERT)) {
            $f->setData(date('Y-m-d H:i'), true);
        }

        if ($f = $data->getFieldByName('product_mod_date')) {
            $f->setData(date('Y-m-d H:i'), true);
        }
    }
}

