<?php
class BanIPSaver extends Saver{
    const TABLE_NAME = 'user_ban_ips';

    public function getDataDescription(){
        $result = parent::getDataDescription();
        if($IPField = $result->getFieldDescriptionByName('ban_ip')){
            $IPField->setType(FieldDescription::FIELD_TYPE_INT);
            $IPField->removeProperty('pattern');
            $IPField->setLength(10);

        }
        return $result;
    }
    public function setData(Data $data){
        if(!$data->isEmpty()){
            $IPField = $data->getFieldByName('ban_ip');
            $IPField->setRowData(0, ip2long($IPField->getRowData(0)));
        }
        parent::setData($data);
    }
}