<?php
/**
 * Содержит класс Orderlist
 * @package energine
 * @author andy.karpov
 * @copyright Energine 2015
 */
namespace Energine\shop\components;

use Energine\share\components\DBDataSet;
use Energine\share\gears\ComponentProxyBuilder;
use Energine\share\gears\EmptyBuilder;
use Energine\share\gears\QAL;
use Energine\share\gears\FieldDescription;
use Energine\share\gears\Field;
use Energine\share\gears\DataDescription;
use Energine\share\gears\Data;
use Energine\share\gears\SimpleBuilder;


/**
 * Список заказов
 * @package energine
 * @author andy.karpov
 */
class OrderList extends DBDataSet implements SampleOrderList {

    public function __construct($name, $module, array $params = null) {
        parent::__construct($name, $module, $params);
        $this->setTableName('shop_orders');
        $this->setFilter([
            'site_id' => E()->getSiteManager()->getCurrentSite()->id,
            'u_id'    => $this->document->getUser()->getID()
        ]);
        $this->setOrder(['order_created' => QAL::DESC]);
    }

    protected function defineParams() {
        return array_merge(
            parent::defineParams(),
            [
                'active' => true
            ]
        );
    }

    protected function main() {
        parent::main();
        $this->buildOrderGoods();
    }

    protected function buildOrderGoods() {

        $dd = $this -> getDataDescription();

        $fd = new FieldDescription('order_goods');
        $dd->addFieldDescription($fd);
        $fd->setType(FieldDescription::FIELD_TYPE_CUSTOM);

        $f = new Field('order_goods');
        $this->getData()->addField($f);

        $f_order_id = $this->getData()->getFieldByName('order_id');

        if ($f_order_id) {
            foreach ($f_order_id->getData() as $key => $order_id) {

                $data = $this->dbh->select(
                    'select goods_id, goods_title, goods_price, goods_quantity, goods_amount
                from shop_orders_goods
                where order_id = %s',
                    $order_id
                );

                $builder = new SimpleBuilder();
                $localData = new Data();
                $localData->load($data);

                $dataDescription = new DataDescription();
                $ffd = new FieldDescription('goods_id');
                $dataDescription->addFieldDescription($ffd);

                $ffd = new FieldDescription('goods_code');
                $ffd->setType(FieldDescription::FIELD_TYPE_STRING);
                $dataDescription->addFieldDescription($ffd);

                $ffd = new FieldDescription('goods_title');
                $ffd->setType(FieldDescription::FIELD_TYPE_STRING);
                $dataDescription->addFieldDescription($ffd);

                $ffd = new FieldDescription('goods_price');
                $ffd->setType(FieldDescription::FIELD_TYPE_MONEY);
                $dataDescription->addFieldDescription($ffd);

                $ffd = new FieldDescription('goods_quantity');
                $ffd->setType(FieldDescription::FIELD_TYPE_INT);
                $dataDescription->addFieldDescription($ffd);

                $ffd = new FieldDescription('goods_amount');
                $ffd->setType(FieldDescription::FIELD_TYPE_MONEY);
                $dataDescription->addFieldDescription($ffd);

                $builder->setData($localData);
                $builder->setDataDescription($dataDescription);

                $builder->build();

                $f->setRowData($key, $builder->getResult());
            }
        }
    }

}

interface SampleOrderList {
}

;