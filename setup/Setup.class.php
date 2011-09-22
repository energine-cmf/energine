<?php
/**
 * Created by JetBrains PhpStorm.
 * User: pavka
 * Date: 9/22/11
 * Time: 11:17 AM
 * To change this template use File | Settings | File Templates.
 */

class Setup {

    private $config;
    private $htdocsDirs = array(
        'images',
        'scripts',
        'stylesheets',
        'templates/content',
        'templates/icons',
        'templates/layout'
    );

    function __construct() {
        header('Content-Type: text/plain; charset=' . CHARSET);
        $this->title('Средство настройки CMF Energine');
        $this->checkEnvironment();
    }

    public function checkEnvironment() {
        $this->title('Проверка системного окружения');

        //А что за PHP версия используется?
        if (floatval(phpversion()) < MIN_PHP_VERSION) {
            throw new Exception('Вашему РНР нужно еще немного подрости. Минимальная допустимая версия ' . MIN_PHP_VERSION);
        }
        $this->text('Версия РНР ', floatval(phpversion()), ' соответствует требованиям');

        //При любом действии без конфига нам не обойтись
        if (!file_exists($configName = '../system.config.php')) {
            throw new Exception('Не найден конфигурационный файл system.config.php. По хорошему, он должен лежать в корне проекта.');
        }

        $this->config = include_once($configName);

        if (!is_array($this->config)) {
            throw new Exception('Странный какой то конфиг. Пользуясь ним я не могу ничего сконфигурить. Или возьмите нормальный конфиг, или - извините.');
        }

        //маловероятно конечно, но лучше убедиться
        if (!isset($this->config['site']['debug'])) {
            throw new Exception('В конфиге ничего не сказано о режиме отладки. Это плохо. Так я работать не буду.');
        }
        $this->text('Конфигурационный файл подключен и проверен');

        //Если режим отладки отключен - то и говорить дальше не о чем
        if (!$this->config['site']['debug']) {
            throw new Exception('Нет. С отключенным режимом отладки я работать не буду, и не просите. Запускайте меня после того как исправите в конфиге ["site"]["debug"] с 0 на 1.');
        }
        $this->text('Режим отладки включен');

        //А задан ли у нас перечень модулей?
        if (!isset($this->config['modules']) && empty($this->config['modules'])) {
            throw new Exception('Странно. Отсутствует перечень модулей. Я могу конечно и сам посмотреть, что находится в папке core/modules, но как то это не кузяво будет. ');
        }
        $this->text('Перечень модулей:', implode(PHP_EOL, $this->config['modules']));
    }


    public function checkDBConnection() {
        $this->title('Настройки базы данных');
        if (!isset($this->config['database']) || empty($this->config['database'])) {
            throw new Exception('В конфиге нет информации о подключении к базе данных');
        }
        $dbInfo = $this->config['database'];

        //валидируем все скопом
        foreach (array('host' => 'адрес хоста', 'db' => 'имя БД', 'username' => 'имя пользователя', 'password' => 'пароль') as $key => $description) {
            if (!isset($dbInfo[$key]) && empty($dbInfo[$key])) {
                throw new Exception('Удивительно, но не указан параметр: ' . $description . '  (["database"]["' . $key . '"])');
            }
        }
        try {
            //Поскольку ошибка при конструировании ПДО объекта генерит кроме исключения еще и варнинги
            //используем пустой обработчик ошибки с запретом всплывания(return true)
            //все это сделано только для того чтобы не выводился варнинг

            set_error_handler(create_function('', 'return true;'));
            $connect = new PDO(
                sprintf(
                    'mysql:host=%s;port=%s;dbname=%s',

                    $dbInfo['host'],
                    (isset($dbInfo['port']) && !empty($dbInfo['port'])) ? $dbInfo['port'] : 3306,
                    $dbInfo['db']
                ),
                $dbInfo['username'],
                $dbInfo['password'],
                array(
                     PDO::ATTR_PERSISTENT => false,
                     PDO::ATTR_EMULATE_PREPARES => true,
                     PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true
                ));
        }
        catch (Exception $e) {
            throw new Exception('Не удалось соединиться с БД по причине: ' . $e->getMessage());
        }
        restore_error_handler();
        $connect->query('SET NAMES utf8');

        $this->text('Соединение с БД ', $dbInfo['db'], ' успешно установлено');
    }

    public function execute($action) {
        if (!method_exists($this, $action)) {
            throw new Exception('Подозрительно все это... Либо программисты че то не учли, либо.... произошло непоправимое.');
        }
        $this->{
        $action
        }();
    }

    private function install() {
        $this->checkDBConnection();
        $this->linker();
    }

