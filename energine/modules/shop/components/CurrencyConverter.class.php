<?php
/**
 * Содержит класс CurrencyConverter
 *
 * @package energine
 * @subpackage shop
 * @author dr.Pavka
 * @copyright ColoCall 2006
 * @version $Id$
 */

//require_once('core/framework/DBWorker.class.php');

/**
 * Конвертер валют
 *
 * @package energine
 * @subpackage shop
 * @author dr.Pavka
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
     * Имя таблицы
     *
     * @var string
     * @access private
     */
    private $tableName;

    /**
     * Имя таблицы переводов
     *
     * @var string
     * @access private
     */
    private $transTableName;

    /**
     * Кэш валют
     *
     * @var array
     * @access private
     */
    private $currencies;

    /**
     * Конструктор класса
     *
     * @return void
     */
    public function __construct() {
        parent::__construct();
        $this->tableName = 'shop_currency';
        $this->transTableName = 'shop_currency_translation';
        $this->currencies = $this->dbh->selectRequest(
        'SELECT * FROM '.$this->tableName.' main '.
        'LEFT JOIN '.$this->transTableName.' trans ON trans.curr_id = main.curr_id '.
        'WHERE trans.lang_id = %s',
        Language::getInstance()->getCurrent()
        );
        $this->currencies = convertDBResult($this->currencies, 'curr_id', true);
        $this->currencies = array_map(create_function('$currInfo', 'return convertFieldNames($currInfo, "curr");'),$this->currencies);
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
        foreach ($this->currencies as $currID => $currencyInfo) {
            if ($currencyInfo['Rate'] == 1) {
                return $currID;
            }
        }
    }

    /**
     * Возвращает курс для переданного идентификатора валюты
     *
     * @return int
     * @access public
     */

    public function getRate($currID) {
        return $this->currencies[$currID]['Rate'];
    }

    /**
     * Конвертирует из одной валюты в другую
     *
     * @param currencyValue float
     * @param currencyID int идентификатор валюты в которую нужно произвести конвертацию
     * @param currIDFrom int идентифкатор валюты которую нужно конвертировать
     * @return float
     * @access public
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
     * Устанавливает идентификатор текущей валюты
     *
     * @return void
     * @access public
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
     * Возвращает список идентификаторов валют
     *
     * @return array
     * @access public
     */

    public function getCurrencies() {
        return array_keys($this->currencies);
    }
}