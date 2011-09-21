<?php
ob_start();

header('Content-Type: text/plain; charset=utf-8');
//Минимальная версия РНР
define('MIN_PHP_VERSION', 5.3);

//перечень допустимых действий
$acceptableActions = array(
    'install',
    'linker'
);

//действие по умолчанию
$action = 'install';

function say($data) {
    echo PHP_EOL, $data, PHP_EOL, PHP_EOL;
}

function prettyPrint($text){
    echo str_repeat('*', 80), PHP_EOL, $text, PHP_EOL, str_repeat('*', 80), PHP_EOL;
}

/**
 * Для stylesheets scripts images templates/content templates/layout
 * проходимся по модулям ядра и модулям проекта
 * и расставляем симлинки
 *
 * @return void
 */
function doLinker() {
    define('CORE', 'core');
    //Название директории проекта
    define('SITE', 'site');
    //Название директории в которой содержатся модули(как ядра, так и модули проекта)
    define('MODULES', 'modules');
    prettyPrint('Создание символических ссылок');
    
    /**
     * Очищаем папку от того что в ней было
     * @param $dir
     * @return void
     */
    function cleaner($dir) {
        if (is_dir($dir)) {
            if ($dh = opendir($dir)) {
                while ((($file = readdir($dh)) !== false)) {
                    if (!in_array($file, array('.', '..'))) {
                        if (is_dir($file = $dir . DIRECTORY_SEPARATOR . $file)) {
                            cleaner($file);
                            echo 'Удаляем директорию ', $file, PHP_EOL;
                            if(!@rmdir($file)){
                                //может это симлинка
                                unlink($file);
                            }
                        }
                        else {
                            echo 'Удаляем файл ', $file, PHP_EOL;
                            unlink($file);
                        }
                    }
                }
                closedir($dh);
            }
        }
    }

    /**
     * Расставляем симлинки для модулей ядра
     *
     * @param string $globPattern - паттерн для выбора файлов
     * @param string $module путь к модулю ядра
     * @param int $level - финт ушами для формирования относительных путей для симлинков, при рекурсии инкрементируется
     * @return void
     */
    function linkCore($globPattern, $module, $level = 1) {
        $fileList = glob($globPattern);
        if (!empty($fileList)) {
            foreach ($fileList as $fo) {
                if (is_dir($fo)) {
                    mkdir($dir = $module . DIRECTORY_SEPARATOR . basename($fo));
                    linkCore($fo . DIRECTORY_SEPARATOR . '*', $dir, $level + 1);
                }
                else {
                    //Если одним из низших по приоритету модулей был уже создан симлинк
                    //то затираем его нафиг
                    if (file_exists($dest = $module . DIRECTORY_SEPARATOR . basename($fo))) {
                        unlink($dest);
                    }
                    echo 'Создаем симлинк ', str_repeat('..' . DIRECTORY_SEPARATOR, $level) . $fo, ' --> ', $dest, PHP_EOL;
                    symlink(str_repeat('..' . DIRECTORY_SEPARATOR, $level) . $fo, $dest);
                }
            }
        }
    }

    /**
     * Расставляем симлинки для модулей сайта
     * принцип слегка другой чем для ядра
     *
     * @param string $globPattern - паттерн для выбора файлов
     * @param string $dir - путь к модую сайта
     * @return void
     */
    function linkSite($globPattern, $dir) {

        $fileList = glob($globPattern);

        //Тут тоже хитрый финт ушами с относительным путем вычисляющимся как количество уровней вложенности исходной папки + еще один
        $relOffset = str_repeat('..' . DIRECTORY_SEPARATOR, sizeof(explode(DIRECTORY_SEPARATOR, $dir)) + 1);

        if (!empty($fileList)) {
            foreach ($fileList as $fo) {
                list(, , $module) = explode(DIRECTORY_SEPARATOR, $fo);
                if (!file_exists($dir . DIRECTORY_SEPARATOR . $module)) {
                    mkdir($dir . DIRECTORY_SEPARATOR . $module);
                }
                echo 'Создаем симлинк ', $relOffset . $fo, ' на ', $dir . DIRECTORY_SEPARATOR . $module . DIRECTORY_SEPARATOR . basename($fo), PHP_EOL;
                symlink($relOffset . $fo, $dir . DIRECTORY_SEPARATOR . $module . DIRECTORY_SEPARATOR . basename($fo));
            }
        }
    }

    $destDirs = array(
        'images',
        'scripts',
        'stylesheets',
        'templates/content',
        'templates/icons',
        'templates/layout'
    );

    foreach ($destDirs as $dir) {
        if (!file_exists($dir)) {
            if (!@mkdir($dir, 0755, true)) {
                throw new Exception('Невозможно создать директорию:' . $dir);
            }
        }
        else {
            cleaner($dir);
        }
    }
    //На этот момент у нас есть все необходимые директории в htdocs и они пустые
    foreach ($destDirs as $dir) {
        //сначала проходимся по модулям ядра
        foreach (array_reverse($GLOBALS['installedModules']) as $module) {
            linkCore(CORE . DIRECTORY_SEPARATOR . MODULES . DIRECTORY_SEPARATOR . $module . DIRECTORY_SEPARATOR . $dir . DIRECTORY_SEPARATOR . '*', $dir, sizeof(explode(DIRECTORY_SEPARATOR, $dir)));

        }
        linkSite(
            SITE . DIRECTORY_SEPARATOR . MODULES . DIRECTORY_SEPARATOR . '*' . DIRECTORY_SEPARATOR . $dir . DIRECTORY_SEPARATOR . '*',
            $dir
        );
    }

    prettyPrint('Символические ссылки расставлены');
}

