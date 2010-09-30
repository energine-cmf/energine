<?php
/**
 * Содержит класс ForumThemesBlock
 *
 * @package energine
 * @subpackage forum
 * @author d.pavka
 * @copyright d.pavka@gmail.com
 */

/**
 * Блок тем форума
 *
 * @package energine
 * @subpackage forum
 * @author d.pavka@gmail.com
 */
class ForumThemesBlock extends DataSet {
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

    protected function createBuilder(){
        return new SimpleBuilder();
    }

    protected function defineParams(){
        return array_merge(
        parent::defineParams(),
        array(
        'limit' => 10
        )
        );
    }
}