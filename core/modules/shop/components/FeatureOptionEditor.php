<?php
/**
 * @file
 * FeatureOptionEditor
 *
 * It contains the definition to:
 * @code
class FeatureOptionEditor;
 * @endcode
 *
 * @author andy.karpov
 * @copyright Energine 2015
 *
 * @version 1.0.0
 */
namespace Energine\shop\components;

use Energine\share\components\Grid;
use Energine\share\gears\FieldDescription;
use Energine\share\gears\QAL;

/**
 * Feature option editor.
 *
 * @code
class FeatureOptionEditor;
 * @endcode
 */
class FeatureOptionEditor extends Grid {
    /**
     * @copydoc Grid::__construct
     */
    // На вход параметром получаем ID характеристики, к которой следует привязать вариант множественного выбора.
    public function __construct($name, array $params = NULL) {
        parent::__construct($name, $params);
        $this->setTableName('shop_feature_options');

        if (is_numeric($this->getParam('featureID'))) {
            $filter = sprintf(' (feature_id = %s) ', $this->getParam('featureID'));
        } else {
            $filter = sprintf(' (feature_id IS NULL and session_id="%s") ', session_id());
        }

        $this->setFilter($filter);

    }

    /**
     * @copydoc Grid::defineParams
     */
    // добавлен параметр featureID - ид характеристики
    protected function defineParams() {
        return array_merge(
            parent::defineParams(),
            [
                'featureID' => false,
            ]
        );
    }

    public function add() {
        parent::add();
        $data = $this->getData();
        if ($feature_id = $this->getParam('featureID')) {
            $f = $data->getFieldByName('feature_id');
            $f->setRowData(0, $feature_id);
        }
        $f = $data->getFieldByName('session_id');
        $f->setData(session_id(), true);
    }

    public function edit() {
        parent::edit();
        $data = $this->getData();
        if ($feature_id = $this->getParam('featureID')) {
            $f = $data->getFieldByName('feature_id');
            $f->setRowData(0, $feature_id);
        }
    }

    protected function createDataDescription() {
        $result = parent::createDataDescription();

        if (in_array($this->getState(), ['add', 'edit'])) {

            $fd = $result->getFieldDescriptionByName('feature_id');
            $fd->setType(FieldDescription::FIELD_TYPE_HIDDEN);

            $fd = $result->getFieldDescriptionByName('session_id');
            $fd->setType(FieldDescription::FIELD_TYPE_HIDDEN);
        }

        return $result;
    }

}