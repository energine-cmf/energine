<?php

namespace Energine\share\gears;

interface IWatermark {

    public function setSource($filename);
    public function setDestination($filename);
    public function apply();

}