<?php
/**
 * Три... Два... Один... Запуск!
 *
 * @package energine
 * @author pavka
 * @copyright Energine 2011
 */

try {
    require_once('bootstrap.php');

    if ($use_timer = E()->getConfigValue('site.useTimer')) {

        class Timer Extends Energine\share\gears\Primitive {

        }

        $timer = new Timer();
        $timer->startTimer();
    }
    E()->UserSession->init();

    E()->getController()->run();

    if ($use_timer) {
        $timer->stopTimer();
        E()->getResponse()->setHeader('X-Timer', $timer->getTimer());
    }

    E()->getResponse()->commit();
} catch (\LogicException $bootstrapException) {
    //Все исключения перехваченные здесь произошли в bootstrap'e
    //И ориентироваться на наличие DEBUG здесь нельзя
    //Поэтому выводим как есть
    header('Content-Type: text/plain; charset=utf-8');
    echo $bootstrapException->getMessage();
} catch (\Exception $generalException) {
    //Если отрабатывает этот кетчер, значит дела пошли совсем плохо
    if (defined('DEBUG') && DEBUG) {
        header('Content-Type: text/plain; charset=utf-8');
        try {
            var_dump($generalException);
        } catch (\Exception $e) {
            echo (string)$e->getMessage();
        }
    }
    //TODO В лог что ли писать?
    /*
     else{

      }
     */
    exit;
}
