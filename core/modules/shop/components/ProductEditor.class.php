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

    /**
     * Редактор параметров
     *
     * @var ParamValuesEditor
     * @access private
     */
    private $paramValueEditor = false;

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
        $_POST['selectorID'] = Sitemap::getInstance()->getIDByURI(array('shop'));
        $this->divEditor = $this->document->componentManager->createComponent('divEditor', 'share', 'DivisionEditor', array('action' => 'selector'));
        $this->divEditor->run();
    }

    /**
     * Для метода showTree вызывается свой билдер
     *
     * @return DOMNode
     * @access public
     */

    public function build() {
        if ($this->getAction() == 'showTree') {
            $result = $this->divEditor->build();
        }
        elseif ($this->getAction() == 'showParams') {
            $result = $this->paramValueEditor->build();
        }
        else {
            $result = parent::build();
        }

        return $result;
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
                'WHERE ct.lang_id = %s', Language::getInstance()->getCurrent()
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
            
        	$field = $this->getData()->getFieldByName('smap_id');
            $this->addAttFilesField(
                'share_sitemap_uploads',
                $this->dbh->selectRequest('
                    SELECT files.upl_id, upl_path, upl_name
                    FROM `share_product_uploads` p2f
                    LEFT JOIN `share_uploads` files ON p2f.upl_id=files.upl_id
                    WHERE product_id = %s
                ', $this->getData()->getFieldByName('product_id')->getRowData(0))
            );
        	
            $res = $this->dbh->select('share_sitemap_translation', array('smap_name'), array('smap_id' => $field->getRowData(0), 'lang_id' => $this->document->getLang()));
            if (!empty($res)) {
                for ($i = 0; $i < count(Language::getInstance()->getLanguages()); $i++) {
                    $field->setRowProperty($i, 'data_name', simplifyDBResult($res, 'smap_name', true));
                }
            }
        }
        elseif($this->getAction() == 'add'){
        	$this->addAttFilesField('share_sitemap_uploads');
        }
    }

    /**
     * При сохранении генерим thumbnail
     *
     * @return mixed
     * @access protected
     */

    protected function saveData() {
        $result = parent::saveData();

        //Если пустой фильтр  - значит у нас метод вставки
        $filter  = $this->saver->getFilter();
        if (empty($filter)) {
            $filter = array($this->getPK()=>$this->saver->getResult());

        }

        /*
        $res = $this->dbh->select($this->getTableName(), array('product_photo_img', 'product_code'), $filter);
        if (!is_array($res) || empty($res)) {
            throw new SystemException('ERR_BAD_DATA', SystemException::ERR_CRITICAL);
        }
        */
        
        //Получили имя файла для исходного изображения
        //$sourceFileName = simplifyDBResult($res, 'product_photo_img', true);
        
        //Создаем thumbnail в том случае если не указан вывод маленькой фотки в форме
        /*
        if (
            !$this->saver->getDataDescription()->getFieldDescriptionByName('product_thumb_img')
            && !empty($sourceFileName)
        ) {
            $this->generateThumbnail($sourceFileName, 'product_thumb_img', 
                $this->getConfigValue('shop.thumbnail.width'), 
                $this->getConfigValue('shop.thumbnail.height'), $filter, true);
        }
*/

        //Сохраняем данные в таблице дополнительных свойств

        if($productCode = simplifyDBResult($res, 'product_code', true)) {
            //Удаляем все записи с таким кодом продукта
            $this->dbh->modify(QAL::DELETE, $this->getExternalTableName(), null, array('product_code' => $productCode));

            $price = (isset($_POST[$this->getExternalTableName()]['product_price']))?$_POST[$this->getExternalTableName()]['product_price']:0;
            $count = (isset($_POST[$this->getExternalTableName()]['product_count']))?$_POST[$this->getExternalTableName()]['product_count']:0;
            $currency = (isset($_POST[$this->getExternalTableName()]['curr_id']))?$_POST[$this->getExternalTableName()]['curr_id']:0;
            //Вставляем записи
            $res = $this->dbh->modify(QAL::INSERT , $this->getExternalTableName(), array('product_code' => $productCode, 'product_price' => $price, 'product_count' => $count, 'curr_id'=> $currency));
        }

        //Для режима вставки в таблицу значений параметров для всех языков добавляем пустые значения
        if ($this->saver->getMode() == QAL::INSERT) {
            $productID = $this->saver->getResult();

            $res = $this->dbh->select('shop_products', 'pt_id' , array('product_id' => $productID));
            $ptID = simplifyDBResult($res, 'pt_id', true);
            $res = $this->dbh->select('shop_product_params', 'pp_id', array('pt_id'=>$ptID));
            if (is_array($res)) {
                $langAbbr = array_keys(Language::getInstance()->getLanguages());
                foreach ($res as $paramInfo) {
                    $ppID = $paramInfo['pp_id'];
                    $ppvID = $this->dbh->modify(QAL::INSERT, 'shop_product_param_values', array('product_id' => $productID, 'pp_id' => $ppID));
                    foreach ($langAbbr as $langID) {
                        $this->dbh->modify(QAL::INSERT, 'shop_product_param_values_translation', array('ppv_id'=>$ppvID, 'lang_id'=>$langID, 'ppv_value'=>QAL::EMPTY_STRING));
                    }
                }
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

    protected function showParams() {
        $productID = $this->getActionParams();
        $productID = $productID[0];
        $this->request->setPathOffset($this->request->getPathOffset() + 2);
        $this->paramValueEditor = $this->document->componentManager->createComponent('paramValuesEditor', 'shop', 'ParamValuesEditor', array('productID'=>$productID));
        //$this->paramValueEditor->getAction();
        $this->paramValueEditor->run();
    }
}
