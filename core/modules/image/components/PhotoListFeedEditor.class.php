<?php
/**
 * Содержит класс PhotoListFeedEditor
 *
 * @package energine
 * @subpackage image
 * @author dr.Pavka
 * @copyright Energine 2007
 * @version $Id$
 */

/**
 * ОПИСАНИЕ_КЛАССА
 *
 * @package energine
 * @subpackage image
 * @author dr.Pavka
 */
class PhotoListFeedEditor extends FeedEditor {
    /**
     * Дефолтное значение для width thumbnail
     *
     */
    const THUMB_WIDTH = 100;
    /**
     * Дефолтное значение для height thumbnail
     *
     */
    const THUMB_HEIGHT = 100;

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
	 * Добавляем thumbnail
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

        $width = ($width = $this->getConfigValue('thumbnail.width'))?$width:PhotoListFeedEditor::THUMB_WIDTH;
        $height = ($height = $this->getConfigValue('thumbnail.height'))?$height:PhotoListFeedEditor::THUMB_HEIGHT;
        
        if (!isset($_POST[$this->getTableName()]['pg_thumb_img'])){
			$this->generateThumbnail(
				$_POST[$this->getTableName()]['pg_photo_img'], 
				'pg_thumb_img', 
				$width, 
				$height, 
				$filter
			);        	
        }
        return $result;
    }
}