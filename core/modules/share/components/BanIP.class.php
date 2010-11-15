<?php
/**
 * Содержит класс BanIP
 *
 * @package energine
 * @subpackage share
 * @author spacelord
 * @copyright Energine 2010
 */

/**
 *
 *
 * @package energine
 * @subpackage share
 * @author spacelord
 * @final
 */
final class BanIP extends DBWorker {

    public function __construct() {
        parent::__construct();
	}

    public function isBannedIP($ip = false){
        if(!$ip){
            $ip = Request::getInstance()->getClientIP();
        }
        $result = false;
        $result = simplifyDBResult(
            $this->dbh->selectRequest('SELECT ban_ip_id FROM share_ban_ips WHERE ban_ip=%s',$ip),
            'ban_ip_id',
            true
        );
        return $result;
   }
}