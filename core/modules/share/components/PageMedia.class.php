<?php 
/**
 * Содержит класс PageMedia
 *
 * @package energine
 * @subpackage share
 * @author d.pavka
 * @copyright d.pavka@gmail.com
 */

 /**
  * Выводит на странице медиаконтейнер из меди файлов присоединенных к данной странице
  *
  * @package energine
  * @subpackage share
  * @author d.pavka@gmail.com
  */
 class PageMedia extends DataSet {
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
    }
    
    /**
      * Выводит галлерею
      * 
      * @return void
      * @access protected
      */
    protected function main(){
        $this->prepare();
        $am = new AttachmentManager($this->getDataDescription(), $this->getData(), 'share_sitemap');
        $am->createFieldDescription();
        $am->createField('smap_id', false, $this->document->getID());
    }
}