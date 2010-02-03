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

//require_once('core/modules/share/components/DBDataSet.class.php');
//require_once('core/modules/shop/components/Discounts.class.php');
//require_once('core/modules/shop/components/CurrencyConverter.class.php');
//require_once('core/modules/shop/components/ProductStatusEditor.class.php');

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
        if ($result->getFieldDescriptionByName('product_price')) {
            $discountFD = new FieldDescription('product_price_with_discount');
            $discountFD->setType(FieldDescription::FIELD_TYPE_FLOAT);
            $discountFD->setMode(FieldDescription::FIELD_MODE_READ);
            $result->addFieldDescription($discountFD);
        }
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
            for ($i = 0; $i < $result->getRowCount(); $i++) {
                if ($producerID = $result->getFieldByName('producer_id')) {
                    list($producerData) = $this->dbh->select('shop_producers', array('producer_name', 'producer_segment'), array('producer_id'=>$producerID->getRowData($i)));
                    $producerID->setRowProperty($i, 'producerID', $producerID->getRowData($i));
                    $producerID->setRowProperty($i, 'producer_segment', $producerData['producer_segment']);
                    $producerID->setRowData($i, $producerData['producer_name']);
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
     * Добавляет значение product_price(если есть в перечне полей)
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

            foreach ($result as $key => $row) {
                $res = $this->dbh->selectRequest(sprintf('SELECT product_price, curr_id FROM shop_product_external_properties
                    WHERE product_code IN (
                    SELECT product_code FROM shop_products WHERE product_id = %s
                    )', $row['product_id']));
                list($res) = $res;
                $result[$key]['product_price'] = $converter->convert($res['product_price'], $currentCurrency, $res['curr_id']);
            }
        }
        if (is_array($result) && $this->getDataDescription()->getFieldDescriptionByName('product_price_with_discount')&& $this->getDataDescription()->getFieldDescriptionByName('product_price')) {
            $discounts = Discounts::getInstance();
            $this->setProperty('discount', $discounts->getDiscountForGroup());
            foreach ($result as $key => $row) {
                $result[$key]['product_price_with_discount'] = $converter->format($discounts->calculateCost($result[$key]['product_price']), $currentCurrency);
                $result[$key]['product_price'] = $converter->format($result[$key]['product_price'], $currentCurrency);
            }
        }
        return $result;
    }

    /**
      * Выводит продукт
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
        $res = $this->dbh->selectRequest('
            SELECT pp.pp_id, pp_name, pp_type
            FROM `shop_product_params` pp
            LEFT JOIN shop_product_params_translation ppt ON ppt.pp_id = pp.pp_id
            WHERE pt_id in(
                SELECT pt_id from shop_products where product_id = %s
                )
            AND lang_id = %s
            ',
        $id,
        $this->document->getLang()
        );

        if(is_array($res))
        foreach ($res as $row) {
            $paramName = 'product_param_'.$row['pp_id'];
            //Для каждого параметра создаем FieldDescription
            $paramFD = new FieldDescription($paramName);
            $paramFD->setType($row['pp_type']);
            $paramFD->setProperty('title', $row['pp_name']);
            $paramFD->setProperty('param', $this->translate('TXT_PRODUCT_PARAMS'));
            $this->getDataDescription()->addFieldDescription($paramFD);
            // находим его значение для данного продукта

            $paramValue = simplifyDBResult($this->dbh->selectRequest(
            'SELECT pp_value FROM `shop_product_param_values` pp
                WHERE pp.pp_id = %s and pp.product_id = %s',
             $row['pp_id'], $id
            ), 'pp_value', true);
            $paramDD = new Field('product_param_'.$row['pp_id']);

            $paramDD->setData($paramValue);
            $this->getData()->addField($paramDD);
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

