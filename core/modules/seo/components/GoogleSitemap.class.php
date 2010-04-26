<?php
/**
 * Содержит класс GoogleSitemap
 *
 * @package energine
 * @subpackage misc
 * @author d.pavka
 * @copyright d.pavka@gmail.com
 */

 /**
  * Компонент для генерации Google Sitemap
  * Должен содержаться в пустом лейауте
  * @see http://www.sitemaps.org/protocol.php 
  *
  * @package energine
  * @subpackage misc
  * @author d.pavka@gmail.com
  */
 class GoogleSitemap extends SitemapTree{
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
        DocumentController::getInstance()->getTransformer()->setFileName('core/modules/seo/transformers/google_sitemap.xslt', true);
        Response::getInstance()->setHeader('Content-Type', 'text/xml; charset=utf-8');
    }
}