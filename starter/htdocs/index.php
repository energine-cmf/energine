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

    $use_timer = E()->getConfigValue('site.useTimer');
    if ($use_timer) {
        class Timer Extends Object { }
        $timer = new Timer();
        $timer->startTimer();
    }

    UserSession::start();
    $reg = E();
    $reg->getController()->run();

    if ($use_timer) {
        $timer->stopTimer();
        $reg->getResponse()->setHeader('X-Timer', $timer->getTimer());
    }

    $reg->getResponse()->commit();
}
catch (Exception $generalException) {
    //Если отрабатывает этот кетчер, значит дела пошли совсем плохо

    if (defined('DEBUG') && DEBUG) {
        header('Content-Type: text/plain; charset=utf-8');
        echo (string) $generalException->getMessage();
    }

    exit;
}
