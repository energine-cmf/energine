<?php
/**
 * Содержит класс GalleryFeed
 *
 * @package energine
 * @subpackage image
 * @author dr.Pavka
 * @copyright Energine 2007
 * @version $Id$
 */

/**
 * Фотогалерея
 *
 * @package energine
 * @subpackage image
 * @author dr.Pavka
 */
class GalleryFeed extends Feed {
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
                if (file_exists($fileName)){
                    list($width, $height) = getimagesize($fileName);

                }
                else {
                    list($width, $height) = array(100, 100);
                }
                $photo->setRowProperty($rowIndex, 'width', $width);
                $photo->setRowProperty($rowIndex, 'height', $height);
            }
        }
	    return $result;
	 }
}