<?php
/**
 * Sape Client
 *
 * @package energine
 * @subpackage incubator/additional
 * @author dr.Pavka
 */
class Sape extends Component {
    private $sapeClient;
    
    /**
     * Конструктор класса
     *
     * @return void
     */
    public function __construct($name, $module, Document $document,  array $params = null) {
        parent::__construct($name, $module, $document,  $params);
        $previousSAPEComponents = $this->document->componentManager->getComponentsByClassName('Sape');
        if (empty($previousSAPEComponents)) {
        	if (!defined('_SAPE_USER')){
                 define('_SAPE_USER', 'e37aa5ee5da182b23876ce80785cd92a');
            }
            
            require_once($_SERVER['DOCUMENT_ROOT'].'/'._SAPE_USER.'/sape.php');
          
            $this->sapeClient = new SAPE_client(array('charset' => 'UTF-8'));
            inspect($previousSAPEComponents);
        }
        inspect($previousSAPEComponents);        
      
    }
    
    protected function defineParams() {
        return array_merge(
            parent::defineParams(),
            array(
            'links' => ''
            )
        );
    }
    
    public function getClient(){
        return $this->sapeClient;
    }
    
}