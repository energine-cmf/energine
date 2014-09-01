<?php
/**
 * @file
 * CalendarObject, CalendarItem
 *
 * It contains the definition to:
 * @code
final class CalendarObject;
final class CalendarItem
@endcode
 *
 * @author dr.Pavka
 * @copyright Energine 2010
 *
 * @version 1.0.0
 */
namespace Energine\calendar\gears;
use Energine\share\gears\Object, Energine\share\gears\DBWorker;
/**
 * Calendar object.
 *
 * @code
final class CalendarObject;
final class CalendarItem
@endcode
 *
 * @final
 */
final class CalendarObject extends Object implements \Iterator {
    /**
     * Current period.
     */
    const PERIOD_CURRENT = 'current';
    /**
     * Previous period.
     */
    const PERIOD_PREVIOUS = 'previous';
    /**
     * Next period.
     */
    const PERIOD_NEXT = 'next';
	
	/**
     * Iteration position.
	 * @var integer $position
	 */
	 private $position;
	/**
     * Today date and time.
	 * @var \DateTime $today
	 */
	public $today;
	/**
     * First month day.
     * @var \DateTime $firstDayOfPeriod
     */
    private $firstDayOfPeriod;
    
	/**
     * Calendar matrix.
     * This is a list of month weeks.@n
     * Matrix has size 7x5.
	 * @var array $calendar
	 */
	private $calendar = array();

	/**
     * Day index in the calendar matrix that corresponds the specific day.
	 * @var array $index
     */
	private $index = array();
	
	public function __construct($monthID = false, $year = false){
		$this->today = new \DateTime();
        $this->today->setTime(0, 0, 0);

		$monthID = (int)((!$monthID)?$this->today->format('n'):$monthID);
		$year = (int)((!$year)?$this->today->format('Y'):$year);
		//Определяем день начала календаря
		//Это последний понедельник предыдущего месяца
		//кроме случая когда 1 е число нужно месяца  - понедельник
		$this->firstDayOfPeriod = \DateTime::createFromFormat('j-n-Y', '1-'.$monthID.'-'.$year);

		$firstDayOfCalendar = clone $this->firstDayOfPeriod;
		//У буржуев Воскресенье - первый день недели
		if($this->firstDayOfPeriod->format('w') != 1) {
			$firstDayOfCalendar->modify('last Monday');
		}
		$date = clone $firstDayOfCalendar;

        $lastDayOfMonth = $this->firstDayOfPeriod->format('t');
        for($go=true,$i=0; true; $i++){
            $row = floor($i/7); $day = floor($i%7);

            $ci = new CalendarItem($date);
            if((int)$date->format('n') == (int)$this->firstDayOfPeriod->format('n')){
                $ci->setProperty('current', 'current');
            }
            if($date == $this->today) {
                $ci->setProperty('today', 'today');
            }
            $this->calendar[$row][$day] = $ci;
            $this->index[$date->format('Y-m-d')] = array($row, $day);
            // цикл заканчивается в последний день недели
            if($date->format('w') == 0)
                // либо это последний день выводимого месяца
                if((($date->format('d') == $lastDayOfMonth) and ($date->format('n') == $monthID))
                    // либо уже следующий месяц
                    or ($date->format('n') > $monthID)
                    // либо уже следующий год
                    or ($date->format('n') < $monthID))
                        break;

            $date = clone $date;
            $date->modify('+1 day');
        }
	}
	
	/**
     * Get previous period.
	 *
     * @param string $periodType Period type.
	 * @return Object
	 *
	 * @note It is used to create calendar toolbar.
	 */
	public function getPeriod($periodType){
	   $tmp = clone $this->firstDayOfPeriod;
		switch ($periodType){
	       case self::PERIOD_NEXT:
	       	$tmp->modify('+1 month');
	       break;
	       case self::PERIOD_PREVIOUS:
	           $tmp->modify('-1 day');    	
	       	break;
		}
	   return (object)array('month' => $tmp->format('n'), 'monthName' => DBWorker::_translate('TXT_MONTH_NAME_'.$tmp->format('m')), 'year' => $tmp->format('Y'));
	}
	
	/**
     * Get data about first and last date from current calendar.
	 * 
	 * @return Object
	 */
	public function getRange(){
	    return (object)array('start' => $this->calendar[0][0]->getDate(), 'end' => $this->calendar[count($this->calendar)-1][6]->getDate());
	}
	
	

