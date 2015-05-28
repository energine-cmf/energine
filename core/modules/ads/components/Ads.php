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

        $this->setLimit([0,1]);

        // todo: set order by RAND() somehow

        parent::main();
    }
}