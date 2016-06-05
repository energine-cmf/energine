<?php

namespace Energine\shop\components;


use Energine\share\components\Grid;
use Energine\share\gears\FieldDescription;
use Energine\share\gears\QAL;
use Energine\share\gears\SiteManager;
use Energine\share\gears\Translit;

class ProducerEditor extends Grid {
    public function __construct($name, array $params = NULL) {
        parent::__construct($name, $params);
        $this->setTableName('shop_producers');
    }

    protected function defineParams() {
        return array_merge(
            parent::defineParams(),
            [
                'site' => false
            ]
        );
    }

    private function getSites() {
        $result = [];
        if ($siteID = $this->getParam('site')) {
            $result = [$siteID];
        } elseif ($this->document->getRights() < ACCESS_FULL) {
            $result = $this->document->getUser()->getSites();
            if (empty($result)) {
                $result = [0];
            }
        } else {
            foreach (E()->getSiteManager() as $site) {
                $result[] = $site->id;
            }
        }

        return $result;
    }

    protected function createDataDescription() {
        $r = parent::createDataDescription();
        if ((($this->getParam('site')) || ($this->document->getRights() < ACCESS_FULL)) && ($fd = $r->getFieldDescriptionByName('producer_site_multi')) ) {
            $fd->setType(FieldDescription::FIELD_TYPE_HIDDEN);
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
        $filter = $order = NULL;
        if ($fkKeyName == 'site_id') {
            //оставляем только те сайты где есть магазины
            if ($sites = E()->getSiteManager()->getSitesByTag('shop')) {
                $filter = array_map(function ($site) {
                    return (string)$site;
                }, $sites);
                $filter['share_sites.site_id'] = $filter;
                //$order['share_sites_translation.site_name'] = QAL::ASC;
            }
        }

        if ($this->getState() !== self::DEFAULT_STATE_NAME) {
            $result = $this->dbh->getForeignKeyData($fkTableName, $fkKeyName, $this->document->getLang(), $filter, $order);
        }

        return $result;
    }

    protected function getRawData() {

        //отбираем тех производителей права на которые есть у текущего пользователя
        //то есть те, у которых есть в перечен привязанных сайтов
        $this->addFilterCondition([$this->getTableName() . '.producer_id' => $this->dbh->getColumn('shop_producers2sites', 'producer_id', ['site_id' => $this->getSites()])]);

        parent::getRawData();
    }

    protected function saveData() {
        if (empty($_POST[$this->getTableName()]['producer_segment'])) {
            $_POST[$this->getTableName()]['producer_segment'] = Translit::asURLSegment($_POST[$this->getTranslationTableName()][E()->getLanguage()->getDefault()]['producer_name']);
        }

        if(!isset($_POST[$this->getTableName()]['producer_site_multi']) || !$_POST[$this->getTableName()]['producer_site_multi']){
            $_POST[$this->getTableName()]['producer_site_multi'] = $this->getSites();
        }

        $r = parent::saveData();

        return $r;
    }
}