	/**
     * Check if exist specific day in the calendar.
	 *
     * Input arguments can be:
	 * - one argument:
     *   - {\DateTime} Specific date
	 * - two arguments:
     *   - {int} Row ID.
     *   - {int} Day ID.
     *
	 * @return boolean
	 */
	protected function itemExists(){
		$result = null;
		$args = func_get_args();
		if(sizeof($args) == 2) {
			list($row, $day) = $args;
			$result = isset($this->calendar[$row][$day]);
		}
		elseif((sizeof($args) == 1) && is_a($args[0], 'DateTime')) {
			$result = isset($this->index[$args[0]->format('Y-m-d')]);
		}
		
		return $result;
	}
	
	/**
     * Get calendar day item by index.
     *
     * @param int $row Row ID.
     * @param int $day Day ID.
     * @return CalendarItem
	 */
	public function getItemByIndex($row, $day){
	    $result = null;
	    if ($this->itemExists($row, $day)) {
	    	$result = $this->calendar[$row][$day];
	    }
	    return $result;
	}

	/**
	 * Get calendar item by date.
	 *
	 * @param \DateTime $date Date.
	 * @return CalendarItem
	 */
	public function getItemByDate(\DateTime $date){
		$result = null;
		if ($this->itemExists($date)){
			list($row, $day)  = $this->index[$date->format('Y-m-d')];
			$result = $this->calendar[$row][$day];
		}

		return $result;
	}
    /**
     * Calc information about current week.
     *
     * @param \DateTime $dateObj Information about current day.
     * @return \DateTime[]
     */
    static public function getWeek(\DateTime $dateObj) {
        $result = array();
        $tmpDateObj = clone $dateObj;

        //Ищем дату ближайшего понедельника

        //Если сегодня не понедельник
        if ($tmpDateObj->format('w') != 1) {
            $tmpDateObj->modify('last Monday');
        }

        for ($i = 1; $i <= 7; $i++) {
            $result[] = clone $tmpDateObj;
            $tmpDateObj->modify('+1 day');
        }
        return $result;
    }
	

    function rewind() {
        $this->position = 0;
    }
    function current() {
        return /*new IteratorIterator(new ArrayObject(*/$this->calendar[$this->position]/*))*/;
    }
    function key() {
        return $this->position;
    }
    function next() {
        ++$this->position;
    }
    function valid() {
        return isset($this->calendar[$this->position]);
    }
}

/**
 * Calendar day.
 *
 * @code
final class CalendarObject;
final class CalendarItem
@endcode
 *
 * @final
 */

final class CalendarItem extends Object implements \Iterator {
    /**
     * Property iterator position.
     * @var int $position
     */
     private $position;
    /**
     * Day.
     * @var \DateTime $date
     */
    private $date;
    /**
     * Title.
     * @var string $title
     */
    private $title;
     
    /**
     * Additional properties.
     * @var array $properties
     */
    private $properties = array();
     
    /**
     * @param \DateTime $date Date.
     */
    public function __construct(\DateTime $date){
        $this->date = $date;
        $this->setTitle($this->date->format('j'));
        $this->setProperty('day', $this->date->format('j'));
        $this->setProperty('month', $this->date->format('n'));
        $this->setProperty('year', $this->date->format('Y'));

    }
    /**
     * Set title for current day.
     *
     * @param string $title Title.
     */
    public function setTitle($title){
        $this->title = $title;
    }

    /**
     * Get title.
     *
     * @return string
     */
    public function getTitle(){
        return $this->title;
    }

    /**
     * Set additional property.
     *
     * @param string $propName Property name.
     * @param mixed $propValue Property value.
     */
    public function setProperty($propName, $propValue){
        $this->properties[$propName] = $propValue;
    }
    /**
     * Get property.
     *
     * @param string $propName Property name.
     * @return mixed|null
     */
    public function getProperty($propName){
        $result =  null;
        if(isset($this->properties[$propName])) $result = $this->properties[$propName];

        return $result;
    }

    /**
     * Get date.
     *
     * @return \DateTime
     */
    public function getDate(){
        return $this->date;
    }
    
    /**
     * Return title by converting into string.
     *
     * @return string
     */
    public function __toString(){
        return $this->getTitle();
    }
    
    function rewind() {
        $this->position = 0;
    }

    function current() {
    	$values = array_values($this->properties);
        return $values[$this->position];
    }

    function key() {
    	$keys = array_keys($this->properties);
        return $keys[$this->position];
    }

    function next() {
        ++$this->position;
    }

    function valid() {
    	$values = array_values($this->properties);
        return isset($values[$this->position]);
    }    
}