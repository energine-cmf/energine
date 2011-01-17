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
        E()->getController()->getTransformer()->setFileName('core/modules/seo/transformers/google_sitemap.xslt', true);
        E()->getResponse()->setHeader('Content-Type', 'text/xml; charset=utf-8');
    }

    protected function loadData() {
        foreach (E()->getSiteManager() as $siteID => $site) {
            if ($site->isIndexed) {
                $sitemap = E()->getMap($siteID);
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
        $sm = E()->getSiteManager();
        $defaultSiteID = $sm->getDefaultSite()->id;
        $mainSiteTree = E()->getMap($defaultSiteID)->getTree();
        foreach ($sm as $siteID => $site) {
            if ($siteID != $defaultSiteID && $site->isIndexed) {
                $mainSiteTree->add(E()->getMap($siteID)->getTree()->getRoot());
            }
        }
        $builder->setTree($mainSiteTree);

        return $builder;
    }
}