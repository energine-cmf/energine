<?php
/**
 * Содержит класс Remover
 *
 * @package energine
 * @subpackage core
 * @author dr.Pavka
 * @copyright Energine 2006
 * @version $Id: Remover.class.php,v 1.5 2008/08/27 15:39:16 chyk Exp $
 */

//require_once('core/framework/Component.class.php');

/**
 * Класс которому передается имя компонента, который необходимо удалить
 * Класс используется для случаев, когда пользователи с разными правми должны видеть разные компоненты
 *
 * @package energine
 * @subpackage core
 * @author dr.Pavka
 */
class Remover extends Component {
    /**
     * Конструктор класса.
     *
     * @access public
     * @param string $name
     * @param string $module
     * @param Document $document
     * @param array $params
     * @return void
     */
    public function __construct($name, $module, Document $document,  array $params = null) {
        parent::__construct($name, $module, $document,  $params);
	}

    /**
     * Добавлен параметр имя компонента
     *
     * @access protected
     * @return array
     */
    protected function defineParams() {
        return array_merge(
            parent::defineParams(),
            array(
	            'componentName' => false
        ));
    }

    /**
     * Дизейблит компонент
     *
     * @return void
     * @access protected
     */

     protected function main() {
        if (
            (
	            $this->document->getRights() != ACCESS_FULL 
	            && 
	            $component = $this->document->componentManager->getComponentByName($this->getParam('componentName'))
            )
        ) {
        	$component->disable();
        }
     }
}