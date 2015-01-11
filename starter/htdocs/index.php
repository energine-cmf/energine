<?php

/**
 * Три... Два... Один... Запуск!
 *
 * @package energine
 * @author pavka
 * @copyright Energine 2011
 */

try {
<<<<<<< HEAD
	if (!file_exists($autoloader = '../vendor/autoload.php')) {
		throw new \LogicException('Autoloader not found. Firstly you have to run "composer install".');
	}
	require_once($autoloader);

=======
    require_once('../../vendor/autoload.php');
>>>>>>> energine.ns
    // подключаем bootstrap
    require_once('bootstrap.php');

    if ($use_timer = E()->getConfigValue('site.useTimer')) {
<<<<<<< HEAD
		class Timer Extends Energine\share\gears\Object {
=======
        class Timer Extends Energine\share\gears\Object {
>>>>>>> energine.ns

        }

        $timer = new Timer();
        $timer->startTimer();
    }

<<<<<<< HEAD
	Energine\share\gears\UserSession::start();
=======
    Energine\share\gears\UserSession::start();
>>>>>>> energine.ns

    $reg = E();

    $reg->getController()->run();

    if ($use_timer) {
        $timer->stopTimer();
        $reg->getResponse()->setHeader('X-Timer', $timer->getTimer());
    }

    $reg->getResponse()->commit();
} catch (\LogicException $bootstrapException) {
    //Все исключения перехваченные здесь произошли в bootstrap'e
    //И ориентироваться на наличие DEBUG здесь нельзя
    //Поэтому выводим как есть
    header('Content-Type: text/plain; charset=utf-8');
    echo $bootstrapException->getMessage();
<<<<<<< HEAD
} catch (\Exception $generalException) {
=======
}
catch (\Exception $generalException) {
>>>>>>> energine.ns
    //Если отрабатывает этот кетчер, значит дела пошли совсем плохо
    if (defined('DEBUG') && DEBUG) {
        header('Content-Type: text/plain; charset=utf-8');
        echo (string)$generalException->getMessage();
    }
    //TODO В лог что ли писать?
    /*
     else{

      }
     */
    exit;
}
