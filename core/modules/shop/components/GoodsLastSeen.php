<?php

namespace Energine\shop\components;

use Energine\share\components\DataSet;
use Energine\share\gears\UserSession;

class GoodsLastSeen extends DataSet {

    /**
     * Bounded component.
     * @var DBDataSet|boolean $bindComponent
     */
    protected $bindComponent;

    /**
     * Конструктор
     *
     * @param string $name
     * @param array $params
     */
    public function __construct($name, array $params = null) {
        parent::__construct($name, $params);
        $this->bindComponent =
            $this->document->componentManager->getBlockByName($this->getParam('bind'));
        $this->setParam('recordsPerPage', false);
        $this->setParam('active', false);
    }

    protected function defineParams() {
        return array_merge(
            parent::defineParams(),
            [
                'bind' => false,
                'bind_state' => 'view',
            ]
        );
    }

    protected function prepare() {

        if ($this->bindComponent and
            $this->bindComponent->getState() == $this->getParam('bind_state') and
            $this->getState() == 'main'
        ) {

            // ID родительской записи
            $priFieldName = $this->bindComponent->getPK();
            $targetIds = $this->bindComponent->getData()->getFieldByName($priFieldName)->getData();
            $goods_id = $targetIds[0];

            // добавляем в список
            E()->UserSession->start();
            $_SESSION['last_seen_goods'][] = $goods_id;
            $_SESSION['last_seen_goods'] = array_unique($_SESSION['last_seen_goods']);
            $_SESSION['last_seen_goods'] = array_slice($_SESSION['last_seen_goods'], 0, 50);

            parent::prepare();

        } else {
            $this->disable();
        }
    }

}