<?php
/**
 * Created by PhpStorm.
 * User: pavka
 * Date: 09.04.15
 * Time: 18:17
 */

namespace Energine\shop\components;


class RoleEditor extends \Energine\user\components\RoleEditor {
    protected function getFKData($table, $key) {
        $filter = $order = [];
        if ($table == 'share_sites' && $key == 'site_id') {
            foreach (E()->getSiteManager()->getSitesByTag('shop') as $site) {
                $filter['share_sites.site_id'][] = (string)$site;
            }
        }

        // Для main убираем список значений в селекте, ни к чему он там
        $result = [];
        if ($this->getState() !== self::DEFAULT_STATE_NAME) {
            $result =
                $this->dbh->getForeignKeyData($table, $key, $this->document->getLang(), $filter, $order);
        }

        return $result;
    }

}