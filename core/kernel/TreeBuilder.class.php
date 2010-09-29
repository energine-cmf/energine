<?php

/**
 * Класс TreeBuilder.
 *
 * @package energine
 * @subpackage core
 * @author dr.Pavka
 * @copyright Energine 2006
 * @version $Id$
 */

/**
 * Построитель древовидных данных.
 * Кроме Data и DataDescription имеет еще и Tree c помощью которого определяется структура
 *
 * @package energine
 * @subpackage core
 * @author dr.Pavka
 */
class TreeBuilder extends AbstractBuilder  {
    /**
     * Имя поля содержащего ключевой идентификатор
     *
     * @var string
     * @access private
     */
    private $idFieldName = false;
    /**
     * Дерево
     *
     * @var TreeNodeList
     * @access private
     */
    private $tree;

    /**
     * Конструктор класса.
     *
     * @access public
     * @return void
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Устанавливает дерево
     *
     * @param TreeNodeList
     * @return void
     * @access public
     */

    public function setTree(TreeNodeList $tree) {
        $this->tree = $tree;
    }

    /**
	 * Построение результата.
	 *
	 * @access protected
	 * @return void
	 */
    protected function run() {
        foreach ($this->dataDescription as $fieldName => $fieldDescription) {
            if (!is_null($fieldDescription->getPropertyValue('key'))) {
            	$this->idFieldName = $fieldName;
            }
        }
        if (!$this->idFieldName) {
        	throw new SystemException('ERR_DEV_NO_TREE_IDENT', SystemException::ERR_DEVELOPER, array($this->idFieldName));
        }
        $this->result->appendChild($this->treeBuild($this->tree));
    }

    /**
     * Внутренний метод постройки древовидного XML
     *
     * @return DOMNode
     * @access private
     */

    private function treeBuild(TreeNodeList $tree) {
        $dom_recordset = $this->result->createElement('recordset');
        $data = array_flip($this->data->getFieldByName($this->idFieldName)->getData());
        foreach ($tree as $id => $node) {
        	if(isset($data[$id])){
            //Идентификатор строки
            $num = $data[$id];
            $dom_record = $this->result->createElement('record');
            foreach ($this->dataDescription as $fieldName => $fieldDescription) {
                $fieldProperties = array();
                $fieldValue = '';

                if($f = $this->data->getFieldByName($fieldName)){
                    $fieldValue = $this->data->getFieldByName($fieldName)->getRowData($num);
                    $fieldProperties = $this->data->getFieldByName($fieldName)->getRowProperties($num);
                    if ($fieldDescription->getType() == FieldDescription::FIELD_TYPE_SELECT ) {
                	    $fieldValue = $this->createOptions($fieldDescription, array($fieldValue));
                    }
                }
            	$dom_field = $this->createField($fieldName, $fieldDescription, $fieldValue, $fieldProperties);
            	$dom_record->appendChild($dom_field);
            }
        	$dom_recordset->appendChild($dom_record);
            if ($node->hasChildren()) {
        		$dom_record->appendChild($this->treeBuild($node->getChildren()));
        	}
        	}

        }
        return $dom_recordset;
    }
}
