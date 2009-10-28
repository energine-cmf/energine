<?php

/**
 * Содержит класс TemplateEditor
 *
 * @package energine
 * @subpackage share
 * @author dr.Pavka
 * @copyright Energine 2006
 * @version $Id$
 */


/**
 * Редактор шаблонов
 *
 * @package energine
 * @subpackage share
 * @author dr.Pavka
 */
class TemplateEditor extends Grid {
    /**
     * Конструктор класса
     *
     * @return void
     */
    public function __construct($name, $module, Document $document,  array $params = null) {
        parent::__construct($name, $module, $document,  $params);
        $this->setTableName('share_templates');
        $this->setTitle($this->translate('TXT_TEMPLATE_EDITOR'));
        $this->setOrderColumn('tmpl_order_num');
        $this->setOrder(array('tmpl_order_num' =>QAL::ASC));
    }
    
    protected function createDataDescription(){
        $result = parent::createDataDescription();
        if($iconField = $result->getFieldDescriptionByName('tmpl_icon')){
	        $iconField->setType(FieldDescription::FIELD_TYPE_IMAGE);
        	if(in_array($this->getAction(), array('add', 'edit'))){
	            $iconField->setType(FieldDescription::FIELD_TYPE_SELECT);
                $iconField->loadAvailableValues(
                $this->loadIconsData(),            
                'key','value');	                    	
	        }
        }
        
        /*if(
            $previewField = $result->getFieldDescriptionByName('tmpl_preview')
            &&
            in_array($this->getAction(), array('add', 'edit'))
        ){
        	   inspect($previewField);
                $previewField->setType(FieldDescription::FIELD_TYPE_SELECT);
                $previewField->loadAvailableValues(
                $this->loadPreviewData(),            
                'key','value');                         
        }*/
        return $result;
    }
    private function loadIconsData(){
    	$result = array();
        foreach(glob("templates/icons/*.icon.gif") as $path){
        	$result[] = array(
        	   'key' => $path,
        	   'value' => basename($path) 
        	);
        }
        
    	return $result;
    }
    
    private function loadPreviewData(){
        $result = array();
        foreach(glob("templates/icons/*.preview.gif") as $path){
            $result[] = array(
               'key' => $path,
               'value' => basename($path) 
            );
        }
        
        return $result;
    }
    protected function createData(){
    	$result = parent::createData();
    	return $result;
    }

    protected function edit(){
    	parent::edit();

    	$field = $this->getData()->getFieldByName('tmpl_content');
		$field->setRowData(0,
			str_replace('.content.xml', '', $field->getRowData(0))
		);

		$field = $this->getData()->getFieldByName('tmpl_layout');
		$field->setRowData(0,
			str_replace('.layout.xml', '', $field->getRowData(0))
		);
    }

    protected function saveData(){
		$_POST[$this->getTableName()]['tmpl_content'] .= '.content.xml';
		$_POST[$this->getTableName()]['tmpl_layout'] .= '.layout.xml';

		parent::saveData();
    }
}
