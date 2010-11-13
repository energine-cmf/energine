<?php

/**
 * Класс DBWorker.
 *
 * @package energine
 * @subpackage core
 * @author 1m.dm
 * @copyright Energine 2006
 */
require_once('Object.class.php');
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
     * Кеш переводов
     * (получается за ними по отдельности очень часто нужно обращаться)
     * @var unknown_type
     */
    private static $translations = null;
    
    /**
     * Конструктор класса.
     *
     * @access public
     * @return void
     */
    public function __construct() {
        parent::__construct();

        if (!isset(self::$dbhInstance)) {
            self::$dbhInstance = new QAL(
                'mysql:'.$this->getConfigValue('database.master.dsn'),
                $this->getConfigValue('database.master.username'),
                $this->getConfigValue('database.master.password'),
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
    	$const = strtoupper($const);
    	
        $currentLangId = intval(Language::getInstance()->getCurrent());
        //если не закешированы - кешируем переводы для текущего языка
        //действия по кешированию логичней было бы разместить в конструкторе, 
        //но на момент его вызова еще не известен дефолтный язык
        if(is_null(self::$translations)){
            $res = self::$dbhInstance->selectRequest(
            'SELECT UPPER(ltag_name) AS const, trans.ltag_value_rtf AS translation FROM share_lang_tags ltag '.
            'LEFT JOIN share_lang_tags_translation trans ON trans.ltag_id = ltag.ltag_id '.
            'WHERE lang_id = '.$currentLangId
            );
            if(is_array($res)){
                foreach($res as $row){
                    self::$translations[$currentLangId][$row['const']] = $row['translation'];
                }
            }
            //вот что должно происходить если в базе нет констант текущего языка?
        }
        //устанавливаем дефолтное возвращаемое значение равным значению константы перевода
        $result = $const;
        //
        //Тут столько хитрых проверок не просто так - а чтобы минимизировать количество обращений к БД
        //
        //Если язык - не указан, используем текущий, а информация о константах на нем у нас закеширована
        if (!isset($langId)) {
            $langId = $currentLangId;
            //эта константа есть в переводах для текущего языка?
        	if(isset(self::$translations[$currentLangId][$const])){
        	    //тогда возвращаем ее значение
        	    $result = self::$translations[$currentLangId][$const];
        	}
        	else{
        	    //если не было  - теперь будет
        	    //а значение возвращается дефолтное
        	    self::$translations[$currentLangId][$const] = $const;
        	}
        }
        //может все таки есть инфа в кеше?
        elseif(isset(self::$translations[$langId][$const])){
            //если да - то чудесно
            $result = self::$translations[$langId][$const];
        }
        else{
            //лезем за значением в базу
            $res = self::$dbhInstance->selectRequest(
                'SELECT trans.ltag_value_rtf AS result FROM share_lang_tags ltag '.
                'LEFT JOIN share_lang_tags_translation trans ON trans.ltag_id = ltag.ltag_id '.
                'WHERE ltag.ltag_name = '.self::$dbhInstance->quote($result).' AND lang_id = '.intval($langId)
            );
            if (is_array($res)) {
                //если нашли - вернем его
            	$result = simplifyDBResult($res, 'result', true);
            }
            //а если не нашли - будет возвращаться дефолтное значение
            
            //нашли - не нашли все равно в кеш записываем 
            self::$translations[$langId][$const] = $result;
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
