<?php

/**
 * Три... Два... Один... Запуск!
 *
 * @package energine
 * @author pavka
 * @copyright Energine 2011
 */

try {
    // подключаем bootstrap
    require_once('bootstrap.php');

    UserSession::start();
    $reg = E();
    $reg->getController()->run();
    $reg->getResponse()->commit();
}
catch (Exception $generalException) {
    //Если отрабатывает этот кетчер, значит дела пошли совсем плохо

    if (defined('DEBUG') && DEBUG) {
        header('Content-Type', 'text/plain; charset=utf-8');
        echo (string) $generalException;
    }

    exit;
}
