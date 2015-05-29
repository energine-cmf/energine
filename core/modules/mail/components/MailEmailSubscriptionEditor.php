<?php
/**
 * Содержит класс MailEmailSubscriptionEditor
 *
 * @package energine
 * @author dr.Pavka
 * @copyright Energine 2015
 */
namespace Energine\mail\components;

use Energine\share\components\Grid;
use Energine\share\gears\QAL;

/**
 * Users that are subscribed
 *
 * @package energine
 * @author dr.Pavka
 */
class MailEmailSubscriptionEditor extends Grid {
    public function __construct($name, $module, array $params = NULL) {
        parent::__construct($name, $module, $params);
        $this->setTableName('mail_email_subscribers');
        $this->setOrder(['me_date' => QAL::ASC]);
    }

    protected function getFKData($fkTableName, $fkKeyName) {
        $filter = $order = NULL;

        if ($fkKeyName == 'subscription_id') {
            $filter = ['mail_subscriptions.subscription_is_default' => 1];
        }

        if ($this->getState() !== self::DEFAULT_STATE_NAME) {
            $result = $this->dbh->getForeignKeyData($fkTableName, $fkKeyName, $this->document->getLang(), $filter, $order);
        }

        return $result;
    }
}