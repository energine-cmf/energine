<?php
/**
 * Содержит класс BanIPEditor
 *
 * @package energine
 * @subpackage share
 * @author spacelord
 * @copyright Energine 2010
 * @version $Id:
 */


/**
 *
 * @package energine
 * @subpackage share
 * @author dr.Pavka
 */
class BanIPEditor extends Grid {
    /**
     * Конструктор класса
     *
     * @param string $name
     * @param string $module

     * @param array $params
     * @access public
     */
	public function __construct($name, $module,   array $params = null) {
        parent::__construct($name, $module,  $params);
        $this->setTableName('share_ban_ips');
        $this->setOrder(array('ban_ip_end_date'=> QAL::ASC));
        $this->setSaver(new BanIPSaver());
	}

    protected function add(){
        parent::add();
        $this->getDataDescription()->getFieldDescriptionByName('ban_ip_end_date')->setType(FieldDescription::FIELD_TYPE_SELECT);
        $fdValues = BanDateTransform::getFormattedFDValues();
        foreach($fd = $this->getDataDescription()->getFieldDescriptionByName('ban_ip_end_date') as $key=>$value){
            $fd = $this->getDataDescription()->getFieldDescriptionByName('ban_ip_end_date');
            $fd->loadAvailableValues($fdValues,'date_key','date_value');
        }
    }

    protected function save(){
        $this->setBuilder(new JSONBuilder());
        $this->setData(new Data());
        $this->setDataDescription(new DataDescription());
        $result = false;
        $banDate = BanDateTransform::getFormattedBanDate($_POST['share_ban_ips']['ban_ip_end_date']);
        if($banDate)
            $_POST['share_ban_ips']['ban_ip_end_date'] = $banDate;
        $result = $this->saveData();

        $JSONResponse = array(
            'data'=>json_encode($_POST['share_ban_ips']),
            'result' => true,
            'mode' => 'save'
        );
        $this->response->setHeader('Content-Type', 'text/javascript; charset=utf-8');
        $this->response->write(json_encode($JSONResponse));
        $this->response->commit();
    }



}