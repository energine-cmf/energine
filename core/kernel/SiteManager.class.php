<?php
/**
 * Содержит класс SiteManager
 *
 * @package energine
 * @subpackage kernel
 * @author d.pavka
 * @copyright d.pavka@gmail.com
 */

/**
 * Работа с сайтам
 *
 * @package energine
 * @subpackage kernel
 * @author d.pavka@gmail.com
 * @final
 */
final class SiteManager extends DBWorker implements Iterator {
    /**
     * Инстанс текущего класса
     *
     * @access private
     * @var SiteManager
     * @static
     */
    //private static $instance;

    /**
     * Данные о всех зарегистрированных сайтах
     *
     * @access private
     * @var Site[]
     */
    private $data;
    /**
     * Индекс используемый при итерации
     *
     *
     * @access private
     * @var int
     * @static
     */
    private static $index = 0;

    /**
     * Идентификатор текущего сайта
     *
     * @access private
     * @var int
     */
    private $currentSiteID = null;

    /**
     * Конструктор класса
     *
     * @access private
     */
    public function __construct() {
        parent::__construct();
        $uri = URI::create();
        $this->data = Site::load();

        $request = 'SELECT d . * , site_id as domain_site
          FROM `share_domains` d
          LEFT JOIN share_domain2site d2c
          USING ( domain_id ) ';
        $res = $this->dbh->select($request);
        if(empty($res)){
            throw new SystemException('ERR_NO_SITE', SystemException::ERR_DEVELOPER);
        }
        foreach ($res as $domainData) {
            $domainData = convertFieldNames($domainData, 'domain_');
            if(!isset($domainData['site'])){
                throw new SystemException('ERR_NO_SITE_DOMAIN', SystemException::ERR_DEVELOPER);
            }
            if($domainData['isDefault']){
                $tmp = $domainData;
                unset($tmp['isDefault'], $tmp['id'], $tmp['site']);
                $this->data[$domainData['site']]->setDomain($tmp);
                unset($tmp);
            }
            if (
                ($domainData['protocol'] == $uri->getScheme()) &&
                ($domainData['host'] == $uri->getHost()) &&
                ($domainData['port'] == $uri->getPort())
            ) {
                $realPathSegments = array_values(array_filter(explode('/', $domainData['root'])));
                $pathSegments = array_slice($uri->getPath(false), 0, sizeof($realPathSegments));
                if ($realPathSegments == $pathSegments) {
                    $this->currentSiteID = $domainData['site'];
                    unset($domainData['isDefault'], $domainData['id'], $domainData['site']);
                    $this->data[$this->currentSiteID]->setDomain($domainData);
                }
            }
        }

        if (is_null($this->currentSiteID)) {
            foreach ($this->data as $siteID => $site) {
                if ($site->isDefault == 1) {
                    $this->currentSiteID = $siteID;
                }
            }
        }
        //Если текущий сайт не активный
        if (!$this->data[$this->currentSiteID]->isActive) {
            throw new SystemException('ERR_403', SystemException::ERR_403);
        }
    }

    /**
     * Возвращает екземпляр объекта Site по идентификатору
     *
     * @return Site
     * @access public
     */
    public function getSiteByID($siteID) {
        if (!isset($this->data[$siteID])) {
            throw new SystemException('ERR_NO_SITE', SystemException::ERR_DEVELOPER, $siteID);
        }
        return $this->data[$siteID];
    }

    /**
     * Возвращает экземпляр объекта сайт по идентфикатору страницы
     *
     * @param int идентфикатор страницы
     * @return Site
     * @access public
     */
    public function getSiteByPage($pageID) {
        return $this->getSiteByID(
            simplifyDBResult(
                $this->dbh->select('share_sitemap', 'site_id', array('smap_id' => $pageID)),
                'site_id',
                true
            )
        );
    }

    /**
     * Returns current's site
     *
     * @return Site
     * @access public
     */
    public function getCurrentSite() {
        return $this->data[$this->currentSiteID];

    }

    /**
     * Возвращает сайт по умолчанию
     *
     * @return Site
     * @access public
     */
    public function getDefaultSite() {
        foreach ($this->data as $site) {
            if ($site->isDefault) {
                return $site;
            }
        }
        throw new SystemException('ERR_NO_DEFAULT_SITE', SystemException::ERR_DEVELOPER);
    }

    /**
     * Возвращает текущий элемент при итерации
     *
     * @return Site
     * @access public
     * @see Iterator
     */
    public function current() {
        $siteIDs = array_keys($this->data);

        return $this->data[$siteIDs[self::$index]];
    }

    /**
     * Возвращает идентификатор текущего сайта(при итерации только)
     *
     * @return int
     * @access public
     * @see Iterator
     */
    public function key() {
        $siteIDs = array_keys($this->data);
        return $siteIDs[self::$index];
    }

    /**
     * Передвигает счетчик на следующий елемент
     *
     * @return void
     * @access public
     * @see Iterator
     */
    public function next() {
        self::$index++;
    }

    /**
     * Сбрасывает счетчик текущих елементов на начало
     *
     * @return void
     * @access public
     * @see Iterator
     */
    public function rewind() {
        self::$index = 0;
    }

    /**
     * Возвращает флаг указывающий существует ли елемент
     *
     * @return boolean
     * @access public
     * @see Iterator
     */
    public function valid() {
        $siteIDs = array_keys($this->data);
        return isset($siteIDs[self::$index]);
    }

}