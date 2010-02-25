<?php
/**
 * Содержит класс ProductList
 *
 * @package energine
 * @subpackage shop
 * @author dr.Pavka
 * @copyright ColoCall 2006
 * @version $Id$
 */

/**
 * Выводит список продуктов для заданного раздела
 *
 * @package energine
 * @subpackage shop
 * @author dr.Pavka
 */
class ProductList extends DBDataSet {
	/**
	 * Имя поля поиска
	 *
	 */
	const SEARCH_FIELD_NAME = 'product';

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
		$this->setTableName('shop_products');
		$id = $this->getParam('id');

		$descendants = array_keys(Sitemap::getInstance()->getTree()->getNodeById($id)->getDescendants()->asList(false));
		$id = array_merge(array($id), $descendants);
		$this->setFilter(array('smap_id' => $id, 'ps_id'=>ProductStatusEditor::getVisibleStatuses($this->document->getRights())));
		$this->setParam('onlyCurrentLang', true);
	}

	/**
	 * Добавлен параметр smapID
	 *
	 * @return array
	 * @access protected
	 */
	protected function defineParams() {
		return array_merge(parent::defineParams(),
		array(
            'id' => $this->document->getID(),
            'active' => true
		));
	}
	/**
	 * Поле producer_id в списке должно выводиться как текст
	 *
	 * @return DATADescription
	 * @access protected
	 */

	protected function createDataDescription() {
		$result = parent::createDataDescription();
		if($producerIDFD = $result->getFieldDescriptionByName('producer_id')) {
			$producerIDFD->setType(FieldDescription::FIELD_TYPE_TEXT);
			$producerIDFD->setProperty('title', $this->translate('TXT_ANOTHER_PRODUCTS'));
		}
		$fd = new FieldDescription('product_images');
		$fd->setType(FieldDescription::FIELD_TYPE_CUSTOM);
		$result->addFieldDescription($fd);
		return $result;
	}

	/**
	 * Для поля producer_id вместо ключа выводится значение а ключ пишется в аттрибут
	 *
	 * @return Data
	 * @access protected
	 */

	protected function createData() {
		$result = parent::createData();
		if ($result) {
			$sitemap = Sitemap::getInstance();
			$this->buildProductImagesField($result);
			
			if ($producerIDField = $result->getFieldByName('producer_id')) {
				$producerData = 
					convertDBResult(
					    $this->dbh->select(
					       'shop_producers', 
					       array('producer_id','producer_name', 'producer_segment'), 
					       array('producer_id'=>$producerIDField->getData())
					    ),
					    'producer_id',
					    true
					);
			}
			for ($i = 0; $i < $result->getRowCount(); $i++) {
				if($producerIDField && ($producerID = $producerIDField->getRowData($i))){
					$producerIDField->setRowProperty($i, 'producerID', $producerID);
					$producerIDField->setRowProperty($i, 'producer_segment', $producerData[$producerID]['producer_segment']);
					$producerIDField->setRowData($i, $producerData[$producerID]['producer_name']);
				}
				if ($smapID = $result->getFieldByName('smap_id')) {
					$smapInfo = $sitemap->getDocumentInfo($smapID->getRowData($i));
					$smapID->setRowProperty($i, 'smap_segment', $sitemap->getURLByID($smapID->getRowData($i)));
					$smapID->setRowData($i, $smapInfo['Name']);
				}
			}
		}

		return $result;
	}

	/**
	 * Добавляет значение product_price(если есть в перечне полей
	 *
	 * @return array
	 * @access protected
	 */

	protected function loadData() {
		if ($this->getAction()=='search' && isset($_GET[self::SEARCH_FIELD_NAME])) {
			$searchData = explode(' ', $_GET[self::SEARCH_FIELD_NAME]);
			$searchData = array_map(create_function('$str', 'return trim($str);'), $searchData);
			$searchString = '';
			reset($searchData);
			while ($str = current($searchData)) {
				$searchString .= ' (product_name like "%'.$str.'%" OR product_description_rtf LIKE "%'.$str.'%") ';
				if (next($searchData)) {
					$searchString .= 'OR';
				}
			}
			$searchString = '('.$searchString.') AND ps_id IN ('.implode(',', ProductStatusEditor::getVisibleStatuses($this->document->getRights())).') ';

			$this->setFilter($searchString);
		}

		$result = parent::loadData();
		if (is_array($result) && $this->getDataDescription()->getFieldDescriptionByName('product_price')) {
			$converter = CurrencyConverter::getInstance();
			$currentCurrency = $converter->getCurrent();

			$res = $this->dbh->selectRequest(
			 'SELECT product_id, product_price, curr_id '.
			 'FROM shop_product_external_properties e '.
			 'LEFT JOIN shop_products p ON p.product_code = e.product_code '.
			 'WHERE product_id IN('.
			implode(',',
			array_map(
			create_function(
			             '$row', 'return $row["product_id"];'
			             ),
			             $result
			             )
			             ).')');
			             	
			             if(is_array($res)){
			             	$res = convertDBResult($res, 'product_id', true);
			             	foreach ($result as $key => $row) {
			             		$result[$key]['product_price'] = $converter->format(
			             		$converter->convert(
			             		$res[$row['product_id']]['product_price'],
			             		$currentCurrency,
			             		$res[$row['product_id']]['curr_id']),
			             		$currentCurrency
			             		);
			             	}
			             }
		}
		return $result;
	}

	/**
	 * Выводит продук
	 *
	 * @return void
	 * @access protected
	 */

	protected function view() {
		$this->setType(self::COMPONENT_TYPE_FORM);
		list($segment) = $this->getActionParams();
		$id = simplifyDBResult($this->dbh->select($this->getTableName(), $this->getPK(), array('product_segment' => $segment)), $this->getPK(), true);
		if (!$this->recordExists($id)) {
			throw new SystemException('ERR_404', SystemException::ERR_404);
		}
		$this->setFilter($id);

		$this->prepare();
		$this->document->componentManager->getComponentByName('breadCrumbs')->addCrumb('',$this->getData()->getFieldByName('product_name')->getRowData(0));
		//Получаем параметры и их значения

		//Сначала  - набор параметров
		$res = $this->dbh->selectRequest(
			'SELECT pp.pp_id, pp_name, pp_type, pp_value FROM `shop_product_params` pp '. 
            'LEFT JOIN shop_product_params_translation ppt ON ppt.pp_id = pp.pp_id '. 
            'LEFT JOIN  shop_products p On p.pt_id = pp.pt_id '.
            'LEFT JOIN  shop_product_param_values pv On pv.pp_id = pp.pp_id AND pv.product_id = p.product_id '.
            'WHERE p.product_id = %s  AND lang_id = %s',
			$id,
			$this->document->getLang()
		);

		if(is_array($res)){
			foreach ($res as $row) {
				$paramName = 'product_param_'.$row['pp_id'];
				//Для каждого параметра создаем FieldDescription
				$paramFD = new FieldDescription($paramName);
				$paramFD->setType($row['pp_type']);
				$paramFD->setProperty('title', $row['pp_name']);
				$paramFD->setProperty('param', $this->translate('TXT_PRODUCT_PARAMS'));
				$this->getDataDescription()->addFieldDescription($paramFD);
				$paramF = new Field('product_param_'.$row['pp_id']);
				$paramF->setData($row['pp_value']);
				$this->getData()->addField($paramF);
		}
		}
		foreach ($this->getDataDescription() as $fieldDescription) {
			$fieldDescription->setMode(FieldDescription::FIELD_MODE_READ);
		}

		if ($component = $this->document->componentManager->getComponentByName('productDivisions')) {
			$component->disable();
		}

		if ($component = $this->document->componentManager->getComponentByName('manufacturers')) {
			$component->disable();
		}
		$this->document->setProperty('title', $this->getData()->getFieldByName('product_name')->getRowData(0));
	}

	private function buildProductImagesField(Data $data){
		$f = new Field('product_images');
		$data->addField($f);

		$images = $this->dbh->selectRequest(
               'SELECT spu.product_id, upl_path, upl_name FROM share_uploads su '.
               'LEFT JOIN `shop_product_uploads` spu ON spu.upl_id = su.upl_id '.
               'WHERE product_id IN ('.implode(',', $data->getFieldByName('product_id')->getData()).')'
               );

               if(is_array($images)){
                foreach($images as $row){
                	$productID = $row['product_id'];
                	if(!isset($imageData[$productID]))
                	$imageData[$productID] = array();
                	 
                	array_push($imageData[$productID], $row);
                }

                for ($i = 0; $i < $data->getRowCount(); $i++) {
                	if(isset($imageData[$data->getFieldByName('product_id')->getRowData($i)])){
                		$builder = new SimpleBuilder();
                		$localData = new Data();
                		$localData->load($imageData[$data->getFieldByName('product_id')->getRowData($i)]);

                		$dataDescription = new DataDescription();
                		$fd = new FieldDescription('product_id');
                		$dataDescription->addFieldDescription($fd);
                		$fd = new FieldDescription('upl_path');
                		$fd->setType(FieldDescription::FIELD_TYPE_IMAGE);
                		$dataDescription->addFieldDescription($fd);
                		$fd = new FieldDescription('upl_name');
                		$dataDescription->addFieldDescription($fd);
                		$builder->setData($localData);
                		$builder->setDataDescription($dataDescription);

                		$builder->build();

                		$f->setRowData($i, $builder->getResult());
                	}
                }

               }
	}

	/**
	 * Выводит список продуктов определенного производителя в данной группе
	 *
	 * @return void
	 * @access protected
	 */

	protected function showManufacturerProducts() {
		list($producerSegment) = $this->getActionParams();
		list($producerInfo) = $this->dbh->select('shop_producers', array('producer_id', 'producer_name'), array('producer_segment' => $producerSegment));
		$this->addFilterCondition(array('producer_id' => $producerInfo['producer_id']));
		$this->document->componentManager->getComponentByName('breadCrumbs')->addCrumb('0', $producerInfo['producer_name'], $producerSegment);
		$this->prepare();
		$this->document->setProperty('title', ($title = $this->document->getProperty('title'))?$title.' / '.$producerInfo['producer_name']:$producerInfo['producer_name']);
		if ($component = $this->document->componentManager->getComponentByName('productDivisions')) {
			$component->disable();
		}
		if ($component = $this->document->componentManager->getComponentByName('manufacturers')) {
			$component->disable();
		}
	}

	/**
	 * Переписан родительский метод
	 * Для метода showManufacturer изменяет принцип получения actionParams
	 *
	 * @return void
	 * @access protected
	 */

	protected function createPager() {
		$recordsPerPage = intval($this->getParam('recordsPerPage'));
		if ($recordsPerPage > 0) {
			$this->pager = new Pager($recordsPerPage);
			if ($this->isActive() && $this->getType() == self::COMPONENT_TYPE_LIST) {
				$actionParams = $this->getActionParams();
				if ($this->getAction() == 'showManufacturerProducts') {
					$paramNum = 1;
				}
				else {
					$paramNum = 0;
				}

				if (isset($actionParams[$paramNum])) {
					$page = intval($actionParams[$paramNum]);
				}
				else {
					$page = 1;
				}

				$this->pager->setCurrentPage($page);
			}

			$this->pager->setProperty('title', $this->translate('TXT_PAGES'));
			if ($this->getAction() == 'search') {
				$this->pager->setProperty('additional_url', 'search-results/');
			}
		}
	}

	/**
	 * Добавлена паенль поиска товаров
	 *
	 * @return DOMNode
	 * @access public
	 */

	public function build() {
		$result = parent::build();
		if ($this->getType() == self::COMPONENT_TYPE_LIST ) {
			$result->documentElement->insertBefore($this->buildSearchForm(), $result->documentElement->childNodes->item(0));
		}
		return $result;
	}

	/**
	 * Постройка формы поиска
	 *
	 * @return DOMNode
	 * @access private
	 */

	private function buildSearchForm() {
		//inspect($this->getActionParams());
		$result = $this->doc->createElement('searchform');
		$result->setAttribute('title', $this->translate('TXT_SEARCH_CATALOGUE'));
		$result->setAttribute('action', 'search-results');
		$field = $this->doc->createElement('search_field', (isset($_GET[self::SEARCH_FIELD_NAME]))?$_GET[self::SEARCH_FIELD_NAME]:'');
		$field->setAttribute('action_title', $this->translate('BTN_SEARCH'));
		$field->setAttribute('name', self::SEARCH_FIELD_NAME);
		$result->appendChild($field);
		return $result;
	}

	/**
	 * Выводит результаты поиска
	 *
	 * @return void
	 * @access public
	 */

	public function search() {
		$this->document->componentManager->getComponentByName('breadCrumbs')->addCrumb();
		$this->prepare();
		if(!$this->getData()){
			$this->addTranslation('MSG_EMPTY_SEARCH_RESULT');
		}
		if ($component = $this->document->componentManager->getComponentByName('productDivisions')) {
			$component->disable();
		}
		$this->document->setProperty('title', $this->translate('TXT_SEARCH_RESULT'));

	}

}

