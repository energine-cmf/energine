<?php
/**
 * Содержит класс ForumCategories
 *
 * @package energine
 * @subpackage forum
 * @author d.pavka
 * @copyright d.pavka@gmail.com
 */

/**
 * Список категорий форума
 *
 * @package energine
 * @subpackage forum
 * @author d.pavka@gmail.com
 */
class ForumCategories extends PageList {
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
    }
    /**
     * Значения параметров выставлены
     *
     * @return int
     * @access protected
     */

    protected function defineParams() {
        $result = array_merge(parent::defineParams(),
            array(
                'id' => self::CURRENT_PAGE,
                'recursive' => true,
                'recordsPerPage' => false
            ));
        return $result;
    }
}