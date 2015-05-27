<?php
/**
 * Содержит класс MailEmailSubscription
 *
 * @package energine
 * @author dr.Pavka
 * @copyright Energine 2015
 */
namespace Energine\mail\components;

use Energine\share\components\DataSet;
use Energine\share\gears\Builder;
use Energine\share\gears\Data;
use Energine\share\gears\DataDescription;
use Energine\share\gears\FieldDescription;
use Energine\share\gears\FormBuilder;
use Energine\share\gears\JSONCustomBuilder;
use Energine\share\gears\QAL;

/**
 * Email subscription form
 *
 * @package energine
 * @author dr.Pavka
 */
class MailEmailSubscription extends DataSet {
    public function __construct($name, $module, array $params = NULL) {
        parent::__construct($name, $module, $params);
        $this->setType(self::COMPONENT_TYPE_FORM_ADD);
        /*@todo create smth like StateConfig - descendant of SimpleXML*/
        $this->setAction((string)$this->config->getStateConfig('subscribe')->uri_patterns->pattern);

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
        $this->setBuilder(new FormBuilder());
        $dd = new DataDescription();
        $fd = new FieldDescription('email');
        $fd->setType(FieldDescription::FIELD_TYPE_EMAIL);
        $dd->addFieldDescription($fd);
        $this->setDataDescription($dd);
        $this->setData(new Data());

        $this->js = $this->buildJS();
        $toolbars = $this->createToolbar();
        if (!empty($toolbars)) {
            $this->addToolbar($toolbars);
        }
    }

    protected function subscribe() {
        $this->setBuilder($b = new JSONCustomBuilder());
        try {
            if (!isset($_POST['email'])) {
                throw new \InvalidArgumentException('ERR_NO_EMAIL');
            }
            $email = $_POST['email'];
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new \InvalidArgumentException('ERR_BAD_EMAIL');
            }
            //firstly searching for user with this email in users list
            if ($uid = $this->dbh->getScalar('user_users', 'u_id', ['u_name' => $email])) {
                $this->dbh->modify('INSERT INTO mail_subscriptions2users (u_id, subscription_id) SELECT %s as me_id, subscription_id FROM mail_subscriptions WHERE subscription_is_active and subscription_is_default', $uid);
            } else {
                if ($this->dbh->getScalar('mail_email_subscribers', 'COUNT(*)', ['me_email' => $email])) {
                    throw new \RuntimeException('ERR_MAIL_EXISTS');
                }
                $meID = $this->dbh->modify(QAL::INSERT, 'mail_email_subscribers', ['me_email' => $email]);
                $this->dbh->modify('INSERT INTO mail_email2subscriptions (me_id, subscription_id) SELECT %s as me_id, subscription_id FROM mail_subscriptions WHERE subscription_is_active and subscription_is_default', $meID);
            }

            $b->setProperties([
                'result' => true,
                'message' => $this->translate('MSG_SUBSCRIBED')
            ]);
        } catch (\Exception $e) {
            $b->setProperties([
                'result' => false,
                'message' => $this->translate($e->getMessage())
            ]);
        }

    }
}