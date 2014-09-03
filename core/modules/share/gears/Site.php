<?php
/**
 * @file
 * Site.
 *
 * It contains the definition to:
 * @code
class Site;
@endcode
 *
 * @author d.pavka
 * @copyright d.pavka@gmail.com
 *
 * @version 1.0.0
 */
namespace Energine\share\gears;
/**
 * Site.
 *
 * @code
class Site;
@endcode
 */
class Site extends DBWorker {
    /**
     * Site data.
     * @var array $data
     */
    private $data;
    /**
     * Site translations.
     * @var array $siteTranslationsData
     */
    static private $siteTranslationsData;
    /**
     * Flag, indicates if extended properties 'share_sites_properties' table exists.
     * @var string
     */
    public static $isPropertiesTableExists = null;

    /**
     * @param array $data Data.
     */
    public function __construct($data) {
        parent::__construct();
        $this->data = convertFieldNames($data, 'site_');
        if(is_null(self::$isPropertiesTableExists)) {
            self::$isPropertiesTableExists = $this->dbh->tableExists('share_sites_properties');
        }
    }

    /**
     * Load site.
     * Return the information about all sites in the form of an array of Site objects.
     * Right away all info is cached from translation table, because ata this moment current language is not yet defined.
     *
     * @return Site[]
     */
    public static function load() {
        $result = array();
        $res = E()->getDB()->select('share_sites');
        foreach ($res as $siteData) {
            $result[$siteData['site_id']] = new Site($siteData);
        }
        $res = E()->getDB()->select('share_sites_translation');
        self::$siteTranslationsData = array();
        $f = function ($row) {
            unset($row['lang_id'], $row['site_id']);
            return $row;
        };
        foreach ($res as $row) {
            self::$siteTranslationsData[$row['lang_id']][$row['site_id']] = $f($row);

        }
        return $result;
    }

    /**
     * Load domain information.
     */
    public function setDomain($domainData) {
        $this->data = array_merge($this->data, $domainData);
        $this->data['base'] =
            $this->data['protocol'] . '://' .
                $this->data['host'] . (($this->data['port'] == 80) ? '' : ':' . $this->data['port']) .
                $this->data['root'];
    }

    /**
     * Magic @c get method.
     *
     * @param string $propName Property name.
     * @return mixed
     */
    public function __get($propName) {
        $result = null;
        if (isset($this->data[$propName])) {
            $result = $this->data[$propName];
        } elseif (strtolower($propName) == 'name') {
            $result = $this->data[$propName] = self::$siteTranslationsData[E()->getLanguage()->getCurrent()][$this->data['id']]['site_name'];
        } elseif (self::$isPropertiesTableExists) {
            $res = $this->data[$propName] = $this->dbh->getScalar(
                'SELECT prop_value FROM share_sites_properties
                    WHERE prop_name = %s
                    AND (site_id = %s
                    OR site_id IS NULL)
                    ORDER BY site_id DESC
                    LIMIT 1',
                $propName,
                $this->data['id']
            );
            $result = (false !== $res)? $res: $result;
        }
        return $result;
    }

}