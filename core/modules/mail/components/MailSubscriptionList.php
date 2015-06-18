<?php

/**
 * Содержит класс MailSubscriptionList
 *
 * @package energine
 * @author andy.karpov
 * @copyright Energine 2015
 */
namespace Energine\mail\components;

use Energine\share\components\DBDataSet;
use Energine\share\gears\Field;
use Energine\share\gears\FieldDescription;
use Energine\share\gears\Data;
use Energine\share\gears\DataDescription;
use Energine\share\gears\QAL;
use Energine\share\gears\SystemException;
use Energine\share\gears\JSONCustomBuilder;

/**
 * Список подписок на рассылки
 *
 * @package energine
 * @author andy.karpov
 */
class MailSubscriptionList extends DBDataSet {

    public function __construct($name, $module, array $params = NULL) {
        parent::__construct($name, $module, $params);
        $this->setTableName('mail_subscriptions');
        $this->setFilter(['subscription_is_active' => '1', 'subscription_is_hidden' => '0']);
        $this->setParam('active', true);
    }

    protected function prepare() {

        parent::prepare();

        // data description для информера
        if (in_array($this->getState(), ['main'])) {

            $data = $this->getData();
            $dd = $this->getDataDescription();

            if ($data) {

                $fd = new FieldDescription('is_subscribed');
                $fd->setType(FieldDescription::FIELD_TYPE_BOOL);
                $dd->addFieldDescription($fd);

                $f = new Field('is_subscribed');
                $data->addField($f);

                $f_subscription_id = $data->getFieldByName('subscription_id');

                $subscribed = $this->dbh->getColumn(
                    'mail_subscriptions2users',
                    'subscription_id',
                    array(
                        'u_id' => $this->document->getUser()->getID()
                    )
                );

                foreach ($f_subscription_id as $i => $row) {
                    $f->setRowData($i, in_array($row, $subscribed) ? '1' : '0');
                }
            }
        }
    }

    protected function toggle() {

        try {
            $sp = $this->getStateParams(true);
            $subscriptionId = $sp['subscriptionID'];
            $res = $this->doToggle($subscriptionId);

            $result = array(
                'result' => true,
                'message' => ($res) ? $this->translate('TXT_SUBSCRIBED') : $this->translate('TXT_UNSUBSCRIBED'),
            );

        } catch (SystemException $e) {

            $result = array(
                'result' => false,
                'errors' => [$e->getMessage()],
                'fields' => $e->getCustomMessage()
            );
        }

        $builder = new JSONCustomBuilder();
        $this->setBuilder($builder);
        $builder->setProperties($result);

    }

    protected function doToggle($subscriptionId) {
        $is_exists = $this->dbh->getScalar(
            'mail_subscriptions2users',
            'subscription_id',
            array(
                'u_id' => $this->document->getUser()->getID(),
                'subscription_id' => $subscriptionId
            )
        );
        if ($is_exists) {
            $this->dbh->modify(
                QAL::DELETE,
                'mail_subscriptions2users',
                NULL,
                array(
                    'u_id' => $this->document->getUser()->getID(),
                    'subscription_id' => $subscriptionId
                )
            );
            return false;
        } else {
            $this->dbh->modify(
                QAL::INSERT,
                'mail_subscriptions2users',
                array(
                    'u_id' => $this->document->getUser()->getID(),
                    'subscription_id' => $subscriptionId
                )
            );
            return true;
        }
    }

}