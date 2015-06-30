<?php

namespace Energine\share\gears;
use Energine\share\gears\IWatermark;

trait FileRepositoryWatermark {

    public function applyWatermark($filename) {
        if ($watermark = E()->getConfigValue('repositories.watermark')) {
            if (isset($watermark[$this->getBase()])) {
                $class = $watermark[$this->getBase()];
                $instance = new $class();
                $instance->setSource($filename);
                $instance->setDestination($filename);
                $instance->apply();
            }
        }
    }

}