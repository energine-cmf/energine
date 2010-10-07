<?php 
/**
 * Содержит класс ForumThemeEditor
 *
 * @package energine
 * @subpackage forum
 * @author sign
 */

 /**
  * Редактор категорий форумов
  *
  * @package energine
  * @subpackage forum
  * @author sign
  */
 class ForumThemeEditor extends Grid {
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
        $this->setTableName('forum_theme');
        $this->setOrder(array('theme_created'=>QAL::DESC));
    }

     protected function loadDataDescription(){
         $result = parent::loadDataDescription();
         if(in_array($this->getAction(), array('edit'))){
             unset($result['comment_id']);
         }
         return $result;

     }
}