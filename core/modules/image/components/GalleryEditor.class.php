<?php
/**
 * Содержит класс GalleryEditor
 *
 * @package energine
 * @subpackage image
 * @author dr.Pavka
 * @copyright Energine 2007
 * @version $Id$
 */

//require_once('core/modules/share/components/Grid.class.php');

/**
 * Редактор фотогалереи
 *
 * @package energine
 * @subpackage image
 * @author dr.Pavka
 */
class GalleryEditor extends FeedEditor {
    /**
     * Конструктор класса
     *
     * @param string $name
     * @param string $module
     * @param Document $document
     * @param array $params
     * @access public
     */
    public function __construct($name, $module, Document $document, array $params = null) {
        parent::__construct($name, $module, $document, $params);
        $this->setTableName('image_photo_gallery');
        $this->setOrder(array('pg_order_num'=>QAL::ASC));
        $this->setOrderColumn('pg_order_num');
    }
    /**
     * При необходимости осуществляем генерацию thumbnail
     * 
     *    
     */
    protected function saveData(){
        $result = parent::saveData();
        if(!($field = $this->getSaver()->getDataDescription()->getFieldDescriptionByName('pg_thumb_img'))){
        	
        }
        
        return $result;
    }
    
}