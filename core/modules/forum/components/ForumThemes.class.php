<?php
/**
 * Содержит класс ForumTheme
 *
 * @package energine
 * @subpackage forum
 * @author d.pavka
 * @copyright d.pavka@gmail.com
 */

 /**
  * Список тем в определенной категории
  *
  * @package energine
  * @subpackage forum
  * @author d.pavka@gmail.com
  */
 class ForumThemes extends Forum {
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
    }
}