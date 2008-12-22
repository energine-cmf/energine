<?php

/**
 * Содержит класс SitemapTree
 *
 * @package energine
 * @subpackage share
 * @author dr.Pavka
 * @copyright ColoCall 2006
 * @version $Id$
 */

//require_once('core/modules/share/components/DataSet.class.php');
//require_once('core/framework/TreeBuilder.class.php');

/**
 * Карта сайта
 *
 * @package energine
 * @subpackage share
 * @author dr.Pavka
 */
class SitemapTree extends DataSet {

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

    }
    /**
     * Загружает данные о дереве разделов
     *
     * @return array
     * @access protected
     */

    protected function loadData() {
        $sitemap = Sitemap::getInstance();
        $res = $sitemap->getInfo();
        foreach ($res as $id => $info) {
        	$result [] = array(
        	   'Id' => $id,
        	   'Pid' =>$info['Pid'],
        	   'Name' => $info['Name'],
        	   'Segment' => $sitemap->getURLByID($id)
        	);
        }
        return $result;
    }

    /**
     * Переопределяет посторитель
     *
     * @return void
     * @access protected
     */

    protected function createBuilder() {
        $builder  = new TreeBuilder();
        $builder->setTree(Sitemap::getInstance()->getTree());
        return $builder;
    }
}