<?php
/**
 * @file
 * Initialisation file.
 *
 * It contains the initialisation functions and constants.
 *
 * @author pavka
 * @copyright Energine 2007
 *
 * @version 1.0.0
 */
// Хак для cgi mode, где SCRIPT_FILENAME возвращает путь к PHP, вместо пути к текущему исполняемому файлу
if (isset($_SERVER['SCRIPT_FILENAME'])) {
    $_SERVER['SCRIPT_FILENAME'] =
        (isset($_SERVER['PATH_TRANSLATED'])) ? $_SERVER['PATH_TRANSLATED'] : $_SERVER['SCRIPT_FILENAME'];
}

// устанавливаем максимальный уровень отображения ошибок
//error_reporting(E_ALL | E_STRICT);
//С 5.4 вроде как E_STRICT стал частью E_ALL
error_reporting(E_ALL);
// включаем вывод ошибок и отключаем вывод в HTML
@ini_set('display_errors', 1);
@ini_set('html_errors', 0);

@date_default_timezone_set('Europe/Kiev');

/**
 * Path to the site kernel directory.
 * @var string SITE_KERNEL_DIR
 */
define('SITE_KERNEL_DIR', SITE_DIR . '/kernel');

$openBasedirRestrictionsIteratorFunc = function ($moduleDir, $phpClassType) {
    return array_map(
        function ($row) use ($moduleDir, $phpClassType) {
            return implode(DIRECTORY_SEPARATOR, [$moduleDir, MODULES, $row, $phpClassType]) . DIRECTORY_SEPARATOR;
        },
        array_filter(
            scandir($moduleDir . DIRECTORY_SEPARATOR . MODULES),
            function ($row) {
                return strpos($row, '.') === false;
            }
        )
    );
};
$dirs = array_merge(
    ['.'],
    call_user_func_array('array_merge',
        array_map(
            function ($folder) use ($openBasedirRestrictionsIteratorFunc) {
                return call_user_func_array('array_merge',
                    array_map(
                        function ($moduleType) use ($folder, $openBasedirRestrictionsIteratorFunc) {
                            return $openBasedirRestrictionsIteratorFunc($moduleType, $folder);
                        },
                        [CORE_DIR, SITE_DIR]));
            },
            ['components', 'gears', 'templates', 'config', 'transformers'])
    )
);

if (!($dir = @ini_get('upload_tmp_dir'))) {
    $dir = '/tmp';
}

// выключено 03.02.2015 by andy.karpov временно
// по причине глючности данного решения с модулями сайта (например при вызове file_exists для xml компонента сайта)
/*array_push($dirs, $dir);
ini_set('open_basedir',
    implode(PATH_SEPARATOR,
        $dirs
    )
);*/

unset($openBasedirRestrictionsIteratorFunc, $dirs, $dir);

/*
 * Определяем константы прав доступа
 * они должны иметь те же значения что и в таблице user_group_rights + ACCESS_NONE = 0
 * загружать их из таблицы особого смысла не имеет
 */
/**
 * Access level: none.
 * @var int ACCESS_NONE
 */
define('ACCESS_NONE', 0);
/**
 * Access level: read.
 * @var int ACCESS_READ
 */
define('ACCESS_READ', 1);
/**
 * Access level: edit.
 * @var int ACCESS_EDIT
 */
define('ACCESS_EDIT', 2);
/**
 * Access level: full.
 * @var int ACCESS_FULL
 */
define('ACCESS_FULL', 3);

// Подключаем реестр и мемкешер, нужные нам для автолоадера
require_once('Registry.php');

/**
 * @fn nrgnErrorHandler($errLevel, $message, $file, $line, $errContext)
 * @brief Error handler.
 * It converts all errors to the SystemException with type ERR_DEVELOPER.
 *
 * @param int $errLevel Error level.
 * @param string $message Error message.
 * @param string $file Error file.
 * @param int $line Error line.
 * @param array $errContext Error context.
 *
 */
set_error_handler(function ($errLevel, $message, $file, $line, $errContext) {
    $e = new Energine\share\gears\SystemException(
        $message,
        Energine\share\gears\SystemException::ERR_DEVELOPER
    );
    throw $e->setFile($file)->setLine($line);
}
);
