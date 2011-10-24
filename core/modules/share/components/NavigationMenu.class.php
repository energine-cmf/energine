<?php
/**
 * Содержит класс NavigationMenu
 *
 * @package energine
 * @subpackage misc
 * @author d.pavka
 * @copyright d.pavka@gmail.com
 */

/**
 * Компонент выводящий список дочерних страниц, а также список страниц того же уровня
 *
 * @package energine
 * @subpackage misc
 * @author d.pavka@gmail.com
 * @final
 */
final class NavigationMenu extends DataSet {
	/**
	 * Отфильтрованные идентифкаторы
	 * 
	 * @access private
	 * @var array | boolean 
	 */
	 private $filteredIDs;
	/**
	 * Конструктор класса
	 *
	 * @param string $name
	 * @param string $module
	 * @param Document $document
	 * @param array $params
	 * @access public
	 */
	public function __construct($name, $module,   array $params = null) {
		$params['config'] = sprintf(CORE_DIR.ComponentConfig::CORE_CONFIG_DIR, $module).get_class($this).'.component.xml';
		parent::__construct($name, $module,  $params);
	}
    /**
     * Добавлен параметр tags позволяющий ограничивать выборку определенными тегами
     *
     * @return array
     */
    protected function defineParams() {
        $result = array_merge(parent::defineParams(),
        array(
        'tags' => '',
        ));
        return $result;
    }
    /**
      * Накладываем ограничения по тегам
      * 
      * @return array | false
      * @access protected
      */
    protected function loadData(){
    	$sitemap = E()->getMap();
    	
    	$data = $sitemap->getInfo(); 
    	$this->filteredIDs = true;
        if (!empty($data)) {
            if($this->getParam('tags'))
                $this->filteredIDs = TagManager::getFilter($this->getParam('tags'), 'share_sitemap_tags');
                
            if(!empty($this->filteredIDs)) {
	            reset($data);
	            while (list($key, $value) = each($data)) {
	                if(($this->filteredIDs !== true) && !in_array($key, $this->filteredIDs)){
	                    unset($data[$key]);
	                    continue;    
	                }    
	                if($key == $sitemap->getDefault()) {
	                    unset($data[$key]);
	                }
	                else {
	                    $data[$key]['Id'] = $key;
	                    $data[$key]['Segment'] = $sitemap->getURLByID($key);
	                    $data[$key]['Name'] = $value['Name'];
                        $data[$key]['Redirect'] = Response::prepareRedirectURL($value['RedirectUrl']);
	                }
	            }
            }
            else {
            	$data = array();
            }
        }
            
        return $data;    
    }
	protected function createBuilder() {
		
		$tree = E()->getMap()->getTree();

		$treeData = array();
		
        //если у нас не раздел 1го уровня
		if($parents = E()->getMap()->getParents($this->document->getID())){
			$ancestorID = key($parents);
			//проходимся по всем прямым предкам
			foreach ($parents as $nodeID => $node){
				//получаем дочерние разделы
				$nodeChilds = $this->dbh->selectRequest('
				    SELECT s.smap_id, s.smap_pid
				    FROM share_sitemap s
				    LEFT JOIN share_sitemap_translation st ON s.smap_id=st.smap_id
				    WHERE smap_pid  = '.$nodeID.' AND smap_is_disabled = 0 AND lang_id = '.E()->getLanguage()->getCurrent().'
				    ORDER BY smap_order_num ASC
				');
				
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