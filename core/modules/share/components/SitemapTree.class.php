<?php
/**
 * @file
 * SitemapTree
 *
 * It contains the definition to:
 * @code
class SitemapTree;
@endcode
 *
 * @author dr.Pavka
 * @copyright Energine 2006
 *
 * @version 1.0.0
 */


/**
 * Site map.
 *
 * @code
class SitemapTree;
@endcode
 */
class SitemapTree extends DataSet {
    //todo VZ: This can be removed.
    /**
     * @copydoc DataSet::__construct
     */
    public function __construct($name, $module,   array $params = null) {
        parent::__construct($name, $module,  $params);

    }
    /**
     * @copydoc DataSet::loadData
     */
    // Загружает данные о дереве разделов
    protected function loadData() {
        $sitemap = E()->getMap();
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
     * @copydoc DataSet::createBuilder
     */
    protected function createBuilder() {
        $builder  = new TreeBuilder();
        $builder->setTree(E()->getMap()->getTree());
        return $builder;
    }
}