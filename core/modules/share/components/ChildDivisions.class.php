<?php
/**
 * Содержит класс ChildDivisions
 *
 * @package energine
 * @subpackage share
 * @author dr.Pavka
 * @copyright Energine 2006
 * @version $Id$
 */



/**
 * Класс передназначен для вівода дочерних разделов текущего раздела
 *
 * @package energine
 * @subpackage share
 * @author dr.Pavka
 * @todo класс необходимо переписать поскольку используется разный принцип получения данных для страниц и разделов
 *
 */
class ChildDivisions extends DataSet  {
	/**
	 * Переменная содержащая идентификатор раздела для которого нужно выводить потомков
	 *
	 * @var int
	 * @access private
	 */
	private $id;

	/**
	 * Идентификатор указывающий на то что нужно исепользовать в качестве идентфикатора id родительской страниц
	 *
	 */
	const PARENT_ID = 'parent';

	/**
	 * Конструктор класса
	 *
	 * @return void
	 */
	public function __construct($name, $module, Document $document,  array $params = null) {
		parent::__construct($name, $module, $document,  $params);
		$this->setType(self::COMPONENT_TYPE_LIST);
		if ($this->getParam('id')) {
			$this->setParam('active', false);
			//$this->setParam('recordsPerPage', false);
		}

		if($this->getParam('id') == self::PARENT_ID){
			$this->id = Sitemap::getInstance()->getParent($this->document->getID());
		}
		elseif (!$this->getParam('id')) {
			$this->id = $this->document->getID();
		}
		else {
			$this->id = $this->getParam('id');
		}
	}

	/**
	 * Возвращает значение id
	 *
	 * @return int
	 * @access protected
	 * @final
	 */
	final protected function getID() {
		return $this->id;
	}

	/**
	 * Устанавливает id
	 *
	 * @return void
	 * @access protected
	 * @final
	 */
	final protected function setID($id) {
		$this->id = $id;
	}

	/**
	 * Добавлен параметр id - идентификатор страницы
	 *
	 * @return int
	 * @access protected
	 */

	protected function defineParams() {
		$result = array_merge(parent::defineParams(),
		array(
        'id'=>false,
        'showFinal' => false,
        'active' => true
		));
		return $result;
	}

	/**
	 * Устанавливаем перечень полей
	 *
	 * @return DataDescription
	 * @access protected
	 */

	protected function createDataDescription() {
		$result = new DataDescription();

		$field = new FieldDescription('Id');
		$field->setType(FieldDescription::FIELD_TYPE_INT);
		$field->addProperty('key', true);
		$result->addFieldDescription($field);

		$field = new FieldDescription('Name');
		$field->setType(FieldDescription::FIELD_TYPE_STRING);
		$result->addFieldDescription($field);

		$field = new FieldDescription('Segment');
		$field->setType(FieldDescription::FIELD_TYPE_STRING);
		$result->addFieldDescription($field);

		$field = new FieldDescription('DescriptionRtf');
		$field->setType(FieldDescription::FIELD_TYPE_TEXT);
		$result->addFieldDescription($field);

		$field = new FieldDescription('AttachedFiles');
		$field->setType(FieldDescription::FIELD_TYPE_CUSTOM);
		$result->addFieldDescription($field);

		return $result;
	}

	protected function createData(){
		$result = parent::createData();
		if($result){
			$field = new Field('AttachedFiles');
			$result->addField($field);
			//Делаем выборку из таблицы дополнительных файлов
			foreach ($result->getFieldByName('Id') as $index => $smapID) {
				$data = $this->dbh->selectRequest('
					SELECT upl.*
					FROM `share_uploads` upl
					LEFT JOIN share_sitemap_uploads ssu on ssu.upl_id = upl.upl_id
					WHERE smap_id = %s
				', $smapID);

				if(is_array($data) && !empty($data)){
					$result->getFieldByName('AttachedFiles')->setRowData($index, $this->buildAttachedFilesField($data));
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
				list($width, $height) = @getimagesize($row);
			if(file_exists($row)){
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
		$data = Sitemap::getInstance()->getChilds($this->getID());
		if(!$this->getParam('showFinal')){
			$data = array_filter(
			     $data, create_function('$element', 'return !$element["isFinal"];')
			);
		}
		else{
            $data = array_filter(
                 $data, create_function('$element', 'return $element["isFinal"];')
            );
		}
		$data = (empty($data))?false:$data;
		if(is_array($data)) {
			if ($this->getParam('recordsPerPage')) {
				if ($this->pager->getCurrentPage()>1) {
					$this->document->componentManager->getComponentByName('breadCrumbs')->addCrumb();
				}
				$this->pager->setRecordsCount(sizeof($data));
				$limit = $this->pager->getLimit();
				$data = array_slice($data, $limit[0], $limit[1], true);
			}
			foreach ($data as $id => $current) {
				$data[$id] = array(
				'Id' => (isset($current['Id']))?$current['Id']:$id,
                'Segment' => $current['Segment'],
                'Name' => $current['Name'],
                'DescriptionRtf' => $current['DescriptionRtf']
				);
			}

		}

		return $data;
	}

	/**
	 * Callback функция для фильтрации массива данных о дочерних страницах(не разделах) по правам
	 *
	 * @param $row
	 * @return bool
	 */
	private function filterDataByRights($row){
		return (Sitemap::getInstance()->getDocumentRights($row['Id']) != ACCESS_NONE);
	}

	/**
	 * Callback функция для генерации полного URL дл страниц
	 *
	 * @param $row
	 * @return array
	 */
	private function prepareSegment($row){
		$row['Segment'] = Sitemap::getInstance()->getURLByID($row['Pid']).$row['Segment'].'/';

		return $row;
	}
}
