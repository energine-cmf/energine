<?php
/**
 * Содержит класс SiteEditor
 *
 * @package energine
 * @subpackage share
 * @author d.pavka
 * @copyright d.pavka@gmail.com
 */

/**
 * Редактор сайтов
 *
 * @package energine
 * @subpackage share
 * @author d.pavka@gmail.com
 */
class SiteEditor extends Grid {
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
		$this->setTableName('share_sites');
	}

	/**
	 * Изменяем типы филдов
	 *
	 * @return DataDescription
	 * @access protected
	 */
	protected function prepare(){
		parent::prepare();
		if(in_array($this->getAction(), array('add', 'edit'))){
			$fd = $this->getDataDescription()->getFieldDescriptionByName('site_protocol');
			$fd->setType(FieldDescription::FIELD_TYPE_SELECT);
			$fd->loadAvailableValues(array(array('key' => 'http', 'value' => 'http://'), array('key' => 'https', 'value' => 'https://')), 'key', 'value');
				
			$fd = $this->getDataDescription()->getFieldDescriptionByName('site_folder');
			$fd->setType(FieldDescription::FIELD_TYPE_SELECT);
			$fd->loadAvailableValues($this->loadFoldersData(), 'key', 'value');

			
			if($this->getData()->getFieldByName('site_is_default')->getRowData(0) == 1){
			 	$this->getDataDescription()->getFieldDescriptionByName('site_is_default')->setMode(FieldDescription::FIELD_MODE_READ);
			}
			if($this->getAction() == 'add'){
				$this->getData()->getFieldByName('site_port')->setData(80, true);
			}
		}
	}

	/**
	 * Загружаем данные о папках в поле folder
	 *
	 * @return array
	 * @access private
	 */
	private function loadFoldersData(){
		$result = array();
		foreach(glob('site/*', GLOB_ONLYDIR) as $folder){
			$folder = str_replace('site/', '', $folder);
			$result[] = array('key' => $folder, 'value' => $folder);
		}
		return $result;
	}
	
	/**
	  * При сохранении данных обрабатываем дефолтный сайт
	  * 
	  * @return mixed
	  * @access protected
	  */
	protected function saveData(){
	    $this->setSaver(new SiteSaver());
	    return parent::saveData(); 
	}

}