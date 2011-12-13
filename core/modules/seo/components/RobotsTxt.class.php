<?php
/**
 * Содержит класс GoogleVideoSitemap
 *
 * @package energine
 * @subpackage misc
 * @author Andrii A
 */

/**
 * Компонент для генерации файла
 * robots.txt
 *
 * @package energine
 * @subpackage misc
 * @author d.pavka@gmail.com
 */

class RobotsTxt extends DataSet
{

    /**
     * Путь к индексу гугл сайтмап, относительно корня сайта
     */
    const SITEMAP_INDEX_PATH = 'google-video-sitemap';

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
        E()->getController()->getTransformer()->setFileName('core/modules/seo/transformers/robotsTxt.xslt', true);
    }

    protected function loadData()
    {
        return $this->generateLinks();
    }

    private function generateLinks()
    {
        $siteinfo = E()->getSiteManager()->getCurrentSite();
        $sitePath = $siteinfo->base;
        return array(array('path'=>$sitePath.self::SITEMAP_INDEX_PATH));
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

}