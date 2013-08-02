<?php
/**
 * Содержит класс Site
 *
 * @package energine
 * @subpackage kernel
 * @author d.pavka
 * @copyright d.pavka@gmail.com
 */

/**
 * Работа с сайтом
 *
 * @package energine
 * @subpackage kernel
 * @author d.pavka@gmail.com
 * @property string id
 * @property string base
 */
class Site extends DBWorker {
    /**
     * Данные сайта
     *
     * @access private
     * @var array
     */
    private $data;
    /**
     * Переводы
     * @var array
     */
    static private $siteTranslationsData;

    /**
     * Конструктор класса
     *
     *
     * @param array $data
     * @access public
     */
    public function __construct($data) {
        parent::__construct();
        $this->data = convertFieldNames($data, 'site_');
    }

    /**
     * Возвращает информацию о всех сайтах в виде массив объектов Site
     * Сразу кешируем инфу из таблицы переводов
     * Поскольку на этот момент текущий язык еще не известен
     * @static
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
     * Загружается информация о домене
     *
     * @return void
     */
    public function setDomain($domainData) {
        $this->data = array_merge($this->data, $domainData);
        $this->data['base'] =
            $this->data['protocol'] . '://' .
                $this->data['host'] . (($this->data['port'] == 80) ? '' : ':' . $this->data['port']) .
                $this->data['root'];
    }

    /**
     * @param $propName
     * @return null
     */
    public function __get($propName) {
        $result = null;
        if (isset($this->data[$propName])) {
            $result = $this->data[$propName];
        } elseif (strtolower($propName) == 'name') {
            $result = $this->data[$propName] = self::$siteTranslationsData[E()->getLanguage()->getCurrent()][$this->data['id']]['site_name'];
        }
        return $result;
    }

}