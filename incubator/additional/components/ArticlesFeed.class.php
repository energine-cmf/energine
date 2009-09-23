<?php
/**
 * Содержит класс ArticlesFeed.
 *
 * @package energine
 * @subpackage misc
 * @author d.pavka
 * @copyright d.pavka@gmail.com
 */

 /**
 * Список статей
 *
 * @package energine
 * @subpackage misc
 * @author d.pavka
 */
 class ArticlesFeed extends Feed {
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
        $this->setTableName('aux_articles');
    }
    
    protected function createDataDescription() {
    	return DBDataSet::createDataDescription();
    }
    
    /**
     * Добавляем поле содержащее путь
     *
     * @access protected
     * @return array
     */
    protected function loadData() {
        $result = parent::loadData();
        if (is_array($result)) {
            $result = array_map(
                create_function('$row','
                    $row["art_url"] = Sitemap::getInstance()->getURLByID($row["smap_id"]);
                    return $row;
                '),
                $result
            );
        }

        return $result;
    }

}