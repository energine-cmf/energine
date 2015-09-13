<?php
/**
 * @file
 * HTMLCap
 *
 * Contains the definition to:
 * @code
class HTMLCap;
 * @endcode
 *
 * @author dr.Pavka
 * @copyright Energine 2015
 *
 * @version 1.0.0
 */

namespace Energine\share\components;


use Energine\share\gears\EmptyBuilder;

class HTMLCap extends DataSet
{

    protected function main(){
        $this->setBuilder(new EmptyBuilder());
        $this->js = $this->buildJS();
    }

    protected function defineParams()
    {
        return array_merge(
            parent::defineParams(),
            [
                'active' => false,
            ]
        );
    }

}