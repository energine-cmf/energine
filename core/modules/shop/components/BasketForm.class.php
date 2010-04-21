<?php
/**
 * Содержит класс BasketForm
 *
 * @package energine
 * @subpackage shop
 * @author dr.Pavka
 * @copyright ColoCall 2006
 * @version $Id$
 */

/**
 * Корзина с выбранными продуктами
 *
 * @package energine
 * @subpackage shop
 * @author dr.Pavka
 */
class BasketForm extends DataSet {
    /**
     * Объект - корзина
     *
     * @var Basket
     * @access private
     */
    private $basket;


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
        $this->basket = Basket::getInstance();
        $this->setType(self::COMPONENT_TYPE_LIST);
    }


    /**
      * Для поля product_id изменяем тип
      *
      * @return DataDescription
      * @access protected
      */

    protected function createDataDescription() {
        $result = parent::createDataDescription();
        $ProductIDFieldDescription = $result->getFieldDescriptionByName('product_id');
        $ProductIDFieldDescription->setType(FieldDescription::FIELD_TYPE_STRING);
        $ProductIDFieldDescription->setMode(FieldDescription::FIELD_MODE_READ);
        
        return $result;
    }
    
    protected function createData(){
    	$result = parent::createData();
    	
    	if(!$result->isEmpty() && $this->getDataDescription()->getFieldDescriptionByName('product_images')){
    	   $this->buildProductImagesField($result);
    	}
    	
    	return $result;
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
     * Загружаем цены
     *
     * @return array
     * @access protected
     */

    protected function loadData() {
        $result = $this->basket->getFormattedContents();
        //Подсчитываем сумму
        if (!empty($result)) {
            $this->setProperty('summ', $this->basket->getTotal());
            $this->addTranslation('TXT_BASKET_SUMM');
        }
        else {
            //Если корзина пустая - добавляем перевод сообщения
        	$this->addTranslation('TXT_BASKET_EMPTY');
        }
        return $result;
    }
}
