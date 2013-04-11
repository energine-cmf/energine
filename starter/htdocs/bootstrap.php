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
if (!file_exists($configName = 'system.config.php')) {
    throw new Exception('Не найден конфигурационный файл system.config.php.');
}

// загружаем конфиг в $config
$config = include($configName);

// получение из конфига пути к setup
if (!array_key_exists('setup_dir', $config)) {
    throw new Exception('Не указана секция setup_dir в system.config.php.');
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
if (isset($_SERVER['REQUEST_URI']) and strpos($_SERVER['REQUEST_URI'], '/setup') === 0) {
    include_once(SETUP_DIR . DIRECTORY_SEPARATOR . 'index.php');
    exit;
}

// подключаем инициализационные функции
require_once(implode(DIRECTORY_SEPARATOR, array(CORE_DIR, 'modules', 'share', 'gears', 'ini.func.php')));

// подключаем служебные(вспомогательные) функции
require_once(implode(DIRECTORY_SEPARATOR, array(CORE_DIR, 'modules', 'share', 'gears', 'utils.func.php')));
