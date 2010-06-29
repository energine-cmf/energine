<?php 
/**
 * Содержит класс LinkingEditor
 *
 * @package energine
 * @subpackage stb
 * @author d.pavka
 * @copyright d.pavka@gmail.com
 */

 /**
  * Редактор 
  *
  * @package energine
  * @subpackage stb
  * @author d.pavka@gmail.com
  */
 class LinkingEditor extends Grid {
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
        $this->isEditable = $this->document->isEditable();
        $this->setProperty('exttype', 'feededitor');
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
        'linkTo' => false
        )
        );
    }    
    /**
     * Убираем все лишнее
     *
     * @return void
     * @access protected
     */

    protected function main() {
        $_SESSION[$this->getPK()] =
            $this->document->componentManager->getComponentByName(
	           $this->getParam('linkTo')
	        )->getFilter();
        $this->addToolbar($this->createToolbar());
        $this->js = $this->buildJS();
    }

    protected function changeOrder($direction){
        $this->setFilter($_SESSION[$this->getPK()]);
        //unset($_SESSION['feed_smap_id']);

        return parent::changeOrder($direction);
    }

    public function build() {
        if ($this->getAction() == 'main') {
            if ($param = $this->getParam('linkTo')) {
                $this->setProperty('linkedComponent', $param);
            }
            $result = Component::build();
            if (($component = $this->document->componentManager->getComponentByName($param)) && ($component->getAction() != 'view') && $this->isEditable) {
                if ($this->js) {
                    $result->documentElement->appendChild($result->importNode($this->js, true));
                }
                $result->documentElement->appendChild($result->createElement('recordset'));
                if (($tbs = $this->getToolbar()) && (!empty($tbs))) {
                    foreach($tbs as $tb)
                    if($toolbar = $tb->build()) {
                        $result->documentElement->appendChild($result->importNode($toolbar, true));
                    }
                }
                 
            }
        }
        else {
            if ($this->getType() !== self::COMPONENT_TYPE_LIST ) {
                $this->setProperty('exttype', 'grid');
            }
            $result = parent::build();
        }

        return $result;
    }
    
}