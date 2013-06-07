<?php

/**
 * Содержит класс Robots
 *
 * @package energine
 * @subpackage seo
 * @author andy.karpov
 * @copyright andy.karpov@gmail.com
 */

/**
 * Компонент для генерации robots.txt
 *
 * @package energine
 * @subpackage seo
 * @author andy.karpov@gmail.com
 */
class Robots extends DataSet
{
    /**
     * Конструктор класса
     *
     * @param string $name
     * @param string $module
     * @param array $params
     */
    public function __construct($name, $module, array $params = null)
    {
        parent::__construct($name, $module, $params);
        E()->getResponse()->setHeader('Content-Type', 'text/plain; charset=utf-8');
    }

    /**
     * Основной стейт генерации robots.txt
     *
     */
    protected function main(){
        E()->getController()->getTransformer()->setFileName('core/modules/seo/transformers/robots_txt.xslt', true);
        parent::main();
        $this->setBuilder(new SimpleBuilder());
    }

    /**
     * Описание DataDescription
     *
     * @return DataDescription
     */
    protected function createDataDescription()
    {
        $dd = new DataDescription();
        $fd = new FieldDescription('entry');
        $fd->setType(FieldDescription::FIELD_TYPE_STRING);
        $dd->addFieldDescription($fd);
        return $dd;
    }

    /**
     * Метод проверки наличия конфигурации модуля seo в system.config.php
     *
     * @return bool
     */
    protected function isSeoConfigured() {
        $cfg = E()->getConfigArray();
        if (!array_key_exists('seo', $cfg)) {
            return false;
        }
        foreach (array('sitemapSegment', 'sitemapTemplate', 'maxVideosInMap') as $seoParam) {
            if (!array_key_exists($seoParam, $cfg['seo'])) {
                return false;
            }
        }
        return true;
    }

    /**
     * Метод получения массива существующих smap_segment для google sitemap
     *
     * @return array|bool
     */
    protected function getSitemapSegmentIds() {

        $res = $this->dbh->select(
            'share_sitemap', 'smap_id', array(
                'smap_segment' => E()->getConfigValue('seo.sitemapSegment')
            )
        );

        if (!is_array($res)) return false;

        $result = array();
        foreach ($res as $row) {
            $result[] = $row['smap_id'];
        }

        return $result;
    }

    /**
     * Создаем сегмент(ы) google sitemap в share_sitemap
     * и даем на него права на просмотр для не авторизированных
     * пользователей. Имя сегмента следует указать в конфиге.
     */
    protected function createSitemapSegment() {

        $smap_ids = $this->getSitemapSegmentIds();
        if ($smap_ids) return;

        // вставка нового сегмента в sitemap на основании конфига
        $this->dbh->selectRequest(
            'INSERT IGNORE INTO share_sitemap
            (site_id,smap_layout,smap_content,smap_segment,smap_pid) '
            . 'SELECT sso.site_id, %s, %s, %s, '
            . '(SELECT smap_id FROM share_sitemap ss2 WHERE ss2.site_id = sso.site_id AND smap_pid IS NULL LIMIT 0,1) '
            . 'FROM share_sites sso '
            . 'WHERE site_is_indexed AND site_is_active '
            . 'AND (SELECT COUNT(ssi.site_id) FROM share_sites ssi '
            . 'INNER JOIN share_sitemap ssm ON ssi.site_id = ssm.site_id '
            . 'WHERE ssm.smap_segment = %s AND ssi.site_id = sso.site_id) = 0',
            E()->getConfigValue('seo.sitemapTemplate') . '.layout.xml',
            E()->getConfigValue('seo.sitemapTemplate') . '.content.xml',
            E()->getConfigValue('seo.sitemapSegment'),
            E()->getConfigValue('seo.sitemapSegment')
        );

        $smap_ids = $this->getSitemapSegmentIds();

        if ($smap_ids) {
            foreach($smap_ids as $smap_id) {

                // права доступа
                $this->dbh->selectRequest(
                    'INSERT IGNORE INTO share_access_level (smap_id, group_id, right_id) ' .
                    ' SELECT %s as smap_id, group_id, (SELECT right_id FROM `user_group_rights` WHERE right_const = "ACCESS_READ") ' .
                    ' FROM `user_groups` ',
                    $smap_id
                );

                // переводы
                $this->dbh->selectRequest(
                    'INSERT IGNORE INTO share_sitemap_translation (smap_id, lang_id, smap_name, smap_is_disabled) ' .
                    ' VALUES (%s, (SELECT lang_id FROM `share_languages` WHERE lang_default), "Google sitemap", 0)',
                    $smap_id
                );
            }
        }
    }

    /**
     * Метод формирования данных для DataSet'а
     *
     * @return array|mixed
     */
    protected function loadData()
    {
        $entries = array();

        if (!$this->isSeoConfigured()) {

            array_push($entries, array('entry' => 'User-agent: *' . PHP_EOL . 'Disallow: /'));

        } else {

            array_push($entries, array('entry' => 'User-agent: *' . PHP_EOL . 'Allow: /'));

            $this->createSitemapSegment();

            $domainsInfo = $this->dbh->selectRequest(
               'SELECT ss.site_id, sd.domain_protocol, sd.domain_host, sd.domain_root ' .
               'FROM share_sites ss ' .
               'INNER JOIN share_domain2site d2s ON ss.site_id = d2s.site_id ' .
               'INNER JOIN share_domains sd ON  sd.domain_id = d2s.domain_id ' .
               'WHERE ss.site_is_indexed'
            );

            if (is_array($domainsInfo)) {
                foreach($domainsInfo as $row) {
                    array_push(
                        $entries,
                        array('entry' =>
                            'Sitemap: ' . $row['domain_protocol'] . '://' . $row['domain_host'] .
                            $row['domain_root'] . E()->getConfigValue('seo.sitemapSegment')
                        )
                    );
                }
            }
        }

        return $entries;
    }


}
