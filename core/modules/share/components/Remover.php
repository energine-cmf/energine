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
namespace Energine\share\components;

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
    /**
     * @copydoc Component::defineParams
     */
    protected function defineParams() {
        // Добавлен параметр имя компонента
        return array_merge(
            parent::defineParams(),
            array(
	            'componentName' => false,
                'force' => false
        ));
    }

    /**
     * @copydoc Component::main
     */
     protected function main() {
         // Дизейблит компонент
        if ($component = $this->document->componentManager->getBlockByName($this->getParam('componentName'))) {
            if($this->getParam('force') && ($this->document->getRights() == ACCESS_FULL) && !$this->document->isEditable()){
                $component->disable();
            }
            elseif($this->document->getRights() != ACCESS_FULL) {
                $component->disable();
            }

        }
     }
}