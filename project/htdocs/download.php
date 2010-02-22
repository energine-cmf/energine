<?php
/**
 * Файл-маршрутизатор для контроля загрузки приватных файлов
 *
 * @package energine
 * @subpackage core
 * @author pavka
 * @copyright Energine 2008
 * @version $Id: download.php,v 1.1.1.1 2008/10/26 09:50:46 pavka Exp $
 */
function getFile($filePath){
    $fileType = 'application/octet-stream';
    header("Cache-Control: public, must-revalidate");
    header("Pragma: hack"); // WTF? oh well, it works...
    header("Content-Type: " . $fileType);
    header("Content-Length: " .(string)(filesize($filePath)) );
    header('Content-Disposition: attachment; filename="'.basename($filePath).'"');
    header("Content-Transfer-Encoding: binary\n");
    readfile($filePath);
}

//если вызвали download.php напрямую - ничего не происходит
if ($_SERVER['REQUEST_URI'] == $_SERVER['SCRIPT_NAME']) {
    die ();
}
$protectedMode = isset($_GET['protected']);

//подключаем начальные настройки
require_once('core/framework/ini.func.php');
require_once('core/framework/utils.func.php');

//получаем путь к файлу
if(Object::_getConfigValue('site.root') != '/')
$filePath = str_replace(Object::_getConfigValue('site.root'), '', $_SERVER['REQUEST_URI']);
else $filePath = substr($_SERVER['REQUEST_URI'], 1);

//если файл не существует - сообщаем об этом
if (!file_exists($filePath)) {
    die('File doesn\'t exist');
}
if (!$protectedMode) {
    //Стартуем сессию
    UserSession::getInstance()->start();

    //Создаем пользователя
    $user = AuthUser::getInstance();

    //если пользователь аутентифицирован - выдаем файл
    if ($user->isAuthenticated()) {
        getFile($filePath);
    }
    else {
    	die('File is accesible only for site users');
    }
}
else {
    getFile($filePath);
}
