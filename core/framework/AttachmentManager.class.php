<?php 
/**
 * Содержит класс AttachmentManager
 *
 * @package energine
 * @subpackage share
 * @author d.pavka
 * @copyright d.pavka@gmail.com
 */

 /**
  * Класс предназначен для автоматизации работы с присоединенными файлами
  *
  * @package energine
  * @subpackage share
  * @author d.pavka@gmail.com
  */
 class AttachmentManager extends Singleton {
 	/**
 	 * 
 	 */
 	const ATTACH_TABLENAME = 'share_uploads';
 	
 	/**
 	 * Связанная таблица
 	 * 
 	 * @access private
 	 * @var string 
 	 */
 	 private $mapTableName;
 	 /**
 	  * Значения
 	  * 
 	  * @access private
 	  * @var array 
 	  */
 	  private $mapValue;
    /**
     * Конструктор класса
     *
     * @access public
     */
    public function __construct() {
        parent::__construct();
    }
    
    /**
     * Возвращает поле
     * 
     * @return DOMNode
     * @access public
     */
    public function build(){
        $f = new Field('attachments');
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
    
}
