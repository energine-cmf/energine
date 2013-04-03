<?php

/**
 * Общие настройки местоположения ядра
 * Используются для основных точек входа, а также для setup/index.php
 *
 * @package energine
 * @author Andy Karpov <andy.karpov@gmail.com>
 * @copyright Energine 2013
 */

// относительный путь к ядру (используется инсталлятором) - если ядро вынесено на 1 уроверь выше htdocs
// define('CORE_REL_DIR', '../core');

// относительный путь к сайту (используется инсталлятором) - если site вынесен на 1 уровень выше htdocs
// define('SITE_REL_DIR', '../site');

// относительный путь к ядру (используется инсталлятором) - если ядро находится на одном уровне с htdocs
define('CORE_REL_DIR', 'core');

// относительный путь к сайту (используется инсталлятором) - если site находится на одном уровне с htdocs
define('SITE_REL_DIR', 'site');

// абсолютный путь к ядру
define('CORE_DIR', realpath(dirname(__FILE__) . '/' . CORE_REL_DIR));

// абсолютный путь к сайту
define('SITE_DIR', realpath(dirname(__FILE__) . '/' . SITE_REL_DIR));

// установка текущего пути местоположения файла bootstrap.php
// как одного из путей для нахождения файлов для include
set_include_path(realpath(dirname(__FILE__)) . PATH_SEPARATOR . get_include_path());
