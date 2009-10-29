<?php
/**
 * Содержит класс FeedEditor
 *
 * @package energine
 * @subpackage share
 * @author dr.Pavka
 * @copyright Energine 2007
 * @version $Id$
 */

/**
 * Класс для построения редакторов управляющихся из панели управления
 *
 * @package energine
 * @subpackage share
 * @author dr.Pavka
 */
class FeedEditor extends Grid {
    /**
     * Включен ли режим редактирования
     *
     * @var boolean
     * @access private
     */
    private $isEditable;

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
     * Для форм поле smap_id віводим как string
     *
     * @return DataDescription
     * @access protected
     */

    protected function createDataDescription() {
        $result = parent::createDataDescription();
        if (in_array($this->getType(), array(self::COMPONENT_TYPE_FORM_ADD, self::COMPONENT_TYPE_FORM_ALTER))) {
            $field = $result->getFieldDescriptionByName('smap_id');
            $field->setType(FieldDescription::FIELD_TYPE_STRING);
            $field->setMode(FieldDescription::FIELD_MODE_READ);
        }
        return $result;
    }

    /**
     * Определяем данные для smap_id
     *
     * @return Data
     * @access protected
     */

     protected function createData() {

        $result = parent::createData();
        if (in_array($this->getType(), array(self::COMPONENT_TYPE_FORM_ADD, self::COMPONENT_TYPE_FORM_ALTER))) {
            $info = Sitemap::getInstance()->getDocumentInfo($this->document->getID());
            $field = $result->getFieldByName('smap_id');
            for($i=0; $i<sizeof(Language::getInstance()->getLanguages()); $i++) {
                $field->setRowProperty($i, 'segment', Sitemap::getInstance()->getURLByID($this->document->getID()));
                $field->setRowData($i, $info['Name']);
            }
        }
        return $result;
     }

    /**
     * Убираем все лишнее
     *
     * @return void
     * @access protected
     */

    protected function main() {
    	$_SESSION['feed_smap_id'] =
	    	$this->document->componentManager->getComponentByName(
		   			$this->getParam('linkTo')
		   	)->getFilter();

        if ($toolbar = $this->createToolbar()) {
            $this->setToolbar($toolbar);
        }
        $this->js = $this->buildJS();
    }

    protected function changeOrder($direction){
	   	$this->setFilter($_SESSION['feed_smap_id']);
		//unset($_SESSION['feed_smap_id']);

	   	return parent::changeOrder($direction);
    }

    /**
      * Для метода main убираем вызов построителя
      *
      * @return DOMDocument
      * @access public
      */

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
                if (($toolbar = $this->getToolbar()) && ($toolbar = $toolbar->build())) {
                    $toolbar = $result->importNode($toolbar, true);
                    $result->documentElement->appendChild($toolbar);
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


    /**
     * Выставляем smap_id в текущее значение
     *
     * @return mixed
     * @access protected
     */

     protected function saveData() {
        $_POST[$this->getTableName()]['smap_id'] = $this->document->getID();
        $result = parent::saveData();
        return $result;
     }

}