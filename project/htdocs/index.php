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
if(!file_exists('.htenergine') || (filesize('.htenergine') === 0)){
    header($_SERVER['SERVER_PROTOCOL']." 200 Ok");
    header("Location: setup/index.php");
    exit();
}

//подключаем инициализационные функции
require_once('core/framework/ini.func.php');

//подключаем служебные(вспомогательные) функции
require_once('core/framework/utils.func.php');

try {
    UserSession::getInstance()->start();
    DocumentController::getInstance()->run();
    Response::getInstance()->commit();
}
catch (SystemException $systemException) {
    $systemException->handle();
}
catch (Exception $generalException) {
    $systemException = new SystemException(
        $generalException->getMessage(),
        $generalException->getCode()
    );
    $systemException->handle();
}

