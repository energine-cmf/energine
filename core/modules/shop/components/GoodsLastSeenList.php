<?php

namespace Energine\shop\components;

use Energine\share\components\DataSet;
use Energine\share\gears\ComponentProxyBuilder;
use Energine\share\gears\EmptyBuilder;

class GoodsLastSeenList extends DataSet implements SampleGoodsLastSeenList {
    protected function defineParams() {
        return array_merge(
            parent::defineParams(),
            [
                'active' => true
            ]
        );
    }

    protected function createBuilder() {
        return new SimpleBuilder();
    }

    private function getCount() {
        return (isset($_SESSION['last_seen_goods']) && is_array($_SESSION['last_seen_goods'])) ? sizeof($_SESSION['last_seen_goods']) : 0;
    }

    protected function mainState() {
        E()->UserSession->start();
        if (!empty($_SESSION['last_seen_goods'])) {
            $this->setBuilder($b = new ComponentProxyBuilder());
            $params = [
                'active'        => false,
                'state'         => 'main',
                'id'            => $_SESSION['last_seen_goods'],
                'list_features' => 'any' // вывод всех фич товаров в списке
            ];
            $b->setComponent('products',
                '\\Energine\\shop\\components\\GoodsList',
                $params);
            $this->addToolbar($this->loadToolbar());
            $this->js = $this->buildJS();
        } else {
            $this->setBuilder(new EmptyBuilder());
        }
    }

    protected function initState() {
        E()->UserSession->start();

        $this->setBuilder(new EmptyBuilder());
        $this->setProperty('count', $this->getCount());
        $this->js = $this->buildJS();
        $this->setProperty('load', (string)$this->config->getStateConfig('main')->uri_patterns->pattern);
    }

    public function build() {
        if ($this->document->getProperty('single')) {
            E()->getController()->getTransformer()->setFileName('../../../../core/modules/shop/transformers/single_wishlist.xslt');
        }
        $result = parent::build();

        return $result;
    }

}

interface SampleGoodsLastSeenList {

}