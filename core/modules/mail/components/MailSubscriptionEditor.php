<?php

namespace Energine\mail\components;

use Energine\share\components\Grid,
    Energine\mail\components\MailSubscriptionUserEditor,
    Energine\share\gears\FieldDescription,
    Energine\share\gears\Field;

class MailSubscriptionEditor extends Grid {

    /**
     * @var MailSubscriptionUserEditor $oEditor
     */
    private $oEditor;


    public function __construct($name,  array $params = null) {
        parent::__construct($name, $params);
        $this->setTableName('mail_subscriptions');
        $this->setTitle($this->translate('TXT_MAIL_SUBSCRIPTION_EDITOR'));
    }

    protected function prepare() {

        parent::prepare();

        if (in_array($this->getState(), array('add', 'edit'))) {

            $fd = new FieldDescription('users');
            $fd->setType(FieldDescription::FIELD_TYPE_TAB);
            $fd->setProperty('title', $this->translate('TAB_SUBSCRIBED_USERS'));
            $this->getDataDescription()->addFieldDescription($fd);

            $field = new Field('users');
            $state = $this->getState();
            $tab_url = (($state != 'add') ? $this->getData()->getFieldByName($this->getPK())->getRowData(0) : '') . '/users/';

            $field->setData($tab_url, true);
            $this->getData()->addField($field);
        }
    }

    protected function usersEditor() {
        $sp = $this->getStateParams(true);
        $params = array('config' => 'core/modules/mail/config/MailSubscriptionUserEditor.component.xml');

        if (isset($sp['subscription_id'])) {
            $this->request->shiftPath(2);
            $params['subscriptionID'] = $sp['subscription_id'];

        } else {
            $this->request->shiftPath(1);
        }
        $this->oEditor = $this->document->componentManager->createComponent('oEditor', 'Energine\mail\components\MailSubscriptionUserEditor', $params);
        $this->oEditor->run();
    }

    public function build() {
        if ($this->getState() == 'usersEditor') {
            $result = $this->oEditor->build();
        } else {
            $result = parent::build();
        }

        return $result;
    }

    protected function saveData() {
        $subscriptionID = parent::saveData();
        $this->dbh->modify(
            'UPDATE mail_subscriptions2users
			SET session_id = NULL, subscription_id=%s
			WHERE (subscription_id IS NULL and session_id = %s) or (subscription_id = %1$s)',
            $subscriptionID, session_id()
        );
        return $subscriptionID;
    }
}
