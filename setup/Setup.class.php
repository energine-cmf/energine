<?php

/**
 * Класс Setup
 *
 * @package energine
 * @subpackage setup
 * @author dr.Pavka
 * @copyright 2013 Energine
 */

/**
 * Основной функционал установки системы.
 *
 * @package energine
 * @subpackage setup
 * @author dr.Pavka
 */
final class Setup {

    /**
     * Путь к папке загрузок
     */
    const UPLOADS_PATH = 'uploads/public/';

    /**
     * Имя таблицы, в которой хранятся пользовательские загрузки
     */
    const UPLOADS_TABLE = 'share_uploads';

    /**
     * Признак запуска установщика из консоли
     * Вовзращает true, если запуск произведен из консоли, false - если из браузера
     *
     * @var bool
     */
    private $isFromConsole;

    /**
     * Массив конфигурации системы
     *
     * @var array
     */
    private $config;

    /**
     * Массив директорий, которые будут созданы и в которые будут создаваться
     * символические ссылки из директорий ядра и сайта
     *
     * @var array
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
     * @var PDO
     */
    private $dbConnect;

    /**
     * Конструктор класса.
     *
     * @param bool $consoleRun Вид вызова сценария установки
     */
    public function __construct($consoleRun) {
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
     * @throws Exception
     */
    private function filterInput($var) {
        if (preg_match('/^[\~\-0-9a-zA-Z\/\.\_]+$/i', $var))
            return $var;
        else
            throw new Exception('Некорректные данные системных переменных, возможна атака на сервер.' . $var);
    }

    /**
     * Возвращает хост, на котором работае система.
     *
     * @return string
     */
    private function getSiteHost() {
        if (!isset($_SERVER['HTTP_HOST'])
                || $_SERVER['HTTP_HOST'] == ''
        )
            return $this->filterInput($_SERVER['SERVER_NAME']);
        else
            return $this->filterInput($_SERVER['HTTP_HOST']);
    }

    /**
     * Возвращает корневую директорию системы.
     *
     *
     * @return string
     */
    private function getSiteRoot() {
        $siteRoot = $this->filterInput($_SERVER['PHP_SELF']);
        $siteRoot = str_replace('index.php', '', $siteRoot);
        return $siteRoot;
    }

    /**
     * Функция для проверки параметров системы,
     * таких как версия PHP, наличие конфигурационного
     * файла Energine а также файла перечня модулей системы.
     *
     * @throws Exception
     */
    public function checkEnvironment() {

        $this->title('Проверка системного окружения');

        //А что за PHP версия используется?
        if (floatval(phpversion()) < MIN_PHP_VERSION) {
            throw new Exception('Вашему РНР нужно еще немного подрости. Минимальная допустимая версия ' . MIN_PHP_VERSION);
        }
        $this->text('Версия РНР ', floatval(phpversion()), ' соответствует требованиям');

        //При любом действии без конфига нам не обойтись
        if (!file_exists($configName = implode(DIRECTORY_SEPARATOR, array(HTDOCS_DIR, 'system.config.php')))) {
            throw new Exception('Не найден конфигурационный файл system.config.php. По хорошему, он должен лежать в корне проекта.');
        }

        $this->config = include($configName);

        // Если скрипт запущен не с консоли, необходимо вычислить хост сайта и его рут директорию
        /*if (!$this->isFromConsole) {
            $this->config['site']['domain'] = $this->getSiteHost();
            $this->config['site']['root'] = $this->getSiteRoot();
        }*/
        //все вышеизложенное - очень подозрительно
        //что нам мешает просто прочитать значения из конфига?
        //Если бы мы потом это в конфиг писали - то еще куда ни шло ... а так ... до выяснения  - закомментировал

        if (!is_array($this->config)) {
            throw new Exception('Странный какой то конфиг. Пользуясь ним я не могу ничего сконфигурить. Или возьмите нормальный конфиг, или - извините.');
        }

        //маловероятно конечно, но лучше убедиться
        if (!isset($this->config['site']['debug'])) {
            throw new Exception('В конфиге ничего не сказано о режиме отладки. Это плохо. Так я работать не буду.');
        }
        $this->text('Конфигурационный файл подключен и проверен');

        //Если режим отладки отключен - то и говорить дальше не о чем
        if (!$this->isFromConsole && !$this->config['site']['debug']) {
            throw new Exception('Нет. С отключенным режимом отладки я работать не буду, и не просите. Запускайте меня после того как исправите в конфиге ["site"]["debug"] с 0 на 1.');
        }
        if ($this->config['site']['debug']) {
            $this->text('Режим отладки включен');
        } else {
            $this->text('Режим отладки выключен');
        }

        //А задан ли у нас перечень модулей?
        if (!isset($this->config['modules']) && empty($this->config['modules'])) {
            throw new Exception('Странно. Отсутствует перечень модулей. Я могу конечно и сам посмотреть, что находится в папке core/modules, но как то это не кузяво будет. ');
        }
        $this->text('Перечень модулей:', PHP_EOL . ' => ' . implode(PHP_EOL . ' => ', array_values($this->config['modules'])));
    }

    /**
     * Обновляет Host и Root сайта в таблице share_sites.
     *
     */
    private function updateSitesTable() {
        $this->text('Обновляем таблицу share_sites...');

        // получаем все домены из таблицы доменов
        $res = $this->dbConnect->query(
            'SELECT * FROM share_domains'
        );

        $domains = $res->fetchAll();
        $res->closeCursor();

        // обновляем таблицу доменов, если:
        // 1. одна запись в таблице
        // 2. больше одной записи, и поле пустое
        // 3. Доменов вообще нет
        if (
            empty($domains)
            ||
            ($domains and (count($domains) == 1 or (count($domains) >= 1 and $domains[0]['domain_host'] == '')))
        ) {
            $this->dbConnect->query(
                ((empty($domains))?'INSERT INTO':'UPDATE')." share_domains SET domain_host = '" . $this->config['site']['domain'] . "',"
                    . "domain_root = '" . $this->config['site']['root'] . "'"
            );
            if(empty($domains)){
                $domainID = $this->dbConnect->lastInsertId();
                $this->dbConnect->query('INSERT INTO share_domain2site SET site_id=1, domain_id='.$domainID);
            }
        }
    }

    /**
     * Проверка возможности соединения с БД.
     *
     * @throws Exception
     */
    private function checkDBConnection() {

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
     * Фактически, запускает один из режимов установки системы.
     *
     * @param string $action
     * @param array $arguments
     * @throws Exception
     */
    public function execute($action, $arguments) {
        if (!method_exists($this, $methodName = $action . 'Action')) {
            throw new Exception('Подозрительно все это... Либо программисты че то не учли, либо.... произошло непоправимое.');
        }
        call_user_func_array(array($this, $methodName), $arguments);
        //$this->{$methodName}();
    }

    /**
     * Очищает папку Cache от ее содержимого.
     * @todo: определится с именем папки
     *
     */
    private function clearCacheAction() {
        $this->title('Очищаем кеш');
        $this->cleaner(implode(DIRECTORY_SEPARATOR, array(HTDOCS_DIR, 'cache')));
    }

    /**
     * Запуск полной установки системы, включающей генерацию symlinks,
     * проверку соединения с БД и обновление таблицы share_sites.
     *
     */
    private function installAction() {
        $this->checkDBConnection();
        $this->updateSitesTable();
        $this->linkerAction();
        $this->scriptMapAction();
        $this->robotsAction();
    }

    /**
     * Проверка конфигурации модуля СЕО
     *
     * Для правильной работы он должен иметь
     * следующие параметры:
     * -> sitemapSegment - имя сегмента карты сайта (по умолчанию google-sitemap)
     * -> sitemapTemplate - имя файла шаблона карты сайта (по умолчанию google_sitemap)
     * -> maxVideosInMap - максимальное количество записей
     *   в карте расположения видео сайта (по умолчанию 5000)
     *
     * @return boolean
     */
    private function isSeoConfigured() {
        if (!array_key_exists('seo', $this->config)) {
            return false;
        }
        foreach (array('sitemapSegment', 'sitemapTemplate', 'maxVideosInMap') as $seoParam) {
            if (!array_key_exists($seoParam, $this->config['seo'])) {
                return false;
            }
        }
        return true;
    }

    /**
     * Генерация файла robots.txt
     * Создаем ссылки на sitemap всех поддоменов
     *
     */
    private function robotsAction() {
        if (!$this->isSeoConfigured()) {
            $this->title('Не сконфигурирован СЕО модуль. Robots.txt генерируется для запрета индексации сайта.');
            $this->generateRobotsTxt(false);
            return;
        }
        $this->checkDBConnection();
        $this->title('Генерация файла robots.txt');
        $this->generateRobotsTxt();
        $this->title('Добавление информации о сегменте ' . $this->config['seo']['sitemapSegment']);
        $this->createSitemapSegment();
    }

    /**
     * Создаем сегмент google sitemap в share_sitemap
     * и даем на него права на просмотр для не авторизированных
     * пользователей. Имя сегмента следует указать в конфиге.
     *
     */
    private function createSitemapSegment() {
        $this->dbConnect->query('INSERT INTO share_sitemap(site_id,smap_layout,smap_content,smap_segment,smap_pid) '
                . 'SELECT sso.site_id,\'' . $this->config['seo']['sitemapTemplate'] . '.layout.xml\','
                . '\'' . $this->config['seo']['sitemapTemplate'] . '.content.xml\','
                . '\'' . $this->config['seo']['sitemapSegment'] . '\','
                . '(SELECT smap_id FROM share_sitemap ss2 WHERE ss2.site_id = sso.site_id AND smap_pid IS NULL LIMIT 0,1) '
                . 'FROM share_sites sso '
                . 'WHERE site_is_indexed AND site_is_active '
                . 'AND (SELECT COUNT(ssi.site_id) FROM share_sites ssi '
                . 'INNER JOIN share_sitemap ssm ON ssi.site_id = ssm.site_id '
                . 'WHERE ssm.smap_segment = \'' . $this->config['seo']['sitemapSegment'] . '\' AND ssi.site_id = sso.site_id) = 0');
        $smIdsInfo = $this->dbConnect->query('SELECT smap_id FROM share_sitemap WHERE '
                . 'smap_segment = \'' . $this->config['seo']['sitemapSegment'] . '\'');
        while ($smIdInfo = $smIdsInfo->fetch()) {
            $this->dbConnect->query('INSERT INTO share_access_level SELECT ' . $smIdInfo[0] . ',group_id,'
                    . '(SELECT right_id FROM `user_group_rights` WHERE right_const = \'ACCESS_READ\') FROM `user_groups` ');
            $this->dbConnect->query('INSERT INTO share_sitemap_translation(smap_id,lang_id,smap_name,smap_is_disabled) '
                    . 'VALUES (' . $smIdInfo[0] . ',(SELECT lang_id FROM `share_languages` WHERE lang_default),\'Google sitemap\',0)');
        }
    }

    /**
     * Заполняем файл robots.txt ссылками на sitemaps
     * Sitemap: http://example.com/sm.xml
     * Если не сконфигурирован модуль СЕО, то запрещаем индексацию сайта.
     *
     * @param $allowRobots
     * @throws Exception
     */
    private function generateRobotsTxt($allowRobots = true) {
        $file = implode(DIRECTORY_SEPARATOR, array(HTDOCS_DIR, 'robots.txt'));
        if (!is_writable(HTDOCS_DIR)) {
            throw new Exception('Невозможно создать файл robots.txt в ' . HTDOCS_DIR);
        }
        if (!$allowRobots) {
            file_put_contents($file, 'User-agent: *' . PHP_EOL . 'Disallow: /' . PHP_EOL);
            return;
        }
        file_put_contents($file, 'User-agent: *' . PHP_EOL . 'Allow: /' . PHP_EOL);
        $domainsInfo = $this->dbConnect->query('SELECT ss.site_id,sd.domain_protocol,sd.domain_host,sd.domain_root FROM share_sites ss '
                . 'INNER JOIN share_domain2site d2s ON ss.site_id = d2s.site_id '
                . 'INNER JOIN share_domains sd ON  sd.domain_id = d2s.domain_id WHERE ss.site_is_indexed');
        while ($domainInfo = $domainsInfo->fetch()) {
            file_put_contents($file, 'Sitemap: ' . $domainInfo['domain_protocol'] . '://' . $domainInfo['domain_host']
                    . $domainInfo['domain_root'] . $this->config['seo']['sitemapSegment']
                    . PHP_EOL, FILE_APPEND);
        }
    }

    /**
     * Запуск установки системы, включающей генерацию symlinks.
     *
     * @throws Exception
     */
    private function linkerAction() {

        $this->title('Создание символических ссылок');

        foreach ($this->htdocsDirs as $dir) {
            $dir = HTDOCS_DIR . DIRECTORY_SEPARATOR . $dir;

            if (!file_exists($dir)) {
                if (!@mkdir($dir, 0755, true)) {
                    throw new Exception('Невозможно создать директорию:' . $dir);
                }
            }
            else {
                $this->cleaner($dir);
            }
        }

        // создаем симлинки модулей из их физического расположения, описанного в конфиге
        // в папку CORE_DIR . '/modules/'
        $this->text(PHP_EOL . 'Создание символических ссылок в ' . CORE_DIR . ':');
        foreach($this->config['modules'] as $module => $module_path) {
            $symlinked_dir = implode(DIRECTORY_SEPARATOR, array(CORE_DIR, MODULES, $module));
            $this->text('Создание символической ссылки ', $module_path, ' -> ', $symlinked_dir);

            if (file_exists($symlinked_dir) || is_link($symlinked_dir)) {
                unlink($symlinked_dir);
            }

            if(!file_exists($module_path)) {
        	    throw new Exception('Не существует: '.$module_path);
            }

            $modules_dir = implode(DIRECTORY_SEPARATOR, array(CORE_DIR, MODULES));
            if (!is_writeable($modules_dir)) {
                throw new Exception('Нет доступа на запись: ' . $modules_dir );
            }

            symlink($module_path, $symlinked_dir);

        }

        //На этот момент у нас есть все необходимые директории в htdocs и они пустые
        foreach ($this->htdocsDirs as $dir) {

            $this->text(PHP_EOL . 'Обработка ' . $dir . ':');

            //сначала проходимся по модулям ядра
            foreach (array_reverse($this->config['modules']) as $module => $module_path) {
                $this->linkCore(
                    implode(DIRECTORY_SEPARATOR, array(CORE_DIR, MODULES, $module, $dir, '*')),
                    implode(DIRECTORY_SEPARATOR, array(HTDOCS_DIR, $dir)),
                    sizeof(explode(DIRECTORY_SEPARATOR, $dir)));

            }
            $this->linkSite(
                implode(DIRECTORY_SEPARATOR, array(SITE_DIR, MODULES, '*', $dir, '*')),
                implode(DIRECTORY_SEPARATOR, array(HTDOCS_DIR, $dir))
            );
        }

        $this->text('Символические ссылки расставлены');
    }

    private function iterateUploads($directory, $PID = null) {

        //static $counter = 0;

        $iterator = new DirectoryIterator($directory);
        foreach ($iterator as $fileinfo) {
            if (!$fileinfo->isDot() && (substr($fileinfo->getFilename(), 0, 1) != '.')) {

                $uplPath = str_replace('../', '', $fileinfo->getPathname());
                $filename = $fileinfo->getFilename();

                echo $uplPath . PHP_EOL;
                $res = $this->dbConnect->query('SELECT upl_id, upl_pid FROM ' . self::UPLOADS_TABLE . ' WHERE upl_path = "' . $uplPath . '"');
                if(!$res) throw new Exception('ERROR');

                $data = $res->fetch(PDO::FETCH_ASSOC);

                if (empty($data)) {
                    $uplWidth = $uplHeight = 'NULL';
                    if (!$fileinfo->isDir()) {
                        $childsCount = 'NULL';
                        $finfo = new finfo(FILEINFO_MIME_TYPE);
                        $mimeType = $finfo->file($fileinfo->getPathname());

                        switch ($mimeType) {
                            case 'image/jpeg':
                            case 'image/png':
                            case 'image/gif':
                                $tmp = getimagesize($fileinfo->getPathname());
                                $internalType = 'image';
                                $uplWidth = $tmp[0];
                                $uplHeight = $tmp[1];
                                break;
                            case 'video/x-flv':
                            case 'video/mp4':
                                $internalType = 'video';
                                break;
                            case 'text/csv':
                                $internalType = 'text';
                                break;
                            case 'application/zip':
                                $internalType = 'zip';
                                break;
                            default:
                                $internalType = 'unknown';
                                break;
                        }
                        $title = $fileinfo->getBasename('.'.$fileinfo->getExtension());
                    }
                    else {
                        $mimeType = 'unknown/mime-type';
                        $internalType = 'folder';
                        $childsCount = 0;
                        $title = $fileinfo->getBasename();
                    }

                    $PID = (empty($PID))?'NULL':$PID;

                    $r = $this->dbConnect->query($q = sprintf('INSERT INTO ' . self::UPLOADS_TABLE . ' (upl_pid, upl_childs_count, upl_path, upl_filename, upl_name, upl_title,upl_internal_type, upl_mime_type, upl_width, upl_height) VALUES(%s, %s, "%s", "%s", "%s", "%s", "%s", "%s", %s, %s)', $PID, $childsCount, $uplPath, $filename, $title, $title, $internalType, $mimeType, $uplWidth, $uplHeight));
                    if(!$r) throw new Exception('ERROR INSERTING');
                    //$this->text($uplPath);
                    if($fileinfo->isDir()){
                        $newPID = $this->dbConnect->lastInsertId();
                    }

                    //$this->dbConnect->lastInsertId();
                }
                else {
                    $newPID = $data['upl_pid'];
                    $r = $this->dbConnect->query('UPDATE ' . self::UPLOADS_TABLE . ' SET upl_is_active=1 WHERE upl_id="' . $data['upl_id'] . '"');
                    if(!$r) throw new Exception('ERROR UPDATING');
                }
                if ($fileinfo->isDir()) {
                    $this->iterateUploads($fileinfo->getPathname(), $newPID);
                }

            }
        }
    }

    private function syncUploadsAction($uploadsPath = self::UPLOADS_PATH) {
        $this->checkDBConnection();
        $this->title('Синхронизация папки с загрузками');
        $this->dbConnect->beginTransaction();
        if(substr($uploadsPath, -1) == '/'){
            $uploadsPath = substr($uploadsPath, 0, -1);
        }
        $r = $this->dbConnect->query('SELECT upl_id FROM '.self::UPLOADS_TABLE.' WHERE upl_path LIKE "'.$uploadsPath.'"');
        if(!$r){
            throw new Exception('Репозиторий по такому пути не существует');
        }
        $PID = $r->fetchColumn();
        if(!$PID){
            throw new Exception('Странный какой то идентификатор родительский.');
        }
        $uploadsPath .= '/';

        try {
            $this->dbConnect->query('UPDATE ' . self::UPLOADS_TABLE . ' SET upl_is_active=0 WHERE upl_path LIKE "'.$uploadsPath.'%"');
            $this->iterateUploads(implode(DIRECTORY_SEPARATOR, array(HTDOCS_DIR, $uploadsPath)), $PID);
            $this->dbConnect->commit();
        }
        catch (Exception $e) {
            $this->dbConnect->rollBack();
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Рекурсивно очищает папку от того, что в ней было.
     *
     * @param string $dir путь к папке
     */
    private function cleaner($dir) {
        if (is_dir($dir)) {
            if ($dh = opendir($dir)) {
                while ((($file = readdir($dh)) !== false)) {
                    if (!in_array($file, array('.', '..'))) {
                        if (is_dir($file = $dir . DIRECTORY_SEPARATOR . $file)) {
                            if(is_link($file)){
                                unlink($file);
                            }
                            else {
                                $this->cleaner($file);
                                rmdir($file);
                            }
                            $this->text('Удаляем директорию ', $file);
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
     * @param string $globPattern паттерн для выбора файлов
     * @param string $module путь к модулю ядра
     * @param int $level финт ушами для формирования относительных путей для симлинков, при рекурсии инкрементируется
     * @throws Exception
     */
    private function linkCore($globPattern, $module, $level = 1) {

        $fileList = glob($globPattern);

        if (!empty($fileList)) {
            foreach ($fileList as $fo) {
                if (is_dir($fo)) {
                    mkdir($dir = $module . DIRECTORY_SEPARATOR . basename($fo));
                    $this->text('Создаем директорию ', $dir);
                    $this->linkCore($fo . DIRECTORY_SEPARATOR . '*', $dir, $level + 1);
                }
                else {
                    //Если одним из низших по приоритету модулей был уже создан симлинк
                    //то затираем его нафиг
                    if (file_exists($dest = $module . DIRECTORY_SEPARATOR . basename($fo))) {
                        unlink($dest);
                    }
                    $this->text('Создаем симлинк ', $fo, ' --> ', $dest);
                    if (!@symlink($fo, $dest)) {
                        throw new Exception('Не удалось создать символическую ссылку с ' . $fo . ' на ' . $dest);
                    }

                }
            }
        }
    }

    /**
     * Создание symlinks для модулей сайта.
     *
     * @param string $globPattern паттерн для выбора файлов
     * @param string $dir директория, в которой создавать симлинки
     */
    private function linkSite($globPattern, $dir) {

        $fileList = glob($globPattern);
        if (!empty($fileList)) {
            foreach ($fileList as $fo) {

                $fo_stripped = str_replace(SITE_DIR, '', $fo);
                list(, , $module) = explode(DIRECTORY_SEPARATOR, $fo_stripped);
                $new_dir = implode(DIRECTORY_SEPARATOR, array($dir, $module));

                if (!file_exists($new_dir)) {
                    mkdir($new_dir);
                }

                $srcFile = $fo;
                $linkPath = implode(DIRECTORY_SEPARATOR, array($dir, $module, basename($fo_stripped)));
                $this->text('Создаем симлинк ', $srcFile, ' --> ', $linkPath);

                if (file_exists($linkPath)) {
                    unlink($linkPath);
                }

                @symlink($srcFile, $linkPath);
            }
        }
    }

    /**
     * Вывод заголовок текущего действия установки, дополненный красивыми звездочками
     *
     * @param string $text
     */
    private function title($text) {
        echo str_repeat('*', 80), PHP_EOL, $text, PHP_EOL, PHP_EOL;
    }

    /**
     * Выводит все переданные параметры в виде строки.
     *
     * @param string
     */
    private function text() {
        foreach (func_get_args() as $text) {
            echo $text;
        }
        echo PHP_EOL;
    }

    /**
     * Рекурсивно проходится по всем файлам и директориям в папке scripts и возвращает результат
     * в переменную $result
     *
     * @param string $directory
     * @param array $result
     */
    private function iterateScripts($directory, &$result) {

        $iterator = new DirectoryIterator($directory);

        foreach ($iterator as $fileinfo) {
            if ($fileinfo->isFile() && !$fileinfo->isDot() && $fileinfo->getExtension() == 'js') {
                $result[$fileinfo->getBasename('.js')] = $directory . DIRECTORY_SEPARATOR . $fileinfo->getFilename();
            }

            if ($fileinfo->isDir() && !$fileinfo->isDot()) {
                $this->iterateScripts($fileinfo->getPathname(), $result);
            }
        }
    }

    /**
     * Парсер включений ScriptLoader.load()
     *
     * @param string $script полное имя javascript-файла
     * @return array массив зависимостей
     */
    private function parseScriptLoader($script) {
        $result = array();

        $data = file_get_contents($script);
        $r = array();
        if (preg_match_all('/ScriptLoader\.load\((([\s,]{1,})?(\'([a-zA-Z\/.-]{1,})\'){1,}([\s,]{1,})?){1,}\)/', $data, $r)) {
            $s = str_replace(array('ScriptLoader.load', '(', ')', "\r", "\n"), '', (string) $r[0][0]);
            $classes = array_map(function($el){ return str_replace(array('\'', ',',' '), '', $el); }, explode(',', $s));
            $result = $classes;
        }

        return $result;
    }

    /**
     * Записывает массив зависимостей в php файл system.jsmap.php
     *
     * @param array $deps
     */
    private function writeScriptMap($deps) {
        file_put_contents(HTDOCS_DIR . '/system.jsmap.php', '<?php return ' . var_export($deps, true) . ';');
    }

    /**
     * Создает файл system.jsmap.php с массивом зависимостей для JS классов
     */
    private function scriptMapAction() {

        $this->title("Создание карты зависимости Javascript классов");

        $files = array();
        $this->iterateScripts(HTDOCS_DIR . '/scripts', $files);

        $result = array();

        foreach($files as $class => $file) {
            $deps = $this->parseScriptLoader($file);
            if ($deps) {
                $class_dir = str_replace(array(HTDOCS_DIR . '/scripts/', '.js') , '', $file);
                $result[$class_dir] = $deps;
                $this->text($class_dir . ' --> ' . implode(', ', $deps));
            }
        }

        $this->writeScriptMap($result);
    }
}
