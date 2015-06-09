<?php

namespace Energine\ads\components;

use Energine\share\components\DBDataSet;

class Ads extends DBDataSet {

    public function __construct($name, $module, array $params = NULL) {
        parent::__construct($name, $module, $params);
        $this->setTableName('ads_items');
    }

    protected function defineParams() {
        return array_merge(
            parent::defineParams(),
            [
                'type' => false,
                'limit' => 1,
                'order' => 'rand'
            ]
        );
    }

    public function main() {

        $type = $this->getParam('type');
        $this->setProperty('type', $type);
        $type_id = $this->dbh->getScalar('ads_types', 'ads_type_id', array('ads_type_sysname' => $type));

        $this->setFilter([
            'ads_type_id' => $type_id,
            'ads_item_is_active' => 1
        ]);

        if ($limit = $this->getParam('limit')) {
            $this->setLimit([0, $limit]);
        }

        if ($order = $this->getParam('order')) {
            if ($order == 'rand') {
                $this->setOrder('RAND()');
            } else {
                $this->setOrder('ads_item_order_num');
            }
        }

        parent::main();
    }
}