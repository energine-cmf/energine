<?php

namespace Energine\mail\components;

use Energine\share\components\Grid;
use Energine\share\gears\FieldDescription;
use Energine\share\gears\QAL;
use Energine\mail\components\UsersLookup;


class MailSubscriptionUserEditor extends Grid
{

    public function __construct($name,  array $params = null)
    {
        parent::__construct($name, $params);
        $this->setTableName('mail_subscriptions2users');

        if (is_numeric($this->getParam('subscriptionID'))) {
            $filter = sprintf(' (subscription_id = %s) ', $this->getParam('subscriptionID'));
        } else {
            $filter = sprintf(' (subscription_id IS NULL and session_id="%s") ', session_id());
        }

        $this->setFilter($filter);

    }

    protected function defineParams()
    {
        return array_merge(
            parent::defineParams(),
            array(
                'subscriptionID' => false,
            )
        );
    }

    public function add()
    {
        parent::add();
        $data = $this->getData();
        if ($subscription_id = $this->getParam('subscriptionID')) {
            $f = $data->getFieldByName('subscription_id');
            $f->setRowData(0, $subscription_id);
        }
        $f = $data->getFieldByName('session_id');
        $f->setRowData(0, session_id());
    }

    public function edit()
    {
        parent::edit();
        $data = $this->getData();
        if ($promotion_id = $this->getParam('subscriptionID')) {
            $f = $data->getFieldByName('subscription_id');
            $f->setRowData(0, $promotion_id);
        }
    }

    protected function createDataDescription()
    {
        $result = parent::createDataDescription();

        if (in_array($this->getState(), array('add', 'edit'))) {

            $fd = $result->getFieldDescriptionByName('subscription_id');
            $fd->setType(FieldDescription::FIELD_TYPE_HIDDEN);

            $fd = $result->getFieldDescriptionByName('session_id');
            $fd->setType(FieldDescription::FIELD_TYPE_HIDDEN);

        }

        return $result;
    }

}