<?php

/**
 * Общие настройки местоположения ядра
 * Используются для основных точек входа, а также для setup/index.php
 *
 * @package energine
 * @author Andy Karpov <andy.karpov@gmail.com>
 * @copyright Energine 2013
 */

// Подключаем конфиг, чтобы достать из него местоположение ядер и имя текущего ядра
if (!file_exists($configName = realpath(dirname(__FILE__)) . '/system.config.php')) {
    throw new LogicException('Не найден конфигурационный файл system.config.php.');
}

// загружаем конфиг в $config
$config = include($configName);

// получение из конфига пути к setup
if (!array_key_exists('setup_dir', $config)) {
    throw new LogicException('Не указана секция setup_dir в system.config.php.');
}

// относительный путь к ядру - если ядро вынесено на 1 уроверь выше htdocs
// define('CORE_REL_DIR', '../core');

// относительный путь к сайту - если site вынесен на 1 уровень выше htdocs
// define('SITE_REL_DIR', '../site');

// относительный путь к ядру - если ядро находится на одном уровне с htdocs
define('CORE_REL_DIR', 'core');

// относительный путь к сайту - если site находится на одном уровне с htdocs
define('SITE_REL_DIR', 'site');

// абсолютный путь к htdocs
define('HTDOCS_DIR', realpath(dirname(__FILE__)));

// абсолютный путь к ядру
define('CORE_DIR', realpath(implode(DIRECTORY_SEPARATOR, array(HTDOCS_DIR, CORE_REL_DIR))));

// абсолютный путь к сайту
define('SITE_DIR', realpath(implode(DIRECTORY_SEPARATOR, array(HTDOCS_DIR, SITE_REL_DIR))));

// абсолютный путь к папке setup
define('SETUP_DIR', $config['setup_dir']);

// режим отладки
define('DEBUG', $config['site']['debug']);

// установка текущего пути местоположения файла bootstrap.php
// как одного из путей для нахождения файлов для include
set_include_path(implode(PATH_SEPARATOR, array(realpath(dirname(__FILE__)), get_include_path())));

// inline-подключение точки входа для setup
// подключение осуществляется именно в данном файле по причине отсутствия симлинков в htdocs/core/modules
// соответственно дальнейшие include'ы - некорректны
if ((isset($_SERVER['REQUEST_URI']) and strpos($_SERVER['REQUEST_URI'], $config['site']['root'] .'setup') === 0) or (isset($argv[1]) and $argv[1] == 'setup')) {
    include_once(SETUP_DIR . DIRECTORY_SEPARATOR . 'index.php');
    exit;
}

//это первое обращение к ядру
//проверяем наличие файла ini.func.php, если он отсутствует -значит скорее всего инсталляция проекта не произошла
if(!file_exists($iniPath = implode(DIRECTORY_SEPARATOR, array(CORE_DIR, 'modules', 'share', 'gears', 'ini.func.php')))){
    throw new LogicException('Ядро не подключено. Необходимо запустить setup.');
}
// подключаем инициализационные функции
require_once($iniPath);

// подключаем служебные(вспомогательные) функции
require_once(implode(DIRECTORY_SEPARATOR, array(CORE_DIR, 'modules', 'share', 'gears', 'utils.func.php')));

// установка уже подключенного конфига в статическую переменную Object
Object::setConfigArray($config);
