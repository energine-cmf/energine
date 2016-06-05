<?php
/**
 * Created by PhpStorm.
 * User: pavka
 * Date: 6/30/15
 * Time: 2:11 PM
 */

namespace Energine\shop\components;


use Energine\share\components\DataSet;
use Energine\share\gears\ComponentProxyBuilder;
use Energine\share\gears\Document;
use Energine\share\gears\EmptyBuilder;
use Energine\share\gears\QAL;
use Energine\share\gears\Request;
use Energine\shop\gears\SampleGoodsList;


class AltRelatedGoodsList extends DataSet implements SampleGoodsList {

    private $bindComponent;

    /**
     * Конструктор
     *
     * @param string $name
     * @param array $params
     */
    public function __construct($name, array $params = NULL) {
        parent::__construct($name, $params);
        $this->setParam('recordsPerPage', false);
    }

    protected function defineParams() {
        return array_merge(
            parent::defineParams(),
            [
                'active' => true,
                'state' => 'init',
                'bind' => false,
                'bind_state' => 'view',
                'relation_type' => 'similar',
                'className' =>'\\Energine\\shop\\components\\GoodsList'
            ]
        );
    }

    protected function initState() {
        $this->bindComponent =
            $this->document->componentManager->getBlockByName($this->getParam('bind'));
        if ($this->bindComponent && $this->bindComponent->getState() == $this->getParam('bind_state')) {
            $params = $this->bindComponent->getStateParams(true);
            $path = substr(str_replace('['.key($params).']', current($params), (string)$this->bindComponent->config->getStateConfig('view')->uri_patterns->pattern), 1);
            $this->setBuilder(new EmptyBuilder());
            $this->setProperty('single_template', str_replace(Document::SINGLE_SEGMENT, $path.Document::SINGLE_SEGMENT, $this->getProperty('single_template')) . 'show/');
            $this->js = $this->buildJS();
        } else {
            $this->disable();
        }

    }

    protected function mainState() {
        $curr = E()['Energine\\shop\\gears\\Currency'];
                $this->setProperty('currency', $curr->getInfo()['currency_shortname']);
                $this->setProperty('currency-order', $curr->getInfo()['currency_shortname_order']);
        $this->setType(self::COMPONENT_TYPE_LIST);
        $segments = array_reverse($this->request->getPath());
        list($segment) = array_slice($segments, array_search(Document::SINGLE_SEGMENT, $segments) + 1, 1);

        if (
        $productID = $this->dbh->getScalar('shop_goods', 'goods_id', ['goods_id' => $segment, 'goods_is_active' => true])
        ) {
            // получаем список goods_id связи
            $goods_ids = $this->dbh->getColumn(
                'shop_goods_relations',
                'goods_to_id',
                [
                    'relation_type' => $relation_type = $this->getParam('relation_type'),
                    'goods_from_id' => $productID
                ],
                [
                    'relation_order_num' => QAL::ASC
                ]
            );

            if (empty($goods_ids)) {
                $goods_ids = ['-1'];
            }
            $this->setBuilder($b = new ComponentProxyBuilder());
            $params = [
                'active' => false,
                'state' => 'main',
                'id' => $goods_ids,
                'list_features' => 'any' // вывод всех фич товаров в списке
            ];
            $b->setComponent('products', $this->getParam('className'), $params);

        } else {
            $this->disable();
        }


    }
}