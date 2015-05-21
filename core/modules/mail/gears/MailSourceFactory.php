<?php

namespace Energine\mail\gears;

use Energine\share\gears\SystemException;

class MailSourceFactory {

    /**
     * @param string $name
     * @return IMailSource
     * @throws SystemException
     */
    public static function getByName($name) {
        $class_name = E()->getConfigValue('mail.subscriptions.' . $name);
        $source = new $class_name;
        return $source;
    }

}