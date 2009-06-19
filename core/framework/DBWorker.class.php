<?php

/**
 * Класс DBWorker.
 *
 * @package energine
 * @subpackage core
 * @author 1m.dm
 * @copyright Energine 2006
 * @version $Id$
 */

//require_once('core/framework/SystemConfig.class.php');
//require_once('core/framework/QAL.class.php');

/**
 * Предоставляет производным классам ссылку на объект для работы с БД.
 *
 * @package energine
 * @subpackage core
 * @author 1m.dm
 * @abstract
 */
abstract class DBWorker extends Object {

    /**
     * @access protected
     * @static
     * @var QAL единый для всех экземпляров класса объект QAL
     */
    protected static $dbhInstance;

    /**
     * @access protected
     * @var QAL ссылка на self::$dbhInstance (для производных классов)
     */
    protected $dbh;

    /**
     * Конструктор класса.
     *
     * @access public
     * @return void
     */
    public function __construct() {
        parent::__construct();
        if (!isset(self::$dbhInstance)) {
            $dsn = sprintf('mysql:host=%s;dbname=%s', $this->getConfigValue('database.host'), $this->getConfigValue('database.name'));
            self::$dbhInstance = new QAL(
                $dsn,
                $this->getConfigValue('database.username'),
                $this->getConfigValue('database.password'),
                array(
                    PDO::ATTR_PERSISTENT => false,
                    PDO::ATTR_EMULATE_PREPARES => true,
                    PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true
                )
            );
        }
        $this->dbh = self::$dbhInstance;
    }

    /**
     * Возвращает перевод текстовой константы из таблицы переводов для
     * указанного языка. Если язык явно не указан - используется текущий язык.
     *
     * @access public
     * @static
     * @param string $const текстовая константа
     * @param int $langId язык для перевода
     * @return string
     */
    public static function _translate($const, $langId = null) {
        $result = $const;
        if (!isset($langId)) {
        	$langId = Language::getInstance()->getCurrent();
        }

        $res = self::$dbhInstance->selectRequest(
            'SELECT trans.ltag_value_rtf AS result FROM share_lang_tags ltag '.
            'LEFT JOIN share_lang_tags_translation trans ON trans.ltag_id = ltag.ltag_id '.
            'WHERE ltag.ltag_name = '.self::$dbhInstance->quote(strtoupper($result)).' AND lang_id = '.intval($langId)
        );
        if (is_array($res)) {
        	$result = simplifyDBResult($res, 'result', true);
        }

        return $result;
    }

    /**
     * Возвращает дату в виде строки прописью
     *
     * @param $year
     * @param $month
     * @param $day
     * @return string
     * @static
     */
    public static function _dateToString($year, $month, $day){
		$result = (int)$day.' '.self::_translate('TXT_MONTH_'.(int)$month).' '.$year;
		return $result;
    }

    /**
     * Нестатический метод-обёртка над DBWorker::_translate -
     * для удобства использования внутри производных классов.
     *
     * @access public
     * @param string $const текстовая константа
     * @param int $langId язык для перевода
     * @return string
     * @see DBWorker::_translate()
     */
    public function translate($const, $langID = null) {
        return self::_translate($const, $langID);
    }

    /**
     * Дата прописью
     * Обертка над DBWorker::_dateToString
     *
     * @param $date string
     * @param $format string
     * @return string
     * @see DBWorker::_dateToString
     */
    public function dateToString($date, $format='%d-%d-%d'){
    	list($year, $month, $day) = sscanf($date, $format);
		return self::_dateToString($year, $month, $day);
    }

}
