<?php
/**
 * Содержит класс NavigationMenu
 *
 * @package energine
 * @subpackage aux
 * @author d.pavka
 * @copyright d.pavka@gmail.com
 */

/**
 * Компонент выводящий список дочерних страниц, а также список страниц того же уровня
 *
 * @package energine
 * @subpackage aux
 * @author d.pavka@gmail.com
 * @final
 */
final class NavigationMenu extends SitemapTree {
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
		$params['configFilename'] = sprintf(ComponentConfig::CORE_CONFIG_DIR, $module).get_class($this).'.component.xml';
		parent::__construct($name, $module, $document,  $params);
	}

	protected function createBuilder() {
		$tree = Sitemap::getInstance()->getTree();

		$treeData = array();
		
        //если у нас не раздел 1го уровня
		if($parents = Sitemap::getInstance()->getParents($this->document->getID())){
			
			$ancestorID = key($parents);
			//проходимся по всем прямым предкам
			foreach ($parents as $nodeID => $node){
				//получаем дочерние разделы
				$nodeChilds = $this->dbh->select(
                      'share_sitemap', 
				array('smap_id', 'smap_pid'),
				array('smap_pid' => $nodeID),
				array('smap_order_num' => QAL::ASC)
				);

				if(is_array($nodeChilds)){
					$nodeChilds = array_map(
					create_function(
                            '$node', 
                            'if($node["smap_pid"] == '.$ancestorID.') $node["smap_pid"] = false;                      
                            return $node;'
                            ),
                            $nodeChilds
                            );
				}
				$treeData = array_merge(
				$treeData,
				$nodeChilds
				);

			}
		}
        //ниже 1го уровня получаем дочерние страницы
		if(!empty($parents)){
			$childs = $this->dbh->select('share_sitemap', array('smap_id', 'smap_pid'), array('smap_pid' => $this->document->getID()), array('smap_order_num' => QAL::ASC));
		}
		//если первого уровня - получаем дочерние разделы 
		else{
			$childs = $this->dbh->selectRequest('SELECT smap_id, null as smap_pid FROM share_sitemap WHERE smap_pid = %s ORDER BY smap_order_num', $this->document->getID()); 
		}

		if(is_array($childs))
			$treeData = array_merge(
			$treeData,
			$childs
			);
			
		$tree = TreeConverter::convert($treeData, 'smap_id', 'smap_pid');
			
		$builder  = new TreeBuilder();
		$builder->setTree($tree);

		return $builder;
	}
}