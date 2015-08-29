<?php

namespace Energine\ads\components;

use Energine\share\components\Grid,
    Energine\share\gears\FieldDescription,
    Energine\share\gears\Field;

class AdsItemEditor extends Grid {

    public function __construct($name,  array $params = null) {
        parent::__construct($name, $params);
        $this->setTableName('ads_items');
        $this->setTitle($this->translate('TXT_ADS_ITEMS_EDITOR'));
    }

    protected function defineParams() {
        return array_merge(
            parent::defineParams(),
            [
                'rootTag' => 'ads'
            ]
        );
    }

    protected function createDataDescription() {
        $r = parent::createDataDescription();
        if (in_array($this->getState(), ['add', 'edit'])) {
            $r->getFieldDescriptionByName('ads_item_smap_multi')->setProperty('tabName', E()->Utils->translate('TXT_CATEGORIES'));
            if (($this->document->getRights() < ACCESS_FULL) && ($fd = $r->getFieldDescriptionByName('ads_item_site_multi'))) {
                $fd->setType(FieldDescription::FIELD_TYPE_HIDDEN);
            }
        }
        return $r;
    }

    /**
     * Отбираем только те сайты которые являются магазинами
     *
     * @param string $fkTableName
     * @param string $fkKeyName
     * @return array
     */
    protected function getFKData($fkTableName, $fkKeyName) {
        $filter = $result = [];
        if ($fkKeyName == 'site_id') {
            //оставляем только те сайты где есть магазины
            if ($sites = E()->getSiteManager()->getSitesByTag('shop')) {
                $filter['share_sites.site_id'] = array_map(function ($site) {
                    return (string)$site;
                }, $sites);
            }
        }
        if ($fkKeyName == 'smap_id') {
            //оставляем только те сайты где есть магазины
            if ($sites = E()->getSiteManager()->getSitesByTag('shop')) {
                $filter['share_sitemap.site_id'] = array_map(function ($site) {
                    return (string)$site;
                }, $sites);
            }
        }

        if ($this->getState() !== self::DEFAULT_STATE_NAME) {
            $result = $this->dbh->getForeignKeyData($fkTableName, $fkKeyName, $this->document->getLang(), $filter);
        }

        if(isset($result[0]) && ($fkKeyName == 'smap_id')) {
            $pages = $rootPages = [];
            foreach($filter['share_sitemap.site_id'] as $siteID){
                $map = E()->getMap($siteID);
                foreach($map->getPagesByTag($this->getParam('rootTag')) as $pageID){
                    $pages[] = $pageID;
                    $pages = array_merge($pages, array_keys($map->getTree()->getNodeById($pageID)->asList()));
                    $rootPages[] = $pageID;
                }
            }

            $result[0] = array_filter($result[0], function($row) use($pages){
                return in_array($row['smap_id'], $pages);
            });
            $result[0] = array_map(function($row) use ($rootPages){
                if(in_array($row['smap_id'], $rootPages)) $row['root'] = E()->getSiteManager()->getSiteByID($row['site_id'])->name;
                return $row;
            }, $result[0]);
        }
        return $result;
    }

    protected function saveData() {
        //Для всех с не админскими правами принудительно выставляем в те сайты на которые у юзера есть права
        if (($this->document->getRights() < ACCESS_FULL)) {
            $_POST[$this->getTableName()]['ads_item_site_multi'] = $this->document->getUser()->getSites();
        }
        return parent::saveData();
    }

    protected function getRawData() {
        if ($this->document->getRights() < ACCESS_FULL) {
            //отбираем те баннеры, права на которые есть у текущего пользователя
            $this->addFilterCondition([
                $this->getTableName() . '.ads_item_id' =>
                    $this->dbh->getColumn('ads_items2sites', 'ads_item_id', ['site_id' => $this->document->getUser()->getSites()])]);
        }
        parent::getRawData();
    }


}
