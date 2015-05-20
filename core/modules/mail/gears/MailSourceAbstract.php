<?php

namespace Energine\mail\gears;

use Energine\share\gears\DBWorker;
use Energine\mail\gears\IMailSource;
use Energine\mail\gears\MailTemplate;

class MailSourceAbstract implements IMailSource
{
    use DBWorker;

    protected $template_name;
    protected $template_item_name;
    protected $lang_id;

    public function setLang($lang_id) {
        $this->lang_id = $lang_id;
    }

    public function getItemsSinceDate(\DateTime $date)
    {
        return array();
    }

    public function getItemsBody($items)
    {
        $result = '';
        foreach ($items as $item) {
            $template = new MailTemplate($this->template_item_name, $item);
            $result .= $template->getBody();
        }
        return $result;
    }

    public function getItemsHTMLBody($items)
    {
        $result = '';
        foreach ($items as $item) {
            $template = new MailTemplate($this->template_item_name, $item);
            $result .= $template->getHTMLBody();
        }
        return $result;
    }

    public function getSubject($subscriber)
    {
        $template = new MailTemplate($this->template_name, $subscriber);
        return $template->getSubject();
    }

    public function getBody($subscriber, $items)
    {
        $items_body = $this->getItemsBody($items);
        $data = array_merge($subscriber, array('items' => $items_body));
        $template = new MailTemplate($this->template_name, $data);
        return $template->getBody();
    }

    public function getHTMLBody($subscriber, $items)
    {
        $items_body = $this->getItemsHTMLBody($items);
        $data = array_merge($subscriber, array('items' => $items_body));
        $template = new MailTemplate($this->template_name, $data);
        return $template->getHTMLBody();
    }
}
