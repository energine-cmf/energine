<?php 
/**
 * Содержит класс BlogPostEditor
 *
 * @package energine
 * @subpackage blog
 * @author sign
 */

 /**
  * Редактор постов блога
  *
  * @package energine
  * @subpackage blog
  * @author sign
  */
 class BlogPostEditor extends Grid {
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
        $this->setTableName('blog_post');
//        $this->setOrderColumn('post_created');
    }
}