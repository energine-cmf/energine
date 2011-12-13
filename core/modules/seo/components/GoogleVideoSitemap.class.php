<?php
/**
 * Содержит класс GoogleVideoSitemap
 *
 * @package energine
 * @subpackage misc
 * @author Andrii A
 */

/**
 * Компонент для генерации Google Video Sitemap
 * Должен содержатся в пустом лейауте
 * @see http://www.sitemaps.org/protocol.php
 *
 * @package energine
 * @subpackage misc
 * @author d.pavka@gmail.com
 */

class GoogleVideoSitemap extends DataSet
{

    /**
     * Путь к гугл сайтмап, относительно корня сайта
     */
    const SITEMAP_PATH = 'google-sitemap';

    /**
     * Путь к директории, хранящей карты сайта , должна заканчиватся на /
     */
    const SITEMAPS_DIRECTORY_PATH = '';

    /**
     * Максимальное кол-во записей в файле
     */
    const MAX_VIDEOS_IN_FILE = 100;

    /*
     * Экземпляр класса PDO
     */
    private $pdoDB;

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
    }

    /**
     * Генерирует список sitemaps
     *
     * @access protected
     * @return array()
     */

    protected function generateVideoMaps()
    {
        $sitemaps = array();

        $siteinfo = E()->getSiteManager()->getCurrentSite();
        $sitePath = $siteinfo->base;
        $currSegment = $this->request->getPath(1,true);

        array_push($sitemaps,array('path'=>$sitePath.self::SITEMAP_PATH));

        $this->pdoDB->query('SELECT SQL_CALC_FOUND_ROWS videos_id FROM seo_sitemap_videos WHERE site_id = '.$siteinfo->id.' ORDER BY videos_date DESC');
        $rows_info = $this->pdoDB->query('SELECT FOUND_ROWS() as num_rows');
        $rows_info = $rows_info->fetch();

        $totalMaps = ceil($rows_info[0]/self::MAX_VIDEOS_IN_FILE);

        for($i=1;$i<=$totalMaps;$i++)
        {
            array_push($sitemaps,array('path'=>$sitePath.$currSegment.'map/'.$i));
        }

        return $sitemaps;
    }


    /**
     * Генерирует непосредственно video sitemap,
     * содержащий информацию о видео файлах.
     *
     * @access protected
     */
    protected function map()
    {
        $mapNumer = $this->getStateParams();
        $limStart = ((int)$mapNumer[0] - 1)*self::MAX_VIDEOS_IN_FILE;
        $limEnd = self::MAX_VIDEOS_IN_FILE;

        $siteinfo = E()->getSiteManager()->getCurrentSite();

        $videosInfo = $this->pdoDB->query('SELECT * FROM seo_sitemap_videos WHERE site_id = '.$siteinfo->id.' ORDER BY videos_date DESC LIMIT '.$limStart.','.$limEnd);

        ob_start();
        {
            $this->startMapFile();
            while ($videoInfo = $videosInfo->fetch()) {
                $this->addVideoToMap($videoInfo);
            }
            $this->endMapFile();
        }
        ob_end_flush();
        die;
    }

    protected function main()
    {
        E()->getController()->getTransformer()->setFileName('core/modules/seo/transformers/google_video_sitemap.xslt', true);
        $this->prepare();
    }

    protected function loadData()
    {
        return $this->generateVideoMaps();
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
        $builder = new SimpleBuilder();
        return $builder;
    }

    private function startMapFile()
    {
        header("Content-type: text/xml; charset=utf-8");
        $mapHeader = '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" '
                     . 'xmlns:video="http://www.google.com/schemas/sitemap-video/1.1">';
        echo $mapHeader . PHP_EOL;
    }

    private function addVideoToMap($video)
    {
        echo '<url>' . PHP_EOL
            .'<loc>' . $video['videos_loc'] . '</loc>' . PHP_EOL
            ."\t" . '<video:video>' . PHP_EOL
            ."\t\t" . '<video:thumbnail_loc>' . $video['videos_thumb'] . '</video:thumbnail_loc>' . PHP_EOL
            ."\t\t" . '<video:title>' . $video['videos_title'] . '</video:title>' . PHP_EOL
            ."\t\t" . '<video:description><![CDATA[' . $video['videos_desc'] . ']]></video:description>' . PHP_EOL
            ."\t\t" . '<video:content_loc>' . $video['videos_path'] . '</video:content_loc>' . PHP_EOL
            ."\t\t" . '<video:publication_date>' . $video['videos_date'] . '</video:publication_date>' . PHP_EOL
            ."\t" . '</video:video>' . PHP_EOL
            .'</url>' . PHP_EOL;
    }

    private function endMapFile()
    {
        echo '</urlset>' . PHP_EOL;
    }

}