<?php

namespace Energine\mail\components;

use Energine\share\components\Grid,
    Energine\share\gears\SystemException;

class MailTemplateEditor extends Grid {

    public function __construct($name,  array $params = null) {
        parent::__construct($name, $params);
        $this->setTableName('mail_templates');
        $this->setTitle($this->translate('TXT_MAIL_TEMPLATES_EDITOR'));
    }

    public function deleteData($id) {
        throw new SystemException('ERR_CANT_DELETE_MAIL_TEMPLATE', SystemException::ERR_CRITICAL);
    }
}
