<?php
/**
 * @file
 * Remover
 *
 * It contains the definition to:
 * @code
class Remover;
@endcode
 *
 * @author dr.Pavka
 * @copyright Energine 2006
 */


/**
 * Store the component name, that should be removed.
 *
 * @code
class Remover;
@endcode
 *
 * This is used when the users with different rights should see different components.
 */
class Remover extends Component {
    //todo VZ: this can be removed.
    /**
     * @copydoc Component::__construct
     */
    public function __construct($name, $module,   array $params = null) {
        parent::__construct($name, $module,  $params);
	}

    /**
     * @copydoc Component::defineParams
     */
    protected function defineParams() {
        // Добавлен параметр имя компонента
        return array_merge(
            parent::defineParams(),
            array(
	            'componentName' => false
        ));
    }

    /**
     * @copydoc Component::main
     */
     protected function main() {
         // Дизейблит компонент
        if ($this->document->getRights() != ACCESS_FULL
            && $component = $this->document->componentManager->getBlockByName($this->getParam('componentName'))
        ) {
        	$component->disable();
        }
     }
}