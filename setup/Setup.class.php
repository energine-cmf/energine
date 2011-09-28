<?php
/**
 * Created by JetBrains PhpStorm.
 * User: pavka
 * Date: 9/22/11
 * Time: 11:17 AM
 * To change this template use File | Settings | File Templates.
 */


/**
 * Основной функционал установки системы.
 *
 * @package energine
 * @subpackage setup
 * @author dr.Pavka
 */
class Setup {

    /**
     * @access private
     * @var bool  true - установка запущенна из консоли false - с браузера
     */
    private $isFromConsole;

    /**
     * @access private
     * @var array конфиг системы
     */
    private $config;

    /**
     * @access private
     * @var PDO Подключение к БД
     */
    private $dbConnect;

    /**
     * @access private
     * @var array директории для последующего создания символических ссылок
     */
    private $htdocsDirs = array(
        'images',
        'scripts',
        'stylesheets',
        'templates/content',
        'templates/icons',
        'templates/layout'
    );

    /**
     * Конструктор класса.
     * 
     * @param bool $consoleRun Вид вызова сценария установки
     * @access public
     */

    function __construct($consoleRun) {
        header('Content-Type: text/plain; charset=' . CHARSET);
        $this->title('Средство настройки CMF Energine');
        $this->isFromConsole = $consoleRun;
        $this->checkEnvironment();
    }

    /**
     * Очистка переменных для предотвращения возможных атак.
     * Если входящяя строка содержит символы помимо
     * Букв, цифр, -, . , /
     * Будет возвращена строка error
     *
     * @param string $var
     * @return string
     * @access private
     */

    private function filterInput($var) {
        if(preg_match('/^[\-0-9a-zA-Z\/\.]+$/i', $var))
            return $var;
        else
            throw new Exception('Некорректные данные системных переменных, возможна атака на сервер.');
    }

    /**
     * Возвращает хост, на котором работае система.
     * 
     * @return string
     * @access private
     */

    private function getSiteHost(){
        if(!isset($_SERVER['HTTP_HOST'])
            ||$_SERVER['HTTP_HOST']=='')
            return $this->filterInput($_SERVER['SERVER_NAME']);
        else
            return $this->filterInput($_SERVER['HTTP_HOST']);
    }

    /**
     * Возвращает корневую директорию системы.
     * 
     * @return string
     * @access private
     */

    private function getSiteRoot(){
        $siteRoot = $this->filterInput($_SERVER['PHP_SELF']);
        $siteRoot = str_replace('setup/index.php', '', $siteRoot);
        return $siteRoot;
    }

    /**
     * Функция для проверки параметров системы,
     * таких как версия PHP, наличие конфигурационного
     * файла Energine а также файла перечня модулей системы.
     *
     * @return void
     * @access public
     */

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

        // Если скрипт запущен не с консоли, необходимо вычислить хост сайта и его рут директорию
        if (!$this->isFromConsole) {
            $this->config['site']['domain'] = $this->getSiteHost();
            $this->config['site']['root'] = $this->getSiteRoot();
        }
        
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

    /**
     * Обновляет Host и Root сайта в таблице share_sites.
     *
     * @return void
     * @access private
     */

    private function updateSitesTable(){
        $this->text('Обновляем таблицу share_sites...');
        $this->dbConnect->query("UPDATE share_sites SET site_host = '".$this->config['site']['domain']."',"
                                ."site_root = '".$this->config['site']['root']."'");
    }

    /**
     * Проверка возможности соединения с БД.
     *
     * @return void
     * @access public
     */

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
            
           $this->dbConnect = $connect;
        }
        catch (Exception $e) {
            throw new Exception('Не удалось соединиться с БД по причине: ' . $e->getMessage());
        }
        restore_error_handler();
        $connect->query('SET NAMES utf8');

        $this->text('Соединение с БД ', $dbInfo['db'], ' успешно установлено');
    }

    /**
     * Функция для вызова метода класса.
     * Фактически, запускает один из режимов
     * установки системы.
     *
     * @param string $action
     * @return void
     * @access public
     */

    public function execute($action) {
        if (!method_exists($this, $action)) {
            throw new Exception('Подозрительно все это... Либо программисты че то не учли, либо.... произошло непоправимое.');
        }
        $this->{
        $action
        }();
    }

    /**
     * Запуск полной установки системы,
     * включающей генерацию symlinks и
     * проверку соединения с БД.
     * 
     * @return void
     * @access private
     */

    private function install() {
        $this->checkDBConnection();
        $this->updateSitesTable();
        $this->linker();
    }

    /**
     * Запуск установки системы,
     * включающей генерацию symlinks.
     *
     * @return void
     * @access private
     */

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

    /**
     * Очищаем папку от того что в ней было
     * 
     * @param $dir
     * @return void
     * @access private
     */

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

    /**
     * Расставляем симлинки для модулей ядра
     *
     * @param string $globPattern - паттерн для выбора файлов
     * @param string $module путь к модулю ядра
     * @param int $level - финт ушами для формирования относительных путей для симлинков, при рекурсии инкрементируется
     * @return void
     * @access private
     */

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

    /**
     * Создание symlinks для модулей сайта.
     *
     * @param string $globPattern
     * @param string $dir
     * @return void
     * @access private
     */

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

    /**
     * Вывод названия текущего действия установки.
     *
     * @param string $text
     * @return void
     * @access private
     */

    private function title($text) {
        echo str_repeat('*', 80), PHP_EOL, $text, PHP_EOL, PHP_EOL;
    }

    /**
     * Выводит все переданные ей параметры в виде строки.
     *
     * @param string $text
     * @return void
     * @access private
     */

    private function text() {
        foreach (func_get_args() as $text) {
            echo $text;
        }
        echo PHP_EOL;
    }

}
