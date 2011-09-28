<?php
/**
 * Содержит класс Remover
 *
 * @package energine
 * @subpackage share
 * @author dr.Pavka
 * @copyright Energine 2006
 */


/**
 * Класс которому передается имя компонента, который необходимо удалить
 * Класс используется для случаев, когда пользователи с разными правми должны видеть разные компоненты
 *
 * @package energine
 * @subpackage share
 * @author dr.Pavka
 */
class Remover extends Component {
    /**
     * @param $name
     * @param $module
     * @param array|null $params
     */
    public function __construct($name, $module,   array $params = null) {
        parent::__construct($name, $module,  $params);
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
	            $component = $this->document->componentManager->getBlockByName($this->getParam('componentName'))
            )
        ) {
        	$component->disable();
        }
     }
}