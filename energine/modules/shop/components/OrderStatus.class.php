<?php
/**
 * Содержит класс OrderStatus
 *
 * @package energine
 * @subpackage shop
 * @author dr.Pavka
 * @copyright ColoCall 2006
 * @version $Id$
 */

//require_once('core/framework/DBWorker.class.php');

/**
 * Класс предназначенный для работы со статусами
 *
 * @package energine
 * @subpackage shop
 * @author dr.Pavka
 */
class OrderStatus extends DBWorker {
    /**
     * @access private
     * @static
     * @var OrderStatus единый для всей системы экземпляр класса OrderStatus
     */
    private static $instance;

    /**
     * Перечень статусов
     *
     * @var array
     * @access private
     */
    private $statuses;
    /**
     * Конструктор класса
     *
     * @access public
     */
	public function __construct() {
        parent::__construct();
        $this->statuses = convertDBResult(
            $this->dbh->selectRequest(
                'SELECT main.os_id, trans.os_name FROM shop_order_statuses main '.
                'LEFT JOIN shop_order_statuses_translation trans ON trans.os_id = main.os_id '.
                'WHERE trans.lang_id = %s '.
                'ORDER BY os_priority',
                Language::getInstance()->getCurrent()
            ),
            'os_id', true);
        if (!$this->statuses) {
        	throw new SystemException('ERR_NO_ORDER_STATUS_DEFINED', SystemException::ERR_CRITICAL);
        }
	}

    /**
     * Возвращает единый для всей системы экземпляр класса OrderStatus.
     *
     * @access public
     * @static
     * @return OrderStatus
     */
    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new OrderStatus();
        }
        return self::$instance;
    }

	/**
	 * Возвращает минимальный статус
	 *
	 * @return int идентификатор статуса
	 * @access public
	 */

	public function getInitial() {
        return key($this->statuses);
	}

	/**
	 * Возвращает имя статуса
	 *
	 * @param int идентификатор статуса
	 * @return string
	 * @access public
	 */

	public function getName($statusID) {
        return $this->statuses[$statusID];
	}
}
