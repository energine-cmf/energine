<?php
/**
 * Содержит классы BanDateTransform
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
final class BanDateTransform extends Object {

    static private $bannedForever = 'FOREVER';

	static public $intervals = array('DAY'=>'TXT_BAN_FOR_DAY',
                              'WEEK'=>'TXT_BAN_FOR_WEEK',
                              'MONTH'=>'TXT_BAN_FOR_MONTH',
                              'YEAR'=>'TXT_BAN_FOR_YEAR',
                              'FOREVER'=>'TXT_BAN_FOR_EVER');

    static private $intervalsTransform = array('DAY'=>'+1 day',
                                               'WEEK'=>'+1 week',
                                               'MONTH'=>'+1 month',
                                               'YEAR'=>'+1 year',
                                               'FOREVER'=>'2030-04-01');


	public function __construct(){
		parent::__construct();
	}

    /**
     * Форматуємо (повертаємо іншій) масив інтервалів у масив для loadAvailableValues
     * @param  $values array
     * @return array|bool
    */
    static public function getFormattedFDValues(){
        $formattedValues = array();
        $i = 0;
        foreach(self::$intervals as $key=>$value){
            $formattedValues[$i]['date_key'] = $key;
            $formattedValues[$i]['date_value'] = $value;
            $i++;
        }
        return $formattedValues;
    }

    static public function getFormattedBanDate($duration, $fromDate = false){
        if(!$fromDate) $fromDate = time();
        if(array_key_exists($duration,self::$intervalsTransform)){
            if($duration==self::$bannedForever)
                return date('Y-m-d',strtotime(self::$intervalsTransform[$duration]));
            else
                return date('Y-m-d',strtotime(self::$intervalsTransform[$duration],$fromDate));
        }
        return false;
    }



    

}