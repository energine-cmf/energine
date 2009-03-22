<?php
/**
 * Sape Client
 *
 * @package energine
 * @subpackage incubator/additional
 * @author dr.Pavka
 */
class Sape extends Component {
   
    /**
     * Конструктор класса
     *
     * @return void
     */
    public function __construct($name, $module, Document $document,  array $params = null) {
        parent::__construct($name, $module, $document,  $params);
    }
    
    protected function defineParams() {
        return array_merge(
            parent::defineParams(),
            array(
            'links' => '',
            'code' => 'e37aa5ee5da182b23876ce80785cd92a'
            )
        );
    }
    
    public function getClient(){
        if (!isset($GLOBALS['sape'])) {
        	if (!defined('_SAPE_USER')){
                 define('_SAPE_USER', $this->getParam('code'));
            }
            require_once($_SERVER['DOCUMENT_ROOT'].'/'._SAPE_USER.'/sape.php');
          
            $GLOBALS['sape'] = new SAPE_client(
                array(
                	'charset' => 'UTF-8',
                	'force_show_code' => true
                )
            );
        }
      
        return $GLOBALS['sape'];
    }
    
    public function build(){
        $this->setBuilder($builder = new SimpleBuilder());
        $fd = new FieldDescription('links');
        $fd->setType(FieldDescription::FIELD_TYPE_CUSTOM);
        
        $f = new Field('links');
        $f->setData($this->getClient()->return_links($this->getParam('links')));
        
        $builder->setDataDescription($dd = new DataDescription());
        $dd->addFieldDescription($fd);
        
        $builder->setData($d = new Data());
        $d->addField($f);
        
        return parent::build();
    }
    
}