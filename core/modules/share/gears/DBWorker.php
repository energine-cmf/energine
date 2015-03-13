<?php
/**
 * @file
 * DBWorker.
 *
 * @code
abstract class DBWorker;
@endcode
 *
 * @author 1m.dm
 * @copyright Energine 2006
 *
 * @version 1.0.0
 */
namespace Energine\share\gears;
/**
 * Provide the derived classes the reference to the object for work with DB.
 *
 * @code
abstract class DBWorker;
@endcode
 *
 * @abstract
 */
abstract class DBWorker extends Object {
    /**
     * QAL object.
     * @var QAL $dbhInstance
     */
    protected static $dbhInstance;

    /**
     * Reference to the DBWorker::$dbhInstance for derived classes.
     * @var QAL $dbh
     */
    protected $dbh;

    //todo VZ: I do not understand the comment in the brackets.
    /**
     * Translation cache.
     *
     * (получается за ними по отдельности очень часто нужно обращаться)
     * @var array $translationsCache
     */
    private static $translationsCache = null;

    /**
     * Request to find translation.
     * @var PDOStatement $findTranslationSQL
     */
    private static $findTranslationSQL;

    public function __construct() {
        $this->dbh = E()->getDB();
        self::$findTranslationSQL = $this->dbh->getPDO()->prepare('SELECT trans.ltag_value_rtf AS translation FROM share_lang_tags ltag  LEFT JOIN share_lang_tags_translation trans ON trans.ltag_id = ltag.ltag_id  WHERE (ltag.ltag_name = ?) AND (lang_id = ?)');
    }

    /**
     * Get the translation of the text constant.
     *
     * Get the translation of the text constant from the translation table for specific language.
     * If the language not provided, then current language will be used.
     *
     * @param string $const Text constant
     * @param int $langId Language ID.
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
        if(!isset(self::$translationsCache[$langId][$const])){
            //Если что то пошло не так - нет смысл генерить ошибку, отдадим просто константу
            if(self::$findTranslationSQL->execute(array($const, $langId))){
                //записали в кеш
                if($result = self::$findTranslationSQL->fetchColumn()){
                    self::$translationsCache[$langId][$const] = $result;
                }
                else {
                    $result = $const;
                }
            }

        }
        //За переводом уже обращались  Он есть
        elseif(self::$translationsCache[$langId][$const]){
            $result = self::$translationsCache[$langId][$const];
        }
        //Неявный случай - за переводом уже обращались но его нету
        //Отдаем константу

        return $result;
    }

    /**
     * Transform date to the string.
     *
     * @param int $year Year value.
     * @param int $month Month value.
     * @param int $day Day value
     * @return string
     */
    public static function _dateToString($year, $month, $day) {
        $result = (int)$day . ' ' . self::_translate('TXT_MONTH_' . (int)$month) . ' ' . $year;
        return $result;
    }

    /**
     * Non-static wrapper method over DBWorker::_translate.
     *
     * This is for using inside derived class.
     *
     * @param string $const Text constant.
     * @param mixed $langID Language ID.
     * @return string
     */
    public function translate($const, $langID = null) {
        return self::_translate($const, $langID);
    }

    /**
     * Non-static wrapper method over DBWorker::_dateToString.
     *
     * @param string $date Date.
     * @param string $format Date format.
     * @return string
     */
    public function dateToString($date, $format = '%d-%d-%d') {
        list($year, $month, $day) = sscanf($date, $format);
        return self::_dateToString($year, $month, $day);
    }

}
