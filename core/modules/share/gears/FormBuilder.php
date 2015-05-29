<?php
/**
 * Created by PhpStorm.
 * User: pavka
 * Date: 5/27/15
 * Time: 1:41 PM
 */

namespace Energine\share\gears;


class FormBuilder extends Builder {
    protected function run() {
        parent::run();

        if (!$this->dataDescription->isEmpty())
            $this->getResult()->removeAttribute('empty');
    }
}