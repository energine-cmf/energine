<?php
/**
 * Содержит класс ChildDivisions
 *
 * @package energine
 * @subpackage share
 * @author dr.Pavka
 * @copyright ColoCall 2006
 * @version $Id$
 */



/**
 * Класс передназначен для вівода дочерних разделов текущего раздела
 *
 * @package energine
 * @subpackage share
 * @author dr.Pavka
 *
 */
class ChildDivisions extends DataSet  {
    /**
     * Переменная содержащая идентификатор раздела для которого нужно выводить потомков
     *
     * @var int
     * @access private
     */
    private $id;

    /**
     * Идентификатор указывающий на то что нужно исепользовать в качестве идентфикатора id родительской страницы
     *
     */
    const PARENT_ID = 'parent';

    /**
     * Конструктор класса
     *
     * @return void
     */
    public function __construct($name, $module, Document $document,  array $params = null) {
        parent::__construct($name, $module, $document,  $params);
        $this->setType(self::COMPONENT_TYPE_LIST);
        if ($this->getParam('id')) {
        	$this->setParam('active', false);
        	//$this->setParam('recordsPerPage', false);
        }

        if($this->getParam('id') == self::PARENT_ID){
            $this->id = Sitemap::getInstance()->getParent($this->document->getID());
        }
        elseif (!$this->getParam('id')) {
            $this->id = $this->document->getID();
        }
        else {
            $this->id = $this->getParam('id');
        }
    }

    /**
     * Возвращает значение id
     *
     * @return int
     * @access protected
     * @final
     */
     final protected function getID() {
        return $this->id;
     }

     /**
      * Устанавливает id
      *
      * @return void
      * @access protected
      * @final
      */
      final protected function setID($id) {
         $this->id = $id;
      }

    /**
	 * Добавлен параметр id - идентификатор страницы
	 *
	 * @return int
	 * @access protected
	 */

    protected function defineParams() {
        $result = array_merge(parent::defineParams(),
        array(
        'id'=>false,
        'showFinal' => false,
        'active' => true
        ));
        return $result;
    }

    /**
     * Устанавливаем перечень полей
     *
     * @return DataDescription
     * @access protected
     */

     protected function createDataDescription() {
        $result = new DataDescription();

        $field = new FieldDescription('Id');
        $field->setType(FieldDescription::FIELD_TYPE_INT);
        $field->addProperty('key', true);
        $result->addFieldDescription($field);

        $field = new FieldDescription('Name');
        $field->setType(FieldDescription::FIELD_TYPE_STRING);
        $result->addFieldDescription($field);

        $field = new FieldDescription('Segment');
        $field->setType(FieldDescription::FIELD_TYPE_STRING);
        $result->addFieldDescription($field);

        $field = new FieldDescription('DescriptionRtf');
        $field->setType(FieldDescription::FIELD_TYPE_TEXT);
        $result->addFieldDescription($field);

        return $result;
     }
    /**
	 * Переопределенный метод загрузки данных
	 *
	 * @return mixed
	 * @access protected
	 */

    protected function loadData() {
        $data = Sitemap::getInstance()->getChilds($this->getID());
        $data = (empty($data))?false:$data;
        if(is_array($data)) {
            if ($this->getParam('recordsPerPage')) {
                if ($this->pager->getCurrentPage()>1) {
                    $this->document->componentManager->getComponentByName('breadCrumbs')->addCrumb();
                }
                $this->pager->setRecordsCount(sizeof($data));
                $limit = $this->pager->getLimit();
                $data = array_slice($data, $limit[0], $limit[1], true);
            }
            foreach ($data as $id => $current) {
                $data[$id] = array(
                'Id' => $id,
                'Segment' => $current['Segment'],
                'Name' => $current['Name'],
                'DescriptionRtf' => $current['DescriptionRtf']
                );
            }

        }

        return $data;
    }
}
