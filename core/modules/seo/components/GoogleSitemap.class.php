<?php
/**
 * Содержит класс GoogleSitemap
 *
 * @package energine
 * @subpackage misc
 * @author d.pavka
 * @copyright d.pavka@gmail.com
 */

/**
 * Компонент для генерации Google Sitemap
 * Должен содержаться в пустом лейауте
 * @see http://www.sitemaps.org/protocol.php
 *
 * @package energine
 * @subpackage misc
 * @author d.pavka@gmail.com
 */
class GoogleSitemap extends SitemapTree {
    /**
     * Конструктор класса
     *
     * @param string $name
     * @param string $module

     * @param array $params
     * @access public
     */
    public function __construct($name, $module, array $params = null) {
        parent::__construct($name, $module, $params);
        DocumentController::getInstance()->getTransformer()->setFileName('core/modules/seo/transformers/google_sitemap.xslt', true);
        Response::getInstance()->setHeader('Content-Type', 'text/xml; charset=utf-8');
    }

    protected function loadData() {
        foreach (SiteManager::getInstance() as $siteID => $site) {
            if ($site->isIndexed) {
                $sitemap = Sitemap::getInstance($siteID);
                $res = $sitemap->getInfo();
                foreach ($res as $id => $info) {
                    $result [] = array(
                        'Id' => $id,
                        'Pid' => $info['Pid'],
                        'Name' => $info['Name'],
                        'Segment' => $sitemap->getURLByID($id),
                        'Site' => $site->base
                    );
                }
            }
        }
        return $result;
    }

    protected function createBuilder() {
        $builder = new TreeBuilder();
        $sm = SiteManager::getInstance();
        $defaultSiteID = $sm->getDefaultSite()->id;
        $mainSiteTree = Sitemap::getInstance($defaultSiteID)->getTree();
        foreach ($sm as $siteID => $site) {
            if ($siteID != $defaultSiteID && $site->isIndexed) {
                $mainSiteTree->add(Sitemap::getInstance($siteID)->getTree()->getRoot());
            }
        }
        $builder->setTree($mainSiteTree);

        return $builder;
    }
}