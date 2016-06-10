<?php
/**
 * Содержит класс SiteList
 *
 * @package energine
 * @author dr.Pavka
 * @copyright Energine 2015
 */
namespace Energine\shop\components;
/**
 * Список магазинов с ограничениями по правам
 *
 * @package energine
 * @author dr.Pavka
 */
class SiteList extends \Energine\share\components\SiteList {
    protected function defineParams() {
        return array_merge(
            parent::defineParams(),
            [
                'tags' => 'shop'
            ]
        );
    }

    protected function loadData() {
        $result = parent::loadData();
        if ($this->document->getRights() < ACCESS_FULL) {

            $result = array_filter($result, function ($row) {
                return in_array($row['site_id'], $this->document->getUser()->getSites());
            });
        }
        return $result;
    }
}