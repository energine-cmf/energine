<?php

namespace Energine\mail\gears;

use Energine\share\gears\DBWorker;
use Energine\share\gears\SystemException;

class MailTemplate {

    use DBWorker;

    protected $name;
    protected $lang_id;
    protected $data = [];
    protected $template = [];

    public function __construct($name, $data = [], $lang_id = null) {
        $this->name = $name;
        $this->lang_id = ($lang_id) ? $lang_id : E()->getLanguage()->getCurrent();
        $this->loadTemplate();
        $this->data = $data;
    }

    protected function loadTemplate() {
        $res = $this->dbh->select(
            'select tt.template_subject, tt.template_body, tt.template_body_rtf
            from mail_templates t
            left join mail_templates_translation tt on t.template_id = tt.template_id and tt.lang_id = %s
            where t.template_sysname = %s and t.template_is_active = 1',
            $this->lang_id,
            $this->name
        );

        if (empty($res)) {
            throw new SystemException('ERR_NO_MAIL_TEMPLATE', SystemException::ERR_CRITICAL, $this->name);
        }

        $this->template = ($res) ? ($res[0]) : array();
    }

    protected function getKeys() {
        return array_map(
            function($item){
                return '[' . $item . ']';
            },
            array_keys($this->data)
        );
    }

    protected function parse($string) {
        return str_replace($this->getKeys(), array_values($this->data), $string);
    }

    public function getSubject() {
        return (!empty($this->template['template_subject'])) ? $this->parse($this->template['template_subject']) : '';
    }

    public function getBody() {
        return (!empty($this->template['template_body'])) ? $this->parse($this->template['template_body']) : '';
    }

    public function getHTMLBody() {
        return (!empty($this->template['template_body_rtf'])) ? $this->parse($this->template['template_body_rtf']) : '';
    }
}
