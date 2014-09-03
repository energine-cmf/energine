<?php
/**
 * @file
 * SiteManager.
 *
 * It contains the definition to:
 * @code
final class SiteManager;
@endcode
 *
 * @author d.pavka
 * @copyright d.pavka@gmail.com
 *
 * @version 1.0.0
 */
namespace Energine\share\gears;
/**
 * Site manager.
 *
 * @code
final class SiteManager;
@endcode
 *
 * @final
 */
final class SiteManager extends DBWorker implements \Iterator {
    /*
     * Instance of the current class.
     *
     * @var SiteManager $instance
     */
    //private static $instance;

    /**
     * Data about all registered sites.
     * Array of Site's.
     * @var array $data
     */
    private $data;
    /**
     * Iteration index.
     * @var int $index
     */
    private static $index = 0;

    /**
     * Current site ID.
     * @var int $currentSiteID
     */
    private $currentSiteID = null;

    /**
     * @copydoc DBWorker::__construct
     *
     * @throws SystemException 'ERR_NO_SITE'
     * @throws SystemException 'ERR_403'
     */
    public function __construct() {
        parent::__construct();
        $uri = URI::create();
        $this->data = Site::load();

        if (!($this->getConfigValue('site.debug')
              && $res = $this->getConfigValue('site.dev_domains'))
        ) {
            $request = 'SELECT d . * , site_id as domain_site
                      FROM `share_domains` d
                      LEFT JOIN share_domain2site d2c
                      USING ( domain_id ) ';
            $res = $this->dbh->select($request);
        }

        if (empty($res) || !is_array($res)) {
            throw new SystemException('ERR_NO_SITE', SystemException::ERR_DEVELOPER);
        }
        foreach ($res as $domainData) {
            $domainData = convertFieldNames($domainData, 'domain_');
            //Если не установлен уже домен - для сайта - дописываем
            //по сути первый домен будет дефолтным
            if (isset($domainData['site']) && is_null($this->data[$domainData['site']]->base)) {
                $tmp = $domainData;
                unset($tmp['id'], $tmp['site']);
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
                    unset($domainData['id'], $domainData['site']);
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
     * Get exemplar of Site object by his ID.
     *
     * @param int $siteID Site ID.
     * @return Site
     *
     * @throws SystemException 'ERR_NO_SITE'
     */
    public function getSiteByID($siteID) {

        if (!isset($this->data[$siteID])) {
            throw new SystemException('ERR_NO_SITE', SystemException::ERR_DEVELOPER, $siteID);
        }
        return $this->data[$siteID];
    }

    /**
     * Get exemplar of Site object by his page ID.
     *
     * @param int $pageID Page ID.
     * @return Site
     */
    public function getSiteByPage($pageID) {
        return $this->getSiteByID(
            $this->dbh->getScalar('share_sitemap', 'site_id', array('smap_id' => $pageID))
        );
    }

    /**
     * Returns current site.
     *
     * @return Site
     */
    public function getCurrentSite() {
        return $this->data[$this->currentSiteID];

    }

    /**
     * Get default site.
     *
     * @return Site
     *
     * @throws SystemException 'ERR_NO_DEFAULT_SITE'
     */
    public function getDefaultSite() {
        foreach ($this->data as $site) {
            if ($site->isDefault) {
                return $site;
            }
        }
        throw new SystemException('ERR_NO_DEFAULT_SITE', SystemException::ERR_DEVELOPER);
    }

    public function current() {
        $siteIDs = array_keys($this->data);

        return $this->data[$siteIDs[self::$index]];
    }

    public function key() {
        $siteIDs = array_keys($this->data);
        return $siteIDs[self::$index];
    }

    public function next() {
        self::$index++;
    }

    public function rewind() {
        self::$index = 0;
    }

    public function valid() {
        $siteIDs = array_keys($this->data);
        return isset($siteIDs[self::$index]);
    }

}