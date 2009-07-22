<?php
/**
 * Содержит класс RSSSChannel
 *
 * @package energine
 * @subpackage misc
 * @author d.pavka
 * @copyright d.pavka@gmail.com
 */

 /**
  * Генератор RSS feed
  *
  * @package energine
  * @subpackage misc
  * @author d.pavka@gmail.com
  */
 class RSSChannel extends DataSet {
    /**
     * Конструктор класса
     *
     * @param string $name
     * @param string $module
     * @param Document $document
     * @param array $params
     * @access public
     */
    public function __construct($name, $module, Document $document,  array $params = null) {
        parent::__construct($name, $module, $document,  $params);
        DocumentController::getInstance()->getTransformer()->setFileName('rss.xslt');
        Response::getInstance()->setHeader('Content-Type', 'application/rss+xml; charset=UTF-8');
    }

    protected function main(){
        
    }
}