<?php 
/**
 * Содержит класс LinkingEditor
 *
 * @package energine
 * @subpackage share
 * @author d.pavka
 * @copyright d.pavka@gmail.com
 */

 /**
  * Редактор 
  *
  * @package energine
  * @subpackage share
  * @author d.pavka@gmail.com
  */
 class LinkingEditor extends Grid {
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
        $this->isEditable = $this->document->isEditable();
        $this->setProperty('exttype', 'feededitor');
        if(!in_array($this->getState(),  array('up', 'down')) && isset($_COOKIE[md5($this->getName())])){
            E()->getResponse()->deleteCookie(md5($this->getName()));
        }
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
        'bind' => false
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
        if($this->getFilter())
            E()->getResponse()->addCookie(md5($this->getName()), convert_uuencode($this->document->componentManager->getBlockByName(
                   $this->getParam('bind')
                )->getFilter()));
        $this->addToolbar($this->createToolbar());
        $this->js = $this->buildJS();
    }

    protected function changeOrder($direction){
        if(isset($_COOKIE[md5($this->getName())])){
            $this->setFilter(convert_uudecode($_COOKIE[md5($this->getName())]));
            E()->getResponse()->deleteCookie(md5($this->getName()));
        }
        return parent::changeOrder($direction);
    }

    public function build() {
        if ($this->getState() == 'main') {
            if ($param = $this->getParam('bind')) {
                $this->setProperty('linkedComponent', $param);
            }
            $result = Component::build();
            if (($component = $this->document->componentManager->getBlockByName($param)) /*&& ($component->getState() != 'view')*/ && $this->isEditable) {
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