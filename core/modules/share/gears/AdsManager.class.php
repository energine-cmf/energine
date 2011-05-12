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

    const TABLE_NAME = 'share_ads';

    public function __construct() {
        parent::__construct();
    }

    public function add($dd){
        $fds = $this->dbh->getColumnsInfo(self::TABLE_NAME);
        unset($fds['smap_id']);
        unset($fds['ad_id']);
        foreach ($fds as $key => $value)
            $fds[$key]['tabName'] = 'TXT_ADS';
        $dd->load($fds);
    }

    public function edit($d, $dd){
        $fds = $this->dbh->getColumnsInfo(self::TABLE_NAME);
        unset($fds['smap_id']);
        $fds['ad_id']['key'] = false;
        foreach ($fds as $key => $value)
            $fds[$key]['tabName'] = 'TXT_ADS';
        $dd->load($fds);

        $data = $this->dbh->select(self::TABLE_NAME, array_keys($fds), array('smap_id' => $d->getFieldByName('smap_id')->getRowData(0)));
        if(is_array($data))
            $d->load($data);
    }

    public function save($data){
        $result = false;
        if(isset($data['ad_id']) && intval($adID = $data['ad_id'])){
            unset($data['ad_id']);
            $result = $this->dbh->modify(QAL::UPDATE, self::TABLE_NAME, $data, array('ad_id' => $adID));
        } else {
            $result = $this->dbh->modify(QAL::INSERT, self::TABLE_NAME, $data);
        }
        return $result;
    }

    public function isActive(){
        if($this->dbh->tableExists(self::TABLE_NAME))
            return true;
        return false;
    }
}
