<?php
/**
 * Содержит класс Comments.
 *
 * @package energine
 * @subpackage misc
 * @author d.pavka
 * @copyright d.pavka@gmal.com
 * @version $Id$
 */

 /**
 * Класс предназначееный для присоединения комментариев к страницам, и другим сущностям
 *
 * @package energine
 * @subpackage misc
 * @author d.pavka
 * @final
 */
final class Comments extends DBDataSet {
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
/*$this->setFilter(array($this->getParam('linkField')=>$this->getParam('linkFieldValue'), 'lang_id'=>$this->document->getLang()));*/
        $this->setParam('recordsPerPage', false);
if ($linkComponent = $this->document->componentManager->getComponentByName($this->getParam('mainComponent'))) {
            if ($linkComponent->getAction() !== 'view') {
            	$this->disable();
            }
        }
        $this->setProperty('title', $this->translate('TXT_'.$this->getName()));
    }

    /**
     * Добавляем параметр  - имя связанного компонента
     *
     * @return array
     * @access protected
     */

    protected function defineParams() {
        return array_merge(
        parent::defineParams(),
            array(
            'mainComponent' => false,
            'linkField' => false            )
        );
    }
}