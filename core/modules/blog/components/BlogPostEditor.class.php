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

     * @param array $params
     * @access public
     */
    public function __construct($name, $module,   array $params = null) {
        parent::__construct($name, $module,  $params);
        $this->setTableName('blog_post');
//        $this->setOrderColumn('post_created');
    }
}