<?php
/**
 * Содержит класс CurrencyConverter
 *
 * @package energine
 * @subpackage shop
 * @author Andrii A
 * @copyright eggmengroup.com
 */
/**
 * Конвертер валют
 * @TODO: пока в классе просто намечены основые метода, в будущем его необходимо доработать
 *
 * @package energine
 * @subpackage shop
 * @author Andrii A
 */
class CurrencyConverter extends DBWorker {
    /**
     * Инстанс класса
     *
     * @var CurrencyConverter
     * @access private
     * @static
     */
    static private $instance;

    /**
     * Кэш валют
     *
     * @var array
     * @access private
     */
    private $currencies;

    /**
     *
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Возвращает Instance объекта
     *
     * @access public
     * @return CurrencyConverter
     * @static
     */
    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new CurrencyConverter();
        }
        return self::$instance;
    }

    /**
     * Возвращает идентификатор валюты по умолчанию
     *
     * @return int
     * @access public
     */
    public function getDefault() {

    }

    /**
     * Возвращает курс для переданного идентификатора валюты
     *
     * @param int $currID
     * @return int
     * @access public
     */

    public function getRate($currID) {
        return $this->currencies[$currID]['Rate'];
    }

    /**
     * Конвертирует цену из одной валюты в другую
     * @param $value
     * @param $currID
     * @param bool $currIDFrom
     * @return float
     * @throws SystemException
     */
    public function convert($value, $currID, $currIDFrom = false) {
        if (!$currIDFrom) {
            $currIDFrom = $this->getDefault();
        }

        if (!isset($this->currencies[$currID]) || !isset($this->currencies[$currIDFrom])) {
            throw new SystemException('ERR_DEV_BAD_CURR_ID', SystemException::ERR_CRITICAL);
        }
        if ($currID != $currIDFrom) {
            $rate = $this->currencies[$currIDFrom]['Rate']/$this->currencies[$currID]['Rate'];
            $result = $value/$rate;
        }
        else {
        	$result = $value;
        }

        return round($result, 2);
    }

    /**
     * Форматирует переданное в параметре значение в формат валюты
     *
     * @param float
     * @param int currID
     * @return string
     * @access public
     */

    public function format($value, $currID) {
        return sprintf($this->currencies[$currID]['Format'], round($value, 2));
    }

    /**
     * Возвращает идентификатор валюты по переданной аббревиатуре
     *
     * @param string
     * @return int
     * @access public
     */

    public function getIDByAbbr($abbr) {
        foreach ($this->currencies as $id => $info) {
            if ($info['Abbr'] == $abbr) {
                return $id;
            }
        }
        return $this->getDefault();
    }

    /**
     * Устанавливает текущюю валюту
     *
     * @param $currencyID
     * @throws SystemException
     */
    public function setCurrent($currencyID) {
        if (!isset($this->currencies[$currencyID])) {
        	throw new SystemException('ERR_NO_CURRENCY', SystemException::ERR_CRITICAL, $currencyID);
        }
        $_SESSION['current_currency'] = $currencyID;
    }

    /**
     * Возвращает идентификатор текущей используемой валюты
     *
     * @return int
     * @access public
     */
    public function getCurrent() {
        if (isset($_SESSION['current_currency'])) {
            $result = $_SESSION['current_currency'];
        }
        else {
            $result = $this->getDefault();
            $this->setCurrent($result);
        }

        return $result;
    }

    /**
     * Возвращает список валют
     *
     * @return array
     * @access public
     */

    public function getCurrencies() {
        return $this->currencies;
    }
}