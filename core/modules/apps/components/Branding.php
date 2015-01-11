<?php
/**
 * @file
 * Branding
 *
 * It contains the definition to:
 * @code
class Branding;
@endcode
 *
 * @author dr.Pavka
 * @copyright Energine 2012
 *
 * @version 1.0.0
 */
namespace Energine\apps\components;
use Energine\share\components\DBDataSet, Energine\share\gears\SimpleBuilder, Energine\share\gears\FieldDescription;
/**
 * Show branding.
 *
 * @code
class Branding;
@endcode
 */
class Branding extends DBDataSet {
    /**
     * @copydoc DBDataSet::__construct
     */
    public function __construct($name, $module, array $params = null) {
        $params['active'] = false;

        parent::__construct($name, $module, $params);
        $this->setTableName('apps_branding');
        $this->setFilter(array('brand_id' => $this->findNearestParentBrandID()));
        $this->setParam('recordsPerPage', false);
    }

    /**
     * Find nearest parent with branding.
     *
     * @return int
     */
    private function findNearestParentBrandID() {
        //список родителей
        $parents = array_keys(E()->getMap()->getParents($this->document->getID()));
        //добавили текущий раздел
        array_push($parents, $this->document->getID());

        //делаем выборку брендингов
        $d = convertDBResult($this->dbh->select('share_sitemap', array('smap_id', 'brand_id'), array('smap_id' => $parents)),
            'smap_id', true);
        //Результирующий массив
        $res = array();
        //проходимся по списку родителей формируя отсортированный список "идент раздела"=>"идент бренда"
        foreach ($parents as $smapID) {
            if (!is_null($d[$smapID]['brand_id']))
                $res[$smapID] = $d[$smapID]['brand_id'];
        }
        //возвращаем последний идент бренда
        return array_pop($res);
    }

    /**
     * @copydoc DBDataSet::createBuilder
     */
    protected function createBuilder() {
        return new SimpleBuilder();
    }

    /**
     * @copydoc DBDataSet::createDataDescription
     */
    protected function createDataDescription() {
        $result = parent::createDataDescription();
        $result->getFieldDescriptionByName('brand_main_img')->setType(FieldDescription::FIELD_TYPE_STRING);
        foreach($result as $fd){
            $fd->setMode(FieldDescription::FIELD_MODE_READ);
        }
        return $result;
    }
}