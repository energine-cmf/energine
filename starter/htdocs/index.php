<?php
/**
 * Три... Два... Один... Запуск!
 *
 * @package energine
 * @author pavka
 * @copyright Energine 2009
 */
//Проверяем установлена ли система
//Отталкиваемся от того факта что содержание .htaccess - создается при установке
/*if (!file_exists('.htenergine') || (filesize('.htenergine') === 0)) {
    header($_SERVER['SERVER_PROTOCOL'] . " 200 Ok");
    header("Location: setup/index.php");
    exit();
}*/


try {
    //подключаем инициализационные функции
    require_once('core/kernel/ini.func.php');

    //подключаем служебные(вспомогательные) функции
    require_once('core/kernel/utils.func.php');

    $reg = E();
    UserSession::start();
    
    $reg->getController()->run();
    $reg->getResponse()->commit();
}
catch (Exception $generalException) {
    //Если отрабатывает этот кетчер, значит дела пошли совсем плохо
    //@todo исправить вывод в зависимости от режима отладки
    $r = E()->getResponse();
    $r->setHeader('Content-Type', 'text/plain; charset=utf-8');
    $r->write((string) $generalException);
    $r->commit();
}
