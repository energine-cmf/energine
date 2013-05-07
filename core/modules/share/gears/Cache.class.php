<?php

/**
 * Класс файлового кеша
 * Кеш по сути является
 * файлами расположенными в папке CACHE_DIR
 * Имя файла(без расширения .cache.php) используется в виде ключа
 *
 */
class Cache {
    /**
     * путь к кешу
     */
    const CACHE_DIR = '../cache/';

    const TRANSLATIONS_KEY = 'translations';

    const CLASS_STRUCTURE_KEY = 'class_structure';
    const DB_STRUCTURE_KEY = 'db_structure';

    /**
     *
     */
    public function __construct() {
        $this->enabled =
                (bool)Object::_getConfigValue('site.cache')
                        &&
                        (!(bool)Object::_getConfigValue('site.debug'))
                        &&
                        is_dir(self::CACHE_DIR)
                        &&
                        is_writable(self::CACHE_DIR);
    }

    /**
     * Возвращает состояние кеша
     *
     * @return bool
     */
    public function isEnabled() {
        return $this->enabled;
    }

    /**
     * Сохраняет данные в кеше
     *
     * @param  $key string
     * @param  $value mixed
     * @return bool
     */
    public function store($key, $value) {
        $content = '<?php' . PHP_EOL . 'return ' . var_export($value, true) . ';';
        return (bool)file_put_contents($this->getCacheFileByKey($key), $content);
    }

    /**
     * Получает данные из кеша
     * @param  $key string
     * @return mixed
     */
    public function retrieve($key) {
        $result = null;
        if ($fileName = $this->cacheFileExists($key)) {
            $result = include_once($fileName);
        }

        return $result;
    }

    /**
     * @param $key string Имя ключа
     */
    public function dispose($key) {
        if ($fileName = $this->cacheFileExists($key)) {
            @unlink($fileName);
        }
    }

    private function getCacheFileByKey($key) {
        return self::CACHE_DIR . str_replace(DIRECTORY_SEPARATOR, '_', $key) . '.cache.php';

    }

    /**
     * @param $key string имя ключа
     * @return string полное имя и путь к файлу кеша | false если не существует
     */
    private function cacheFileExists($key) {
        if (file_exists($fileName = $this->getCacheFileByKey($key))) {
            return $fileName;
        }
        return false;
    }
}
