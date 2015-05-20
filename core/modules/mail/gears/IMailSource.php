<?php

namespace Energine\mail\gears;

interface IMailSource
{
    public function setLang($lang_id);

    public function getItemsSinceDate(\DateTime $date);
    public function getItemsBody($items);
    public function getItemsHTMLBody($items);

    public function getSubject($subscriber);
    public function getBody($subscriber, $items);
    public function getHTMLBody($subscriber, $items);

}