<?php

/**
 * Содержит класс UserLookup
 * @package energine
 * @author andy.karpov
 * @copyright Energine 2015
 */
namespace Energine\user\components;

use Energine\share\components\Grid;
use Energine\share\gears\FieldDescription;
use Energine\share\gears\Filter;
use Energine\share\gears\FilterExpression;
use Energine\share\gears\FilterField;

/**
 * Test
 * @package energine
 * @author andy.karpov
 */
class UserLookup extends Grid {
    public function __construct($name, array $params = null) {
        parent::__construct($name, $params);
        $this->setTableName('user_users');
    }

    protected function loadData() {
        $result = parent::loadData();
        if (in_array($this->getType(), [self::COMPONENT_TYPE_LIST]) && is_array($result)) {
            $result = array_map(function ($row) {
                $row['u_real_name'] = $row['u_fullname'];
                $row['u_fullname'] = $row['u_real_name'] . ' ' . $row['u_name'];
                return $row;
            }, $result);
        }

        return $result;
    }

    protected function createDataDescrition() {
        $result = parent::createDataDescription();
        if (in_array($this->getType(), [self::COMPONENT_TYPE_LIST])) {
            $f = new FieldDescription('u_real_name');
            $f->setType(FieldDescription::FIELD_TYPE_STRING);
            $result->addFieldDescription($f);

            $result->getFieldDescriptionByName('u_fullname')->setType(FieldDescription::FIELD_TYPE_HIDDEN);
        }

        return $result;
    }
}