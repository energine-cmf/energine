<?php
/**
 * Created by PhpStorm.
 * User: pavka
 * Date: 9/30/15
 * Time: 2:10 PM
 */

namespace Energine\user\gears;


interface IOAuth {
    public function getLoginUrl();
}