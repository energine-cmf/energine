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
	const TMPL_CONTENT = 'content';
	const TMPL_LAYOUT = 'layout';
	
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
        
        if($f = $result->getFieldDescriptionByName('tmpl_icon')){
	        $f->setType(FieldDescription::FIELD_TYPE_IMAGE);
        	if(in_array($this->getAction(), array('add', 'edit'))){
	            $f->setType(FieldDescription::FIELD_TYPE_SELECT);
                $f->loadAvailableValues(
                $this->loadIconsData(),            
                'key','value');	                    	
	        }
        }
        
        foreach (array(self::TMPL_CONTENT, self::TMPL_LAYOUT) as $type)
	        if(($f = $result->getFieldDescriptionByName('tmpl_'.$type)) && in_array($this->getAction(), array('add', 'edit'))){
		        $f->setType(FieldDescription::FIELD_TYPE_SELECT);
		        $f->loadAvailableValues(
		        $this->loadTemplateData($type),            
		        'key','value');                         
	        }
        
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
    
    private function loadTemplateData($type){
        $result = array();
        foreach(glob("templates/".$type."/*.".$type.".xml") as $path){
        	$path = basename($path);
            $result[] = array(
               'key' => $path,
               'value' => $path 
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
}
