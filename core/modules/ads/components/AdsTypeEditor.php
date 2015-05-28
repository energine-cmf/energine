<?php

namespace Energine\ads\components;

use Energine\share\components\Grid,
    Energine\share\gears\FieldDescription,
    Energine\share\gears\Field;

class AdsTypeEditor extends Grid {

    public function __construct($name,  array $params = null) {
        parent::__construct($name, $params);
        $this->setTableName('ads_types');
        $this->setTitle($this->translate('TXT_ADS_TYPES_EDITOR'));
    }

}
