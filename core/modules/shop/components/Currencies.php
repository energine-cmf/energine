<?php
/**
 * Created by PhpStorm.
 * User: pavka
 * Date: 8/25/15
 * Time: 6:09 PM
 */

namespace Energine\shop\components;


use Energine\share\components\DataSet;
use Energine\share\gears\SimpleBuilder;

class Currencies extends DataSet {
    protected function defineParams() {
        return array_merge(
            parent::defineParams(),
            [
                'active' => false
            ]
        );
    }

    protected function main() {
        $this->setType(self::COMPONENT_TYPE_LIST);
        $this->setDataDescription(E()['Energine\\shop\\gears\\Currency']->asDataDescription());
        $this->setData(E()['Energine\\shop\\gears\\Currency']->asData());
        $this->setBuilder(new SimpleBuilder());
    }
}