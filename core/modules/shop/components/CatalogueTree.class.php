<?php
/**
 * Содержит класс CatalogueTree
 *
 * @package energine
 * @subpackage shop
 * @author dr.Pavka
 * @copyright ColoCall 2007
 * @version $Id$
 */

//require_once('core/modules/share/components/SitemapTree.class.php');

/**
 * Выводит дерево товарных разделов
 *
 * @package energine
 * @subpackage shop
 * @author dr.Pavka
 */
class CatalogueTree extends SitemapTree {
    /**
     * Конструктор класса
     *
     * @param string $name
     * @param string $module
     * @param Document $document
     * @param array $params
     * @access public
     */
	public function __construct($name, $module, Document $document, array $params = null) {
        parent::__construct($name, $module, $document, $params);
	}
}