    private function linker() {

        $this->title('Создание символических ссылок');


        foreach ($this->htdocsDirs as $dir) {
            $dir = '../'.$dir;
            
            if (!file_exists($dir)) {
                if (!@mkdir($dir, 0755, true)) {
                    throw new Exception('Невозможно создать директорию:' . $dir);
                }
            }
            else {
                $this->cleaner($dir);
            }
        }
        //На этот момент у нас есть все необходимые директории в htdocs и они пустые
        foreach ($this->htdocsDirs as $dir) {

            //сначала проходимся по модулям ядра
            foreach (array_reverse($this->config['modules']) as $module) {
                $this->linkCore(
                    '../' . CORE . DIRECTORY_SEPARATOR . MODULES . DIRECTORY_SEPARATOR . $module . DIRECTORY_SEPARATOR . $dir . DIRECTORY_SEPARATOR . '*',
                    '../'.$dir,
                    sizeof(explode(DIRECTORY_SEPARATOR, $dir)));

            }
            $this->linkSite(
                '../' . SITE . DIRECTORY_SEPARATOR . MODULES . DIRECTORY_SEPARATOR . '*' . DIRECTORY_SEPARATOR . $dir . DIRECTORY_SEPARATOR . '*',
                '../'.$dir
            );
        }

        $this->text('Символические ссылки расставлены');
    }

    private function cleaner($dir) {
        if (is_dir($dir)) {
            if ($dh = opendir($dir)) {
                while ((($file = readdir($dh)) !== false)) {
                    if (!in_array($file, array('.', '..'))) {
                        if (is_dir($file = $dir . DIRECTORY_SEPARATOR . $file)) {
                            $this->cleaner($file);
                            $this->text('Удаляем директорию ', $file);
                            if (!@rmdir($file)) {
                                //может это симлинка
                                unlink($file);
                            }
                        }
                        else {
                            $this->text('Удаляем файл ', $file);
                            unlink($file);
                        }
                    }
                }
                closedir($dh);
            }
        }
    }

    private function linkCore($globPattern, $module, $level = 1) {

        $fileList = glob($globPattern);

        if (!empty($fileList)) {
            foreach ($fileList as $fo) {
                if (is_dir($fo)) {
                    mkdir($dir = $module . DIRECTORY_SEPARATOR . basename($fo));
                    $this->linkCore($fo . DIRECTORY_SEPARATOR . '*', $dir, $level + 1);
                }
                else {
                    //Если одним из низших по приоритету модулей был уже создан симлинк
                    //то затираем его нафиг
                    if (file_exists($dest = $module . DIRECTORY_SEPARATOR . basename($fo))) {
                        unlink($dest);
                    }
                    $this->text('Создаем симлинк ', str_repeat('..' . DIRECTORY_SEPARATOR, $level) . $fo, ' --> ', $dest);
                    if(!@symlink(str_repeat('..' . DIRECTORY_SEPARATOR, $level-1) . $fo, $dest)){
                        throw new Exception('Не удлось создать символическую ссылку с '.str_repeat('..' . DIRECTORY_SEPARATOR, $level-1) . $fo.' на '. $dest);
                    }

                }
            }
        }
    }

    private function linkSite($globPattern, $dir) {
        $fileList = glob($globPattern);
        //Тут тоже хитрый финт ушами с относительным путем вычисляющимся как количество уровней вложенности исходной папки + еще один
        $relOffset = str_repeat('..' . DIRECTORY_SEPARATOR, sizeof(explode(DIRECTORY_SEPARATOR, $dir)) -1 );

        if (!empty($fileList)) {
            foreach ($fileList as $fo) {
                list(, , ,$module) = explode(DIRECTORY_SEPARATOR, $fo);
                if (!file_exists($dir . DIRECTORY_SEPARATOR . $module)) {
                    mkdir($dir . DIRECTORY_SEPARATOR . $module);
                }
                $this->text('Создаем симлинк ', $relOffset . $fo, ' на ', $dir . DIRECTORY_SEPARATOR . $module . DIRECTORY_SEPARATOR . basename($fo));
                if (file_exists($dir . DIRECTORY_SEPARATOR . $module . DIRECTORY_SEPARATOR . basename($fo))) {
                    unlink($dir . DIRECTORY_SEPARATOR . $module . DIRECTORY_SEPARATOR . basename($fo));
                }
                symlink($relOffset . $fo, $dir . DIRECTORY_SEPARATOR . $module . DIRECTORY_SEPARATOR . basename($fo));
            }
        }
    }

    private function title($text) {
        echo str_repeat('*', 80), PHP_EOL, $text, PHP_EOL, PHP_EOL;
    }

    private function text() {
        foreach (func_get_args() as $text) {
            echo $text;
        }
        echo PHP_EOL;
    }

}
