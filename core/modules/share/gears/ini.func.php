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

//todo VZ: Delete this?
/*
 * опыт показывает что есть еще люди пользующиеся register_globals = On
 * не катит
 */
/*if (ini_get('register_globals')) {
    die('Register_globals directive must be turned off.');
}*/

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
 * Path to the directory with user components.
 * @var string SITE_COMPONENTS_DIR
 * @note Sign @c '*' means the module name.
 */
define('SITE_COMPONENTS_DIR', SITE_DIR . '/modules/*/components');
/**
 * Path to the directory with site PHP files.
 * @var string SITE_GEARS_DIR
 * @note Sign @c '*' means the module name.
 */
define('SITE_GEARS_DIR', SITE_DIR . '/modules/*/gears');
/**
 * Path to the directory with standard components.
 * @var string CORE_COMPONENTS_DIR
 * @note Sign @c '*' means the module name.
 */
define('CORE_COMPONENTS_DIR', CORE_DIR . '/modules/*/components');

/**
 * Path to the directory with core PHP files.
 * @var string CORE_GEARS_DIR
 * @note Sign @c '*' means the module name.
 */
define('CORE_GEARS_DIR', CORE_DIR . '/modules/*/gears');

/**
 * Path to the site kernel directory.
 * @var string SITE_KERNEL_DIR
 */
define('SITE_KERNEL_DIR', SITE_DIR . '/kernel');


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
//require_once('Cache.class.php');

//spl_autoload_register(
/*
 * Функция автозагрузки файлов классов
 *
 * @param string $className имя класса
 * @return void
 * @staticvar array $paths массив путей к файлам классов вида [имя класса]=>путь к файлу класса
 */
/*function ($className) {
    $className = simplifyClassName($className);
    static $paths = array();
    //если массив путей не заполнен - заполняем
    if (empty($paths)) {
        //Если мемкеш не заенейблен или значения путей в нем нет
        $mc = E()->getCache();
        if (!$mc->isEnabled() || !($paths = $mc->retrieve(Energine\share\gears\Cache::CLASS_STRUCTURE_KEY))) {
            //собираем в статическую переменную
            $tmp = array_reduce(
                array(
                    CORE_COMPONENTS_DIR,
                    CORE_GEARS_DIR,
                    SITE_KERNEL_DIR,
                    SITE_COMPONENTS_DIR,
                    SITE_GEARS_DIR
                ),
                function ($result, $row) {
                    if (!($cmps = glob($row . '/*.class.php'))) {
                        $cmps = array();
                    }
                    return array_merge($result, $cmps);
                },
                array());

            foreach ($tmp as $fileName) {
                $paths[substr(strrchr($fileName, '/'), 1, -10)] = $fileName;
            }
            if ($mc->isEnabled())
                $mc->store(Energine\share\gears\Cache::CLASS_STRUCTURE_KEY, $paths);
        }
    }

    if (!isset($paths[$className]) || !@require($paths[$className])) {
        throw new Energine\share\gears\SystemException('ERR_NO_CLASS', Energine\share\gears\SystemException::ERR_CRITICAL, $className);
    }
});*/

# устанавливаем свой обработчик ошибок
set_error_handler('nrgnErrorHandler');

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
 * @throws SystemException
 */
function nrgnErrorHandler($errLevel, $message, $file, $line, $errContext) {
    try {
        $e = new Energine\share\gears\SystemException(
            $message,
            Energine\share\gears\SystemException::ERR_DEVELOPER
        );
        throw $e->setFile($file)->setLine($line);
    } catch (\Exception $e) {
        //Если ошибка произошла здесь, то капец
        echo 'Message:', $message, PHP_EOL, 'File:', $file, PHP_EOL, 'Line:', $line, PHP_EOL;
        exit;
    }
}
