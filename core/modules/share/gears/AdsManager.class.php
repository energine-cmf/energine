<?php
/**
 * Содержит класс AdsManager
 *
 * @package energine
 * @subpackage share
 * @author spacelord
 * @copyright spacelord.5@gmail.com
 */

/**
 *
 * @package energine
 * @subpackage share
 * @author spacelord.5@gmail.com
 */
class AdsManager extends DBWorker {

    private $tableName = 'share_ads';
    private $componentAction;
    private $columnsInfo;
    private $columns;

    public function __construct($smapID, $componentAction = 'edit') {
        parent::__construct();
        $this->componentAction = $componentAction;
        $this->loadColumnsInfo();
        $this->smapID = $smapID;
    }


    public function save(){
        if(isset($_POST['componentAction']) && isset($_POST['share_sitemap']) && isset($_POST[$this->tableName])){
            $this->componentAction = $_POST['componentAction'];
            $ads = $_POST[$this->tableName];
            $ads['smap_id'] = $this->smapID;
            switch($this->componentAction){
                case 'add':
                    $result = $this->dbh->modify(QAL::INSERT,$this->tableName,$ads);
                    break;
                case 'edit':
                    break;
                default:
                    break;
            }
            return $result;
        }
    }

    public function getFieldsDescriptions(){
        $fds = $this->columnsInfo;
        foreach ($fds as $key => $value) {
            $fds[$key]['tabName'] = 'TXT_ADS';
            if (in_array($key, array('ad_id')))
                $fds[$key]['key'] = false;
        }
        $dd = new DataDescription();
        $dd->load($fds);
        return $dd;
    }

    public function getFields(){
        if ($this->componentAction == 'edit') {
            $fields = $this->columns;
            $result = $this->dbh->select($this->tableName, $fields, array('smap_id' => $this->smapID));
            return $result[0];
        }
    }

    private function loadColumnsInfo(){
        $this->columnsInfo = $this->dbh->getColumnsInfo($this->tableName);
        unset($this->columnsInfo['smap_id']);
        if($this->componentAction=='add')
            unset($this->columnsInfo['ad_id']);
        $this->columns = array_keys($this->columnsInfo);
    }
}
