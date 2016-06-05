<?php

namespace Energine\shop\components;

use Energine\share\components\DataSet;
use Energine\share\gears\Data;
use Energine\share\gears\FieldDescription;
use Energine\share\gears\QAL;
use Energine\share\gears\SimpleBuilder;

class GoodsSort extends DataSet {

    protected $sort_data = [];

    public function __construct($name, array $params = NULL) {
        $params['active'] = false;
        parent::__construct($name, $params);
        $this->setTitle($this->translate('TXT_SORT'));
        $this->setProperty('get', http_build_query($_GET));
    }


    protected function defineParams() {
        return array_merge(
            parent::defineParams(),
            [
                'bind' => false,
            ]
        );
    }

    protected function createDataDescription() {
        $result = parent::createDataDescription();
        if (!($fd = $result->getFieldDescriptionByName('dir'))) {
            $fd = new FieldDescription('dir');
            $fd->setType(FieldDescription::FIELD_TYPE_SELECT);
            $result->addFieldDescription($fd);
        }
        $fd->loadAvailableValues([['id' => $v = strtolower(QAL::ASC), 'value' => $v], ['id' => $v = strtolower(QAL::DESC), 'value' => $v]], 'id', 'value');
        if (!($fd = $result->getFieldDescriptionByName('field'))) {
            $fd = new FieldDescription('field');
            $fd->setType(FieldDescription::FIELD_TYPE_SELECT);
            $result->addFieldDescription($fd);
        }
        $r = [];
        foreach(['price', 'name'] as $f){
            array_push($r, ['id' => $f, 'value' => $this->translate('FIELD_GOODS_'.$f)]);
        }

        $fd->loadAvailableValues($r, 'id', 'value');
        return $result;
    }

    public function main() {
        $goodsList = E()->getDocument()->componentManager->getBlockByName($this->getParam('bind'));
        if ($goodsList->getState() == 'view') $this->disable();
        else {

            $this->setBuilder(new SimpleBuilder());
            $this->setDataDescription($this->createDataDescription());
            $d = new Data();
            $d->load([$goodsList->getSortData()]);
            $this->setData($d);
        }
    }

}