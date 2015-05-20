<?php

namespace Energine\mail\gears;

use Energine\share\gears\SystemException;
use Energine\mail\gears\MailSourceNews;

class MailSourceFactory {

    /**
     * @param string $name
     * @return IMailSource
     * @throws SystemException
     */
    public static function getByName($name) {
        // todo! подумать, как правильно инстанциировать класс
        $class_name = 'Energine\mail\gears\MailSource' . ucfirst($name);
        $source = new $class_name();
        return $source;
    }

}