<?php

/**
 * Класс DBWorker.
 *
 * @package energine
 * @subpackage kernel
 * @author 1m.dm
 * @copyright Energine 2006
 */
/**
 * Предоставляет производным классам ссылку на объект для работы с БД.
 *
 * @package energine
 * @subpackage kernel
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
     * Кеш переводов
     * (получается за ними по отдельности очень часто нужно обращаться)
     * @var array
     */
    private static $translations = null;
    /**
     * @var PDOStatement
     */
    private static $findTranslationSQL;

    /**
     * Конструктор класса.
     *
     * @access public
     * @return void
     */
    public function __construct() {
        $this->dbh = E()->getDB();
        self::$findTranslationSQL = $this->dbh->getPDO()->prepare('SELECT trans.ltag_value_rtf AS translation FROM share_lang_tags ltag  LEFT JOIN share_lang_tags_translation trans ON trans.ltag_id = ltag.ltag_id  WHERE (ltag.ltag_name = ?) AND (lang_id = ?)');
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
        if (empty($const)) return $const;
        $const = strtoupper($const);
        if(is_null($langId)){
            $langId = intval(E()->getLanguage()->getCurrent());
        }
        $result = $const;

        //Мы еще не обращались за этим переводом
        if(!isset(self::$translations[$langId][$const])){
            //Если что то пошло не так - нет смысл генерить ошибку, отдадим просто константу
            if(self::$findTranslationSQL->execute(array($const, $langId))){
                //записали в кеш
                if($result = self::$findTranslationSQL->fetchColumn()){
                    self::$translations[$langId][$const] = $result;
                }
                else {
                    $result = $const;
                }
            }

        }
        //За переводом уже обращались  Он есть
        elseif(self::$translations[$langId][$const]){
            $result = self::$translations[$langId][$const];
        }
        //Неявный случай - за переводом уже обращались но его нету
        //Отдаем константу

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
    public static function _dateToString($year, $month, $day) {
        $result = (int)$day . ' ' . self::_translate('TXT_MONTH_' . (int)$month) . ' ' . $year;
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
    public function dateToString($date, $format = '%d-%d-%d') {
        list($year, $month, $day) = sscanf($date, $format);
        return self::_dateToString($year, $month, $day);
    }

}
