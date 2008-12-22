<?php
/**
 * Содержит класс PhotoListFeed
 *
 * @package energine
 * @subpackage image
 * @author dr.Pavka
 * @copyright ColoCall 2007
 * @version $Id$
 */

/**
 * Фотогалерея
 *
 * @package energine
 * @subpackage image
 * @author dr.Pavka
 */
class PhotoListFeed extends Feed {
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
        $this->setOrder(array('pg_date'=>QAL::DESC));
	}

    /**
	 * Добавляем свойства real_width и real_height
	 *
	 * @return Data
	 * @access protected
	 */

	 protected function createData() {
	    $result = parent::createData();
        if ($result && $photo = $result->getFieldByName('pg_photo_img')) {
            foreach ($photo as $rowIndex => $fileName) {
                list($width, $height) = getimagesize($fileName);
            	$photo->setRowProperty($rowIndex, 'width', $width);
            	$photo->setRowProperty($rowIndex, 'height', $height);
            }
        }
	    return $result;
	 }
}