/**
 * Производим проверку коннекта к БД
 * запускаем линкер
 * @return void
 */
function doInstall() {
    doLinker();
    prettyPrint('Установка завершена успешно');
}

//Смотрим а как запущен сетап(консоль/веб)
//Ориентируемся на наличие $argv - как показатель
if (isset($argv)) {
    $args = $argv;
    //консоль
    array_shift($args);
}
else {
    //веб
    $args = array_keys($_GET);
}
//если нам в параметрах пришло что то очень похожее на допустимое действие
//то считаем, что это оно и есть
if (!empty($args) && in_array($args[0], $acceptableActions)) {
    list($action) = $args;
}


try {
    prettyPrint('Проверка системного окружения');
    
    //А что за PHP версия используется?
    if (floatval(phpversion()) < MIN_PHP_VERSION) {
        throw new Exception('Вашему РНР нужно еще немного подрости. Минимальная допустимая версия ' . MIN_PHP_VERSION);
    }
    echo 'Версия РНР ', floatval(phpversion()), ' соответствует требованиям',PHP_EOL;

    //При любом действии без конфига нам не обойтись
    if (!file_exists($configName = 'system.config.php')) {
        throw new Exception('Не найден конфигурационный файл system.config.php. По хорошему, он должен лежать в корне проекта.');
    }

    $config = include_once($configName);

    if (!is_array($config)) {
        throw new Exception('Странный какой то конфиг. Пользуясь ним я не могу ничего сконфигурить. Или возьмите нормальный конфиг, или - извините.');
    }

    //маловероятно конечно, но лучше убедиться
    if (!isset($config['site']['debug'])) {
        throw new Exception('В конфиге ничего не сказано о режиме отладки. Это плохо. Так я работать не буду.');
    }
    echo 'Конфигурационный файл подключен и проверен', PHP_EOL;
    
    //Если режим отладки отключен - то и говорить дальше не о чем
    if (!$config['site']['debug']) {
        throw new Exception('Нет. С отключенным режимом отладки я работать не буду, и не просите. Запускайте меня после того как исправите в конфиге ["site"]["debug"] с 0 на 1.');
    }
    echo 'Режим отладки включен', PHP_EOL;

    //А задан ли у нас перечень модулей?
    if (!isset($config['modules']) && empty($config['modules'])) {
        throw new Exception('Странно. Отсутствует перечень модулей. Я могу конечно и сам посмотреть, что находится в папке core/modules, но как то это не кузяво будет. ');
    }
    echo 'Перечень модулей:', implode(PHP_EOL, $installedModules = $config['modules']),PHP_EOL;

    
    //Ну вроде как все проверили
    //на этот момент у нас есть вся необходимая информация
    //как для инсталляции так и для линкера

    //Запускаем одноименную функцию
    //Тут позволили себе использваоть переменное имя функции поскольку все равно это точно одно из приемлимых значений
    //впрочем наверное возможны варианты
    if (!function_exists($funcName = 'do' . ucfirst($action))) {
        throw new Exception('Это подозрительно. Как могло получиться, что нужно исполнять функцию которой не существует?');
    }

    call_user_func($funcName);
}
catch (Exception $e) {
    echo 'При установке все пошло не так.', PHP_EOL, 'А точнее :', PHP_EOL, $e->getMessage();
}

$data = ob_get_contents();
ob_end_clean();
say($data);
exit;
