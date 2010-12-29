<?php
/**
 * Содержит набор инициализационных функций и констант
 *
 * @package energine
 * @subpackage core
 * @author pavka
 * @copyright Energine 2007
 */
/**
 * опыт показывает что есть еще люди  пользующиеся register_globals = On
 * не катит
 */
if (ini_get('register_globals')) {
	die('Register_globals directive must be turned off.');
}
/**
 * Хак для cgi mode, где SCRIPT_FILENAME возвращает путь к PHP, вместо пути к текущему исполняемому файлу
 */
if (isset($_SERVER['SCRIPT_FILENAME'])) {
    $_SERVER['SCRIPT_FILENAME'] = (isset($_SERVER['PATH_TRANSLATED']))?$_SERVER['PATH_TRANSLATED']:$_SERVER['SCRIPT_FILENAME'];
}

/**
 * устанавливаем максимальный уровень отображения ошибок
 */
error_reporting(E_ALL | E_STRICT);

/**
 * включаем вывод ошибок и отключаем вывод в HTML
 */
ini_set('display_errors', 1);
ini_set('html_errors', 0);

/**
 * отключаем magic_quotes_runtime
 */
@set_magic_quotes_runtime(1);
/**
 * поскольку magic_quotes_gpc в runtime отключить нельзя, идем на ухищрение
 */
if (get_magic_quotes_gpc()) {
   function recursiveStripslashes($value) {
       $value = is_array($value)?array_map('recursiveStripslashes', $value):stripslashes($value);
       return $value;
   }
   $_POST = array_map('recursiveStripslashes', $_POST);
   $_GET = array_map('recursiveStripslashes', $_GET);
   $_COOKIE = array_map('recursiveStripslashes', $_COOKIE);
   $_REQUEST = array_map('recursiveStripslashes', $_REQUEST);
}

@date_default_timezone_set('Europe/Kiev');

/**
 * Путь к директории пользовательских компонентов
 */
define('SITE_COMPONENTS_DIR', 'site/modules/*/components');
/**
 * Путь к директории пользовательских PHP файлов
 */
define('SITE_GEARS_DIR', 'site/*/gears');

/**
 * Шаблон пути к директориям компонентов стандартных модулей,
 * где * заменяется именем модуля
 */
define('CORE_COMPONENTS_DIR', 'core/modules/*/components');

define('CORE_GEARS_DIR', 'core/modules/*/gears');

/**
 * Путь к директории ядра системы
 */
define('CORE_KERNEL_DIR', 'core/kernel');
/**
 * Путь к директории ядра проекта
 */
define('SITE_KERNEL_DIR', 'site/kernel');


/**
 * Определяем константы прав доступа
 * они должны иметь те же значения что и в таблице user_group_rights + ACCESS_NONE = 0
 * загружать их из таблицы особого смысла не имеет
 */
/**
 * Права отсутствуют
 *
 */
define('ACCESS_NONE', 0);
/**
 * Уровень прав - только чтение
 *
 */
define('ACCESS_READ', 1);
/**
 * Уровень прав - редактирование
 *
 */
define('ACCESS_EDIT', 2);
/**
 * Уровень - полный доступ
 *
 */
define('ACCESS_FULL', 3);
/**
 * Подключаем реестр и мемкешер, нужные нам для автолоадера
 */
require_once('Registry.class.php');
require_once('Memcacher.class.php');

/**
 * Функция автозагрузки файлов классов
 *
 * @param string $className имя класса
 * @return void
 * @staticvar array $paths массив путей к файлам классов вида [имя класса]=>путь к файлу класса
 */
function __autoload($className){
    static $paths = array();
    //если массив путей не заполнен - заполняем
    if (empty($paths)) {
        //Если мемкеш не заенейблен или значения путей в нем нет
        $mc = E()->getCache();
        if(!$mc->isEnabled() || !($paths = $mc->retrieve('class_structure'))){
            //собираем в статическую переменную
            $tmp = glob(
            '{' . implode(',', array(
                CORE_KERNEL_DIR,
                CORE_COMPONENTS_DIR,
                CORE_GEARS_DIR,
                SITE_KERNEL_DIR,
                SITE_COMPONENTS_DIR,
                SITE_GEARS_DIR
            )) . '}/*.class.php',
                GLOB_BRACE
            );
            foreach ($tmp as $fileName) {
                $paths[substr(strrchr($fileName,'/'), 1, -10)] = $fileName;
            }
            $mc->store('class_structure', $paths);
        }
    }

    if (isset($paths[$className])) {
    	require($paths[$className]);
    }
    else {
        trigger_error('no class '.$className.' found');
    }
}

# устанавливаем свой обработчик ошибок
set_error_handler('nrgnErrorHandler');

/**
 * Обработчик ошибок.
 * Преобразует все ошибки в системные исключения с типом ERR_DEVELOPER.
 *
 * @param int $errno
 * @param string $errstr
 * @param string $errfile
 * @param string $errline
 * @return void
 */
function nrgnErrorHandler($errLevel, $message, $file, $line, $errContext) {
    $e = new SystemException(
        $message,
        SystemException::ERR_DEVELOPER
    );
    throw $e->setFile($file)->setLine($line);
}
