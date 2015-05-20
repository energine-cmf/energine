<?php

namespace Energine\mail\gears;

use Energine\share\gears\DBWorker;
use Energine\mail\gears\MailSourceAbstract;
use Energine\mail\gears\IMailSource;

class MailSourceNews extends MailSourceAbstract
{
    protected $template_name = 'mail_news';
    protected $template_item_name = 'mail_news_item';

    protected function getURLBySmapIDSegment($smap_id, $segment) {
        // todo: строить URL новости без использования ядра ?
        return 'http://todo/' . $smap_id . '/' . $segment . '/';
    }

    public function getItemsSinceDate(\DateTime $date)
    {

        $items = $this->dbh->select(
            'select n.news_id as id, n.smap_id as smap_id, n.news_segment as segment,
                nt.news_title as title, nt.news_announce_rtf as description,
                n.news_date as `date`
            from apps_news n
            left join apps_news_translation nt on n.news_id = nt.news_id and nt.lang_id = %s
            where n.news_is_active = 1 and news_date >= %s
            order by n.news_date desc LIMIT 100',
            $this->lang_id,
            $date->format('Y-m-d H:i:s')
        );

        //$map = E()->getMap();

        array_walk($items, function (&$item) {
            //$item['url'] = $map->getURLByID($item['smap_id']) . $item['segment'] . '/';
            $item['url'] = $this->getURLBySmapIDSegment($item['smap_id'], $item['segment']);
            unset($item['smap_id']);
            unset($item['segment']);
            $item['description'] = strip_tags($item['description']);
            $d = new \DateTime($item['date']);
            $item['date'] = $d->format('d.m.Y'); // todo: брать формат из конфига ?
        });

        return $items;

    }
}
