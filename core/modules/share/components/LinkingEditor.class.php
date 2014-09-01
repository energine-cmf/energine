<?php 
/**
 * @file
 * LinkingEditor
 *
 * It contains the definition to:
 * @code
class LinkingEditor;
@endcode
 *
 * @author d.pavka
 * @copyright d.pavka@gmail.com
 *
 * @version 1.0.0
 */
namespace Energine\share\components;
use Energine\share\gears\Component;
/**
 * Linking editor.
 *
 * @code
class LinkingEditor;
@endcode
 */
class LinkingEditor extends Grid {
    /**
     * @copydoc Grid::__construct
     */
    public function __construct($name, $module,   array $params = null) {
        parent::__construct($name, $module,  $params);
        if(!($this->isEditable = $this->document->isEditable()) && ($this->getState() == self::DEFAULT_STATE_NAME)){
           $this->disable();
        }
        else {
            $this->setProperty('exttype', 'feededitor');
            if(!in_array($this->getState(),  array('up', 'down')) && isset($_COOKIE[md5($this->getName())])){
                E()->getResponse()->deleteCookie(md5($this->getName()));
            }
        }
    }

    /**
     * @copydoc Grid::defineParams
     */
    // Добавляем параметр  - имя связанного компонента
    protected function defineParams() {
        return array_merge(
        parent::defineParams(),
        array(
        'bind' => false
        )
        );
    }    
    /**
     * @copydoc Grid::main
     */
    // Убираем все лишнее
    protected function main() {
        if($this->getFilter())
            E()->getResponse()->addCookie(md5($this->getName()), convert_uuencode($this->document->componentManager->getBlockByName(
                   $this->getParam('bind')
                )->getFilter()));
        $this->addToolbar($this->createToolbar());
        $this->js = $this->buildJS();
    }

     /**
      * @copydoc Grid::changeOrder
      */
    // Поскольку у нас список выводится в другом компоненте(Feed), то для того чтобы подхватить фильтры, наложенные в нем, мы записываем их в куки  - а здесь читаем
    protected function changeOrder($direction){
        if(isset($_COOKIE[md5($this->getName())])){
            $this->setFilter(convert_uudecode($_COOKIE[md5($this->getName())]));
            E()->getResponse()->deleteCookie(md5($this->getName()));
        }
        return parent::changeOrder($direction);
    }

     /**
      * @copydoc Grid::build
      */
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