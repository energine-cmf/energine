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
 * Компонент для генерации Google Sitemap,
 * Google Sitemap Index и Google Video Sitemap.
 * Должен содержаться в пустом лейауте.
 * @see http://www.sitemaps.org/protocol.php
 * @see http://www.google.com/support/webmasters/bin/answer.py?answer=80472
 *
 * @package energine
 * @subpackage misc
 * @author d.pavka@gmail.com
 */
class GoogleSitemap extends SitemapTree
{

    /**
     * Максимальное кол-во записей с информацией
     * о видео в файле video sitemap.
     */
    private $maxVideos;

    /**
     * Экземпляр класса PDO
     */
    private $pdoDB;

    /**
     * Максимальное кол - во видео в файле сайт мап
     * по умолчанию.
     */
    const DEFAULT_MAX_VIDEOS = 40000;

    /**
     * Конструктор класса
     *
     * @param string $name
     * @param string $module

     * @param array $params
     * @access public
     */
    public function __construct($name, $module, array $params = null)
    {
        parent::__construct($name, $module, $params);
        E()->getResponse()->setHeader('Content-Type', 'text/xml; charset=utf-8');
        $this->pdoDB = $this->dbh->getPDO();
        $this->maxVideos = ((int)$this->getConfigValue('seo.maxVideosInMap'))? (int)$this->getConfigValue('seo.maxVideosInMap'): self::DEFAULT_MAX_VIDEOS;
    }


    /**
     * Генерирует google sitemap index
     *
     * @access protected
     */
    protected function main(){
        E()->getController()->getTransformer()->setFileName('core/modules/seo/transformers/google_sitemap_index.xslt', true);
        parent::main();
        $this->setBuilder(new SimpleBuilder());
    }

    /**
     * Генерирует google sitemap
     *
     * @access protected
     */
    protected function map(){
        $this->prepare();
        E()->getController()->getTransformer()->setFileName('core/modules/seo/transformers/google_sitemap.xslt', true);
        $dd = new DataDescription();
        foreach (array('Id' => FieldDescription::FIELD_TYPE_INT,
                     'Pid' => FieldDescription::FIELD_TYPE_INT,
                     'Segment' => FieldDescription::FIELD_TYPE_STRING,
                     'Site' => FieldDescription::FIELD_TYPE_STRING) as $fieldName => $fieldType) {

            $fd = new FieldDescription($fieldName);

            if($fieldName == 'Id')
                $fd->setType($fieldType)->setProperty('key', 1);
            else
                $fd->setType($fieldType);

            $dd->addFieldDescription($fd);

        }
        $this->setDataDescription($dd);
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
        $this->getData()->load($result);
    }

    /**
     * Генерирует непосредственно video sitemap,
     * содержащий информацию о видео файлах.
     *
     * @access protected
     */
    protected function videomap() {
        $respone = E()->getResponse();

        $params = $this->getStateParams();
        $mapNumber = ((int)$params[0])?(int)$params[0]:1;
        $limStart = ($mapNumber - 1) * $this->maxVideos;
        $limEnd = $this->maxVideos;

        $siteinfo = E()->getSiteManager()->getCurrentSite();

        $videosInfo = $this->pdoDB->query('SELECT * FROM seo_sitemap_videos WHERE site_id = ' . $siteinfo->id . ' ORDER BY videos_date DESC LIMIT ' . $limStart . ',' . $limEnd);

        {
            $respone->write('<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" '
                    . 'xmlns:video="http://www.google.com/schemas/sitemap-video/1.1">' . PHP_EOL);

            while ($videoInfo = $videosInfo->fetch()) {
                E()->getResponse()->write('<url>' . PHP_EOL
                        . '<loc>' . $videoInfo['videos_loc'] . '</loc>' . PHP_EOL
                        . "\t" . '<video:video>' . PHP_EOL
                        . "\t\t" . '<video:thumbnail_loc>' . $videoInfo['videos_thumb'] . '</video:thumbnail_loc>' . PHP_EOL
                        . "\t\t" . '<video:title>' . $videoInfo['videos_title'] . '</video:title>' . PHP_EOL
                        . "\t\t" . '<video:description><![CDATA[' . $videoInfo['videos_desc'] . ']]></video:description>' . PHP_EOL
                        . "\t\t" . '<video:content_loc>' . $videoInfo['videos_path'] . '</video:content_loc>' . PHP_EOL
                        . "\t\t" . '<video:publication_date>' . $videoInfo['videos_date'] . '</video:publication_date>' . PHP_EOL
                        . "\t" . '</video:video>' . PHP_EOL
                        . '</url>' . PHP_EOL);
            }

            $respone->write('</urlset>' . PHP_EOL);
        }

        E()->getResponse()->commit();
    }

    protected function loadData()
    {
        $sitemaps = array();

        $siteinfo = E()->getSiteManager()->getCurrentSite();
        $sitePath = $siteinfo->base;
        $fullPath = $this->request->getPath(1, true);

        $this->pdoDB->query('SELECT SQL_CALC_FOUND_ROWS videos_id FROM seo_sitemap_videos WHERE site_id = ' . $siteinfo->id . ' ORDER BY videos_date DESC');
        $rows_info = $this->pdoDB->query('SELECT FOUND_ROWS() as num_rows');
        $rows_info = $rows_info->fetch();

        $totalMaps = ceil($rows_info[0] / $this->maxVideos);

        array_push($sitemaps, array('path' => $sitePath . $fullPath.'map'));
        for ($i = 1; $i <= $totalMaps; $i++)
        {
            array_push($sitemaps, array('path' => $sitePath . $fullPath . 'videomap/' . $i));
        }
        return $sitemaps;
    }

    protected function createDataDescription()
    {
        $dd = new DataDescription();
        $fd = new FieldDescription('path');
        $fd->setType(FieldDescription::FIELD_TYPE_STRING);
        $dd->addFieldDescription($fd);
        return $dd;
    }

    protected function createBuilder()
    {
        $builder = new TreeBuilder();
        $sm = E()->getSiteManager();
        $defaultSiteID = $sm->getDefaultSite()->id;
        $mainSiteTree = E()->getMap($defaultSiteID)->getTree();
        foreach ($sm as $siteID => $site) {
            if ($siteID != $defaultSiteID && $site->isIndexed) {
                $tree = E()->getMap($siteID)->getTree();
                if ($tree) {
                    $mainSiteTree->add($tree->getRoot());
                }
            }
        }
        $builder->setTree($mainSiteTree);

        return $builder;
    }

}