<?php

namespace Energine\shop\components;


use Energine\share\gears\FieldDescription;
use Energine\share\gears\SystemException;

class CategoryEditor extends DivisionEditor {

    public function __construct($name, array $params = NULL) {
        parent::__construct($name, $params);
        $sp = $this->getStateParams(true);
        if (!isset($sp['site_id'])) {
            $shopIDs = E()->getSiteManager()->getSitesByTag('shop', true);
            if (empty($shopIDs)) {
                throw new SystemException('ERR_NO_SHOP', SystemException::ERR_CRITICAL);
            }
            list($shopID) = $shopIDs;
            $this->setStateParam('site_id', $shopID);
        }
    }


    protected function isSmapIsCategory($smap_id) {
        $catalog_site_id = $this->getStateParams();
        list($catalog_site_id) = $catalog_site_id;

        $catalog_category_id = E()->getMap($catalog_site_id)->getPagesByTag('catalogue');
        if (empty($catalog_category_id)) {
            throw new SystemException('ERR_NO_CATALOGUE', SystemException::ERR_CRITICAL);
        }


        if (in_array($smap_id, $catalog_category_id)) return $smap_id;
        $map = E()->getMap($catalog_site_id);
        $parents = $map->getParents($smap_id);

        if (!empty($parents)) {
            foreach (array_keys($parents) as $pid) {
                if (in_array($pid, $catalog_category_id)) return true;
            }
        }

        return false;
    }

    protected function loadData() {
        $result = parent::loadData();
        if ($result && $this->getState() == 'getRawData') {
            foreach ($result as $key => $row) {
                if (!($smap = $this->isSmapIsCategory($row['smap_id']))) {
                    unset($result[$key]);
                } elseif ($smap !== true) {
                    $result[$key]['smap_pid'] = NULL;
                }
            }
        }

        return $result;
    }

    protected function edit() {
        parent::edit();
        $map = E()->getMap($this->getData()->getFieldByName('site_id')->getRowData(0));

        if (in_array($this->getData()->getFieldByName('smap_id')->getRowData(0), $map->getPagesByTag('catalogue'))) {
            $this->getDataDescription()->getFieldDescriptionByName('smap_pid')
                ->setMode(FieldDescription::FIELD_MODE_READ);
        }
    }
}
