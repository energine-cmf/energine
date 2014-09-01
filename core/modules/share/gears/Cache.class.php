<?php
/**
 * @file
 * Cache.
 *
 * It contains the definition to:
 * @code
class Cache;
@endcode
 *
 * @author
 *
 * @version 1.0.0
 */
namespace Energine\share\gears;
/**
 * File cache.
 *
 * @code
class Cache;
@endcode
 *
 * Cache represent files placed in cache directory.
 * Cache file name (without ".cache.php") is used as a key.
 */
class Cache {
    /**
     * Path to the cache.
     * @var string CACHE_DIR
     */
    const CACHE_DIR = '../cache/';

    /**
     * Translation key.
     * @var string TRANSLATIONS_KEY
     * @deprecated
     */
    const TRANSLATIONS_KEY = 'translations';

    /**
     * Cache filename, that stores information about paths.
     * @var string CLASS_STRUCTURE_KEY
     */
    const CLASS_STRUCTURE_KEY = 'class_structure';
    /**
     * Cache filename, that stores an array with data base structure.
     * @var string DB_STRUCTURE_KEY
     */
    const DB_STRUCTURE_KEY = 'db_structure';

    public function __construct() {
        $this->enabled =
                (bool)Object::_getConfigValue('site.cache')
                        && (!(bool)Object::_getConfigValue('site.debug'))
                        && is_dir(self::CACHE_DIR)
                        && is_writable(self::CACHE_DIR);
    }

    /**
     * Get cache state.
     *
     * @return bool
     */
    public function isEnabled() {
        return $this->enabled;
    }

    /**
     * Store data in cache.
     *
     * @param string $key Data key.
     * @param mixed $value Data value.
     * @return bool
     */
    public function store($key, $value) {
        $content = '<?php' . PHP_EOL . 'return ' . var_export($value, true) . ';';
        return (bool)file_put_contents($this->getCacheFileByKey($key), $content);
    }

    /**
     * Get data from cache.
     *
     * @param string $key Data key.
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
     * Remove data from cache.
     *
     * @param string $key Data key.
     */
    public function dispose($key) {
        if ($fileName = $this->cacheFileExists($key)) {
            @unlink($fileName);
        }
    }

    /**
     * Get cache file by data key.
     *
     * @param string $key Data key.
     * @return string
     */
    private function getCacheFileByKey($key) {
        return self::CACHE_DIR . str_replace(DIRECTORY_SEPARATOR, '_', $key) . '.cache.php';

    }

    /**
     * Check if cache file for specific data key exist.
     *
     * @param string $key Data key.
     * @return string|false
     */
    private function cacheFileExists($key) {
        if (file_exists($fileName = $this->getCacheFileByKey($key))) {
            return $fileName;
        }
        return false;
    }
}
