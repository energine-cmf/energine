<?php
/**
 * Содержит класс CLASS_NAME
 *
 * @package energine
 * @subpackage MODULE_NAME
 * @author d.pavka
 * @copyright d.pavka@gmail.com
 */

 /**
  * CLASS_DESC
  *
  * @package energine
  * @subpackage MODULE_NAME
  * @author d.pavka@gmail.com
  */
 class JSONDivBuilder extends JSONBuilder {
 	private $documentId;
    /**
     * Конструктор класса
     *
     * @access public
     */
    public function __construct() {
        parent::__construct();
    }
    
    public function setDocumentId($id){
    	$this->documentId = $id;
    }
    
    public function getResult(){
    	$this->result['current'] = $this->documentId;
    	$result = parent::getResult();
    	return $result;
    }
}