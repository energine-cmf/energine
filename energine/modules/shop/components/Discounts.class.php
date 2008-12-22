<?php
/**
 * Содержит класс Discounts
 *
 * @package energine
 * @subpackage shop
 * @author 1m.dm
 * @copyright ColoCall 2006
 * @version $Id$
 */

//require_once('core/framework/DBWorker.class.php');
//require_once('core/framework/AuthUser.class.php');

/**
 * Скидочки ))
 *
 * @package energine
 * @subpackage shop
 * @author 1m.dm
 */
class Discounts extends DBWorker {

    /**
     * @access private
     * @static
     * @var Discounts единый для всей системы экземпляр класса Discounts
     */
    private static $instance;

    /**
     * @access private
     * @var string имя таблицы скидок в БД
     */
    private $tableName;

    /**
     * @access private
     * @var int идентификатор текущей группы пользователей
     */
    private $currentGroupId;

    /**
     * @access private
     * @var int процент скидки для текущей группы
     */
    private $currentGroupDiscount;

    /**
     * Конструктор класса.
     *
     * @access public
     * @return void
     */
    public function __construct() {
        parent::__construct();
        $this->tableName = 'shop_discounts';
        $this->setCurrentGroup($this->getDefaultGroup());
    }

    /**
     * Возвращает единый для всей системы экземпляр класса Discount.
     *
     * @access public
     * @static
     * @return Basket
     */
    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new Discounts;
        }
        return self::$instance;
    }

    /**
     * Возвращает имя таблицы скидок.
     *
     * @access protected
     * @return string
     */
    protected function getTableName() {
        return $this->tableName;
    }

    /**
     * Устанавливает текущую группу, которая используется системой скидок для
     * расчетов окончательной стоимости.
     *
     * @access public
     * @param int $groupId
     * @return void
     */
    public function setCurrentGroup($groupId) {
        $this->currentGroupId = intval($groupId);
        $this->currentGroupDiscount = $this->getDiscountForGroup($this->currentGroupId);
    }

    /**
     * Возвращает процент скидки для указанной группы.
     * Если группа не указана - используется группа по-умолчанию.
     * Если скидка для группы не определена - возвращается 0;
     *
     * @access public
     * @param int $groupId
     * @return int
     */
    public function getDiscountForGroup($groupId = null) {
        if (!isset($groupId)) {
            $groupId = $this->currentGroupId;
        }

        $result = $this->dbh->select($this->tableName, 'dscnt_percent', array('group_id' => intval($groupId)));
        if (is_array($result)) {
            return intval($result[0]['dscnt_percent']);
        }
        return 0;
    }

    /**
     * Возвращает группу по-умолчанию для текущего пользователя.
     * Группой по-умолчанию считается такая группа пользователя,
     * для которой установлен наибольший размер скидки.
     *
     * @access public
     * @return int
     */
    public function getDefaultGroup() {
        $authUser = AuthUser::getInstance();
        $userGroups = $authUser->getGroups();
        $discounts = $this->dbh->select($this->tableName, array('group_id', 'dscnt_percent'));
        $discounts = convertDBResult($discounts, 'group_id', true);
        // Группа с наибольшей скидкой используется по-умолчанию.
        $maxDiscount = 0;
        $defaultGroup = 0;
        foreach ($userGroups as $groupId) {
            if (isset($discounts[$groupId])) {
                $discount = intval($discounts[$groupId]['dscnt_percent']);
                if ($discount > $maxDiscount) {
                    $maxDiscount = $discount;
                    $defaultGroup = $groupId;
                }
            }
        }
        return $defaultGroup;
    }

    /**
     * Расчитывает окончательную стоимость для указанной цены,
     * основываясь на проценте скидки для текущей группы.
     *
     * @access public
     * @param float $price
     * @return float
     */
    public function calculateCost($price) {
        $price = floatval($price);
        $cost = $price * (1 - $this->currentGroupDiscount / 100);
        return $cost;
    }
}