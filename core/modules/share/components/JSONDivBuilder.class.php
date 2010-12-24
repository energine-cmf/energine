<?php
/**
 * Содержит класс JSONDivBuilder
 *
 * @package energine
 * @subpackage share
 * @author d.pavka
 * @copyright d.pavka@gmail.com
 */

 /**
  * Модифицированный постороитель для редактора разделов
  *
  * @package energine
  * @subpackage share
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