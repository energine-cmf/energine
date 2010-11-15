<?php
class BanIPSaver extends Saver{
    /**
     * Конструктор класса
     *
     * @param string $name
     * @param string $module
     * @param Document $document
     * @param array $params
     * @access public
     */
	public function __construct() {
        parent::__construct();
	}

	public function save(){
        $result = false;
		if(isset($_POST['share_ban_ips']) && !empty($_POST['share_ban_ips'])) {
            switch($this->getMode()){
                case QAL::INSERT:
                    $result = $this->dbh->modify(
                        QAL::INSERT,
                        'share_ban_ips',
                        array('ban_ip_id' => $this->getResult(),
                              'ban_ip' => $_POST['share_ban_ips']['ban_ip'],
                              'ban_ip_end_date' => $_POST['share_ban_ips']['ban_ip_end_date']));
                    break;
                case QAL::UPDATE:
                    $result = $this->dbh->modify(
                        QAL::UPDATE,
                        'share_ban_ips',
                        array('ban_ip' => $_POST['share_ban_ips']['ban_ip'],
                              'ban_ip_end_date' => $_POST['share_ban_ips']['ban_ip_end_date']),
                        'ban_ip_id = '.intval($this->getData()->getFieldByName('ban_ip_id')->getRowData(0)));
                    break;
                default:
                    break;
            }
		}
        return $result;
	}
}