<?php

/**
 * Содержит класс ProductEditor
 *
 * @package energine
 * @subpackage shop
 * @author dr.Pavka
 * @copyright ColoCall 2006
 * @version $Id$
 */

/**
 * Редактор продуктов
 *
 * @package energine
 * @subpackage shop
 * @author dr.Pavka
 */
class ProductEditor extends Grid {
    /**
     * Дерево разделов
     *
     * @var DivisionEditor
     * @access private
     */
    private $divEditor;
    /**
     * Имя таблицы содержащей дополнительные данные(в эту таблицу будут загружаться данные из системы учета)
     *
     * @var string
     * @access private
     */
    private $externalTableName;
    
    private $manufacturerEditor;

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
        $this->setOrder('product_id', QAL::DESC);
        $this->externalTableName = 'shop_product_external_properties';
    }

    /**
     * Возвращает имя таблицы с дополниельными данными
     *
     * @return string
     * @access protected
     * @final
     */

    final protected function getExternalTableName() {
        return $this->externalTableName;
    }
    
    /**
	 * Выводит дерево разделов
	 *
	 * @return void
	 * @access protected
	 */

    protected function showTree() {
        $this->request->setPathOffset($this->request->getPathOffset() + 1);
        
        //$_POST['selectorID'] = Sitemap::getInstance()->getIDByURI(array('shop'));
        $this->divEditor = $this->document->componentManager->createComponent(
            'divEditor', 
            'share', 
            'DivisionEditor', 
            array(
                'action' => 'selector',
                'configFilename' => 'core/modules/shop/config/ProductDivisionEditor.component.xml'
            )
        );
        $this->divEditor->run();
    }

    /**
     * Для метода showTree вызывается свой билдер
     *
     * @return DOMNode
     * @access public
     */

    public function build() {
    	switch ($this->getAction()){
    		case 'showTree':
    			$result = $this->divEditor->build();
    			break;
    		case 'addManufacturer':
    			$result = $this->manufacturerEditor->build();
    			break;
    		default:
    			$result = parent::build();
    	}
        

        return $result;
    }

    /**
     * Возвращает данные о значения в связанной таблицы
     * Добавлена возможность вызвать окно "добавить производителя" из списка выбора
     *  
     * @return array
     * @access protected
     */

    protected function getFKData($fkTableName, $fkKeyName) {
    	$result = $this->dbh->getForeignKeyData($fkTableName, $fkKeyName, $this->document->getLang()); 
    	if(in_array($this->getAction(), array('add', 'edit')) && $fkKeyName == 'producer_id'){
    	   $addManufacturerOption  = array(
               array(
                   $result[1] => '-1',
                   $result[2] => $this->translate('TXT_ADD_MANUFACTURER'),
                   'label' => $this->translate('TXT_OR')
               )
           ); 
    	   if($result[0] === true){
    	       $result[0] = $addManufacturerOption;  
    	   }
    	   else{
    	       $result[0] = array_merge($result[0], $addManufacturerOption);	
    	   }	
    	}
    	
        return $result;
    } 

    protected function addManufacturer(){
        $this->request->setPathOffset($this->request->getPathOffset() + 1);
        $this->manufacturerEditor = 
            $this->document->componentManager->createComponent(
                'manufacturerEditor', 
                'shop', 
                'ManufacturersEditor', 
                array(
                    'action'=>'add',
                    'configFilename' => 'core/modules/shop/config/AddManufacturerForm.component.xml'
                ));
        $this->manufacturerEditor->run();
    }

    /**
      * Для поля smap_id формируется Дерево разделов
      * Добавляются поля product_price, curr_id
      *
      * @return DataDescription
      * @access protected
      */

    protected function createDataDescription() {
        $result = parent::createDataDescription();

        
        if (in_array($this->getAction(), array('add', 'edit'))) {
        	$this->addTranslation('FIELD_PP_NAME', 'FIELD_PPV_VALUE');
        	$fdPtID = $result->getFieldDescriptionByName('pt_id');
        	$fdPtID->removeProperty('nullable'); 
        	$fdPtID->setProperty('tabName', $this->translate('TAB_PRODUCT_PARAMS'));
        	$result->getFieldDescriptionByName('producer_id')->removeProperty('nullable');
            $smapPIDFieldDescription = $result->getFieldDescriptionByName('smap_id');
            $smapPIDFieldDescription->setType(FieldDescription::FIELD_TYPE_STRING);
            $smapPIDFieldDescription->setMode(FieldDescription::FIELD_MODE_READ);
            if ($productPriceFieldDescription = $result->getFieldDescriptionByName('product_price')) {
                $productPriceFieldDescription->setProperty('nullable', false);
                $productPriceFieldDescription->setType(FieldDescription::FIELD_TYPE_FLOAT);
                $productPriceFieldDescription->setProperty('title', $this->translate('FIELD_PRODUCT_PRICE'));
                $productPriceFieldDescription->setProperty('tableName', $this->getExternalTableName());
            }
            if ($productCurrFieldDescription = $result->getFieldDescriptionByName('curr_id')) {
                $productCurrFieldDescription->setType(FieldDescription::FIELD_TYPE_SELECT );
                $productCurrFieldDescription->setProperty('title', $this->translate('FIELD_CURR_ID'));
                $productCurrFieldDescription->setProperty('tableName', $this->getExternalTableName());
                $currencyOptions = $this->dbh->selectRequest(
                'SELECT c.curr_id, curr_name FROM shop_currency c '.
                'LEFT JOIN shop_currency_translation ct ON ct.curr_id = c.curr_id '.
                'WHERE ct.lang_id = %s '.
                'ORDER by curr_order_num', Language::getInstance()->getCurrent()
                );
                $productCurrFieldDescription->loadAvailableValues($currencyOptions, 'curr_id', 'curr_name');
            }

            if($productCountFieldDescription = $result->getFieldDescriptionByName('product_count')){
                $productCountFieldDescription->setType(FieldDescription::FIELD_TYPE_INT);
                $productCountFieldDescription->setProperty('title', $this->translate('FIELD_PRODUCT_COUNT'));
                $productCountFieldDescription->setProperty('tableName', $this->getExternalTableName());
            }
        }
        elseif ($this->getAction() == 'getRawData' && ($productCountFieldDescription = $result->getFieldDescriptionByName('product_count'))) {
            $productCountFieldDescription->setType(FieldDescription::FIELD_TYPE_INT);
            $productCountFieldDescription->setProperty('title', $this->translate('FIELD_PRODUCT_COUNT'));
        }
        return $result;
    }

    /**
     * Для метода edit добавляем данные о полях из внешней таблицы
     *
     * @return array
     * @access protected
     */

    protected function loadData() {
        $result = parent::loadData();
        if (($this->getType() == self::COMPONENT_TYPE_FORM_ALTER) && isset($result[0]['product_code'])) {
            $productCode = $result[0]['product_code'];
            $res = $this->dbh->select($this->getExternalTableName(), array('product_price', 'product_count', 'curr_id'), array('product_code' => $productCode));
            if (is_array($res)) {
                list($res) = $res;
            }
            else {
                $res = array(
                'product_price' => 0,
                'product_count' => 0,
                'curr_id' => 1
                );
            }
            foreach ($result as $langID => $data) {
                $result[$langID]['product_price'] = $res['product_price'];
                $result[$langID]['product_count'] = $res['product_count'];
                $result[$langID]['curr_id'] = $res['curr_id'];
            }
        }
        elseif ($this->getType() == self::COMPONENT_TYPE_FORM_ADD) {
            foreach ($result as $langID => $data) {
                if (isset($result[$langID]['ps_id'])) {
                    $result[$langID]['ps_id'] = ProductStatusEditor::getDefaultStatus();
                }
                $result[$langID]['product_count'] = 1;
            }
        }
        elseif ($this->getAction() == 'getRawData' && is_array($result) && $this->getDataDescription()->getFieldDescriptionByName('product_count')) {
            $ids = array_map(create_function('$row', 'return $row[\'product_id\'];'), $result);
            $request  = 'SELECT product_id, product_count FROM `shop_product_external_properties` prop LEFT JOIN shop_products p ON prop.`product_code` = p.`product_code` WHERE p.product_id IN ('.implode(',', $ids).')';
            $res = convertDBResult($this->dbh->selectRequest($request), 'product_id', true);
            foreach ($result as $key => $row) {
            	$result[$key]['product_count'] = $res[$row['product_id']]['product_count'];
            }
        }


        return $result;
    }
    /**
     * Для метода редактирования изменяем данные для поля smap_id
     *
     * @return Builder
     * @access protected
     */

    protected function prepare() {
        parent::prepare();
        if ($this->getAction() == 'edit') {
            
        	$field = $this->getData()->getFieldByName('product_id');
            $this->addAttFilesField(
                'shop_product_uploads',
                $this->dbh->selectRequest('
                    SELECT files.upl_id, upl_path, upl_name
                    FROM `shop_product_uploads` p2f
                    LEFT JOIN `share_uploads` files ON p2f.upl_id=files.upl_id
                    WHERE product_id = %s
                ', $this->getData()->getFieldByName('product_id')->getRowData(0))
            );
        	
            $field = $this->getData()->getFieldByName('smap_id');
            $smapSegment = '';
            if($field->getRowData(0) !== null) {
                $smapSegment = Sitemap::getInstance()->getURLByID($field->getRowData(0));
            }
            $smapName = simplifyDBResult($this->dbh->select('share_sitemap_translation', array('smap_name'), array('smap_id' => $field->getRowData(0), 'lang_id' => $this->document->getLang())), 'smap_name', true);

            for ($i = 0; $i < count(Language::getInstance()->getLanguages()); $i++) {
                $field->setRowProperty($i, 'data_name', $smapName);
                $field->setRowProperty($i, 'segment', $smapSegment);
            }
        }
        elseif($this->getAction() == 'add'){
        	$this->addAttFilesField('shop_product_uploads');
        }
        /*
        if(in_array($this->getAction(), array('add', 'edit'))){
            $fd = $this->getDataDescription()->getFieldDescriptionByName('producer_id');
            inspect($fd->getAvailableValues());
        }*/   
    }

    /**
     * При сохранении генерим thumbnail
     *
     * @return mixed
     * @access protected
     */

    protected function saveData() {
    	if(($this->getPreviousAction() == 'add') && (!isset($_POST[$this->getTableName()]['product_segment']) || empty($_POST[$this->getTableName()]['product_segment'])) ){
    	   	$_POST[$this->getTableName()]['product_segment'] = Translit::transliterate($_POST[$this->getTranslationTableName()][Language::getInstance()->getDefault()]['product_name'], '-', true);
    	}
        $result = parent::saveData();

        //Если пустой фильтр  - значит у нас метод вставки
        $filter  = $this->saver->getFilter();
        if (empty($filter)) {
            $filter = array($this->getPK()=>$this->saver->getResult());

        }

        //Сохраняем данные в таблице дополнительных свойств
            $productCode = $_POST[$this->getTableName()]['product_code'];
            //Удаляем все записи с таким кодом продукта
            $this->dbh->modify(QAL::DELETE, $this->getExternalTableName(), null, array('product_code' => $productCode));

            $price = (isset($_POST[$this->getExternalTableName()]['product_price']))?$_POST[$this->getExternalTableName()]['product_price']:0;
            $count = (isset($_POST[$this->getExternalTableName()]['product_count']))?$_POST[$this->getExternalTableName()]['product_count']:0;
            $currency = (isset($_POST[$this->getExternalTableName()]['curr_id']))?$_POST[$this->getExternalTableName()]['curr_id']:0;
            //Вставляем записи
            $res = $this->dbh->modify(QAL::INSERT , $this->getExternalTableName(), array('product_code' => $productCode, 'product_price' => $price, 'product_count' => $count, 'curr_id'=> $currency));
        $productID = ($this->saver->getMode() == QAL::INSERT)?$this->saver->getResult():$_POST[$this->getTableName()]['product_id'];
        
        if(isset($_POST['shop_product_param_values'])){
	        $productParams = array_filter($_POST['shop_product_param_values']);
	        if(!empty($productParams)){
	            $this->dbh->modify(
	                QAL::DELETE,
	                'shop_product_param_values', 
	                null, 
	                array('product_id'=>$productID)
	            );
	            foreach($productParams as $ppID => $ppValue){
	            	$this->dbh->modify(
	            	   QAL::INSERT,
	            	   'shop_product_param_values', 
	            	   array(
	            	       'product_id' => $productID,
	            	       'pp_id' => $ppID,
	            	       'pp_value' => $ppValue
	            	   )
	            	);
	            }	
	        }
        }
        //Удаляем предыдущие записи из таблицы связей с дополнительными файлами
        $this->dbh->modify(QAL::DELETE, 'shop_product_uploads', null, array('product_id' => $productID));
        
        //записываем данные в таблицу shop_product_uploads
        $this->dbh->modify(QAL::DELETE, 'shop_product_uploads', null, array('product_id' => $productID));
        if(isset($_POST['uploads']['upl_id'])){
            foreach ($_POST['uploads']['upl_id'] as $uplID){
                $this->dbh->modify(QAL::INSERT, 'shop_product_uploads', array('product_id' => $productID, 'upl_id' => $uplID));
            }
        }
       
        
        
        return $result;
    }

    /**
     * Выводит редактор значений парметров
     *
     * @return void
     * @access protected
     */

    protected function loadParams() {
    	//inspect($_POST);
        $typeId = (int)$_POST['pt_id'];
        $productId = (int)$_POST['product_id'];
        
        $data = $this->dbh->selectRequest('
        SELECT p.pp_id, pp_type, pp_name, pp_value FROM `shop_product_params` p
            LEFT JOIN shop_product_params_translation pt On pt.pp_id = p.pp_id
            LEFT JOIN shop_product_param_values pv ON pv.pp_id = p.pp_id and product_id = %s
            WHERE pt_id=%s and pt.lang_id =%s
        ', $productId, $typeId, Language::getInstance()->getDefault());
        
        $JSONResponse = array('result' => true, 'data' => $data);
        
        $this->response->setHeader('Content-Type', 'text/javascript; charset=utf-8');
        $this->response->write(json_encode($JSONResponse));
        $this->response->commit();    
    }
}
