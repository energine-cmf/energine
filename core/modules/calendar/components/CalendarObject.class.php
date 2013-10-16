<?php
/**
 * Содержит классы CalendarObject и CalendarItem
 *
 * @package energine
 * @subpackage calendar
 * @author dr.Pavka
 * @copyright Energine 2010
 */

/**
 * 
 * Объект календаря
 *
 * @package energine
 * @subpackage calendar
 * @author dr.Pavka
 * @final
 */
final class CalendarObject extends Object implements Iterator {
	const PERIOD_CURRENT = 'current';
	const PERIOD_PREVIOUS = 'previous';
	const PERIOD_NEXT = 'next';
	
	/**
	 * позиция итерации
	 * 
	 * @access private
	 * @var integer 
	 */
	 private $position;
	/**
	 * Сегодня
	 *
	 * Сделано публичным, поскольку его изменение после вызова констурктора не приводит ни к чему плохому
	 * @access public
	 * @var DateTime
	 */
	public $today;
	/**
     * Первый день календарного месяца 
     *
     * @access private
     * @var DateTime
     */
    private $firstDayOfPeriod;
    
	/**
	 * Собственно матрица
	 * Календарь представляет из себя матрицу 7х5
	 *
	 * @access private
	 * @var array
	 */
	private $calendar = array();

	/**
	 * Индекс сопоставляющий дату и месторасположение в матрице
	 *
	 * @access private
	 * @var array
	 */
	private $index = array();
	
	/**
	 * Конструктор создает матрицу
	 *
	 * @return void
	 * @access public
	 */
	public function __construct($monthID = false, $year = false){
		$this->today = new DateTime();
        $this->today->setTime(0, 0, 0);

		$monthID = (int)((!$monthID)?$this->today->format('n'):$monthID);
		$year = (int)((!$year)?$this->today->format('Y'):$year);
		//Определяем день начала календаря
		//Это последний понедельник предыдущего месяца
		//кроме случая когда 1 е число нужно месяца  - понедельник
		$this->firstDayOfPeriod = DateTime::createFromFormat('j-n-Y', '1-'.$monthID.'-'.$year);

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
	 * Возвращает данные о предыдущем периоде
	 * Используется для создания тулбара календаря
	 *  
	 * @return Object
	 * @access public
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
	 * Возвращает данные о начальной и конечной датах текущего календаря
     *
     * $this->calendar - список недель, из последней берём седьмой день
	 * 
	 * @return Object
	 * @access public
	 */
	public function getRange(){
	    return (object)array('start' => $this->calendar[0][0]->getDate(), 'end' => $this->calendar[count($this->calendar)-1][6]->getDate());
	}
	
	

	/**
	 * Возвращает флаг указывающий на то, существует ли такая дата в календаре
	 *
	 * @param DateTime дата
	 * Или
	 * @param int идентификатор строки
	 * @param int идентификатор дня
	 * 
	 * @return boolean
	 * @access protected
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
	 * Возвращает объект календарного дня по индексу
	 * 
	 * @return CalendarItem
	 * @access public
	 */
	public function getItemByIndex($row, $day){
	    $result = null;
	    if ($this->itemExists($row, $day)) {
	    	$result = $this->calendar[$row][$day];
	    }
	    return $result;
	}

	/**
	 * getDate
	 *
	 * @param DateTime
	 * @return CalendarItem
	 * @access public
	 */
	public function getItemByDate(DateTime $date){
		$result = null;
		if ($this->itemExists($date)){
			list($row, $day)  = $this->index[$date->format('Y-m-d')];
			$result = $this->calendar[$row][$day];
		}

		return $result;
	}
        /**
     * Вычисляем информацию о текущей неделе
     *
     * @param DateTime информация о текущем дн
     * @return DateTime[]
     * @access public
     * @static
     */
    static public function getWeek(DateTime $dateObj) {
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
	
	
	/**
	 * @see Iterator 
	 */
    function rewind() {
        $this->position = 0;
    }
    /**
     * @see Iterator
     */
    function current() {
        return /*new IteratorIterator(new ArrayObject(*/$this->calendar[$this->position]/*))*/;
    }
    /**
     * @see Iterator
     */
    function key() {
        return $this->position;
    }
    /**
     * @see Iterator
     */
    function next() {
        ++$this->position;
    }
    /**
     * @see Iterator
     */
    function valid() {
        return isset($this->calendar[$this->position]);
    }	
}

/**
 * Класс овеществляющий день календаря
 
 * @package energine
 * @subpackage calendar
 * @author dr.Pavka
 * @final
 */
final class CalendarItem extends Object implements Iterator {
    /**
     * Property iterartor position
     * 
     * @access private
     * @var int 
     */
     private $position;
    /**
     * День 
     *
     * @access private
     * @var DateTime
     */
    private $date;
    /**
     * Подпись
     *
     * @access private
     * @var string
     */
    private $title;
     
    /**
     * Дополнительные свойства
     *
     * @access private
     * @var array
     */
    private $properties = array();
     
    /**
     * Создает день календаря
     * 
     * @param DateTime Дата
     * @return void
     * @access public
     */
    public function __construct(DateTime $date){
        $this->date = $date;
        $this->setTitle($this->date->format('j'));
        $this->setProperty('day', $this->date->format('j'));
        $this->setProperty('month', $this->date->format('n'));
        $this->setProperty('year', $this->date->format('Y'));

    }
    /**
     * Устанавливает подпись для данного дня
     *
     * @param string подпись
     *
     * @return void
     * @access public
     */
    public function setTitle($title){
        $this->title = $title;
    }

    /**
     * Возвращает подпись данного пункта
     *
     * @return string
     * @access public
     */
    public function getTitle(){
        return $this->title;
    }

    /**
     * Устанавливает значение дополнительного свойства
     *
     * @param string название свойств
     * @param mixed значений свойств
     *
     * @return void
     * @access public
     */
    public function setProperty($propName, $propValue){
        $this->properties[$propName] = $propValue;
    }
    /**
     * Возвращает значение дополнительного свойства
     *
     * @param string значение свойств
     * @return mixed
     * @access public
     */
    public function getProperty($propName){
        $result =  null;
        if(isset($this->properties[$propName])) $result = $this->properties[$propName];

        return $result;
    }

    /**
     * Возвращает объект DateTime сопоставленный с объектом
     *
     * @return DateTime
     * @access public
     */
    public function getDate(){
        return $this->date;
    }
    
    /**
     * При превращении в строку - выводим дату
     * 
     * @return string
     * @access public
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