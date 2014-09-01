<?php
/**
 * @file
 * JSONDivBuilder
 *
 * It contains the definition to:
 * @code
class JSONDivBuilder;
@endcode
 *
 * @author d.pavka
 * @copyright d.pavka@gmail.com
 *
 * @version 1.0.0
 */
namespace Energine\share\gears;
/**
 * JSON builder for division editor.
 *
 * @code
class JSONDivBuilder;
@endcode
 */
class JSONDivBuilder extends JSONBuilder {
    /**
     * Document ID.
     * @var int $documentId
     */
    private $documentId;

    /**
     * Set document ID.
     * @param int $id ID
     */
    public function setDocumentId($id){
    	$this->documentId = $id;
    }
    
    public function getResult(){
    	$this->result['current'] = $this->documentId;
    	$result = parent::getResult();
    	return $result;
    }
}