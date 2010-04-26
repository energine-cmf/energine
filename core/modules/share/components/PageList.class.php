<?php
/**
 * Содержит класс PageList
 *
 * @package energine
 * @subpackage share
 * @author dr.Pavka
 */


/**
 * Класс выводит список подразделов
 *
 * @package energine
 * @subpackage share
 * @author dr.Pavka
 * @final
 */
final class PageList extends DataSet {
	const CURRENT_PAGE = 'current';
	const PARENT_PAGE = 'parent';
	const ALL_PAGES = 'all';
	/**
	 * Идентификатор раздела для которого мы выводим чайлдов
	 *
	 * @access private
	 * @var int
	 */
	private $pid;

	/**
	 * Конструктор класса
	 *
	 * @return void
	 */
	public function __construct($name, $module, Document $document,  array $params = null) {
		parent::__construct($name, $module, $document,  $params);
		$this->setType(self::COMPONENT_TYPE_LIST);
		/*
		 if ($this->getParam('id')) {
		 $this->setParam('active', false);
		 }
		 */
		$this->addTranslation('TXT_HOME');
	}
	/**
	 * Добавлен параметр tags
	 *
	 * @return int
	 * @access protected
	 */

	protected function defineParams() {
		$result = array_merge(parent::defineParams(),
		array(
        'tags' => '',
        'id' => false,
		'site' => SiteManager::getInstance()->getCurrentSite()->id 
		));
		return $result;
	}

	protected function createData(){
		$result = parent::createData();
		if($this->getDataDescription()->getFieldDescriptionByName('AttachedFiles')){
			 
			$field = new Field('AttachedFiles');
			$result->addField($field);

			//Делаем выборку из таблицы дополнительных файлов
			if($result->getFieldByName('Id')){
				$res = $this->dbh->selectRequest('
	                    SELECT smap_id, upl.*
	                    FROM `share_uploads` upl
	                    LEFT JOIN share_sitemap_uploads ssu on ssu.upl_id = upl.upl_id
	                    WHERE smap_id IN ('.implode(',', $result->getFieldByName('Id')->getData()).')
	                ');
				if(is_array($res)){
					//конвертируем результат запроса в удобный формат
					foreach($res as $row){
						$smapID = $row['smap_id'];
						unset($row['smap_id']);
						if(!isset($uplData[$smapID])){
							$uplData[$smapID] = array();
						}
						array_push($uplData[$smapID], $row);
					}
					 
					foreach ($result->getFieldByName('Id') as $index => $smapID) {
						if(!empty($uplData[$smapID])){
							$result->getFieldByName('AttachedFiles')->setRowData($index, $this->buildAttachedFilesField($uplData[$smapID]));
						}
					}
				}
			}
		}

		return $result;
	}

	private function buildAttachedFilesField($attachedFilesData){
		$builder = new SimpleBuilder();

		$data = new Data();
		$data->load($attachedFilesData);

		$dataDescription = new DataDescription();
		$dataDescription->load($this->dbh->getColumnsInfo('share_uploads'));

		$builder->setData($data);
		foreach ($data->getFieldByName('upl_path') as $key => $row) {
			if(file_exists($row)){
				list($width, $height) = @getimagesize($row);
				$data->getFieldByName('upl_path')->setRowProperty($key, 'width', $width);
				$data->getFieldByName('upl_path')->setRowProperty($key, 'height', $height);
			}
		}
		$builder->setDataDescription($dataDescription);
		$builder->build();

		return $builder->getResult();
	}
	/**
	 * Переопределенный метод загрузки данных
	 *
	 * @return mixed
	 * @access protected
	 */

	protected function loadData() {
		$sitemap = Sitemap::getInstance($this->getParam('site'));
			
		//Выводим siblin
		if($this->getParam('id') == self::PARENT_PAGE){
			$data = $sitemap->getChilds(
			$sitemap->getParent(
			$this->document->getID()
			)
			);
		}
		//выводим child текуще
		elseif($this->getParam('id') == self::CURRENT_PAGE){
			$data = $sitemap->getChilds(
			$this->document->getID()
			);
		}
		//выводим все разделы
		elseif($this->getParam('id') == self::ALL_PAGES){
			$data = $sitemap->getInfo();
		}
		//если пустой 
		//выводим главное меню
		elseif (!$this->getParam('id')) {
			$data = $sitemap->getChilds($sitemap->getDefault());
		}
		//выводим child переданной в параметре
		else {
			$data = $sitemap->getChilds((int)$this->getParam('id'));
		}
		
		if (!empty($data)) {
			$hasDescriptionRtf = (bool)$this->getDataDescription()->getFieldDescriptionByName('DescriptionRtf');
            $filteredIDs = TagManager::getInstance()->getFilter($this->getParam('tags'), 'share_sitemap_tags');
            
			reset($data);
			while (list($key, $value) = each($data)) {
                if($this->getParam('tags') && !in_array($key, $filteredIDs)){
                    unset($data[$key]);
                    continue;    
                }    
	            if($key == $sitemap->getDefault()) {
                    unset($data[$key]);
                }
                else {
                    $data[$key]['Id'] = $key;
                    $data[$key]['Segment'] = $value['Segment'];
                    $data[$key]['Name'] = $value['Name'];
                    if($hasDescriptionRtf) $data[$key]['DescriptionRtf'] = $value['DescriptionRtf'];
                }
			    
			}
		}
		return $data;
	}
}
