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
     * Первый день месяца 
     *
     * Сделано публичным, поскольку его изменение после вызова констурктора не приводит ни к чему плохому
     * @access public
     * @var DateTime
     */
    public $first;
    
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
	 * Итераторы
	 * 
	 * @access private
	 * @var array 
	 */
	 private $rowIterators;
	/**
	 * Констурктор создает матрицу
	 *
	 * @return void
	 * @access public
	 */
	public function __construct($monthID = false, $year = false){
		parent::__construct();
		$this->today = new DateTime();
		 
		$monthID = (int)((!$monthID)?$this->today->format('n'):$monthID);
		$year = (int)((!$year)?$this->today->format('Y'):$year);
		//Определяем день начала календаря
		//Это последний понедельник предыдущего месяца
		//кроме случая когда 1 е число нужно месяца  - понедельник
		$firstDayOfMonth = DateTime::createFromFormat('j-n-Y', '1-'.$monthID.'-'.$year);

		$this->first = clone $firstDayOfMonth;
		//У буржуев Воскресенье - первый день недели
		if($firstDayOfMonth->format('w') != 1) {
			$this->first->modify('last Monday');
		}
		$date = clone $this->first;

		for ($row = 0; $row < 5; $row ++){
			for ($day = 0; $day < 7; $day ++){
				$ci = new CalendarItem($date);
				if((int)$date->format('n') == (int)$firstDayOfMonth->format('n')){
					$ci->setProperty('current', 'current');
				}
				if($date == $this->today) {
					$ci->setProperty('today', 'today');
				}
				$this->calendar[$row][$day] = $ci;
				$this->index[$date->format('Y-m-d')] = array($row, $day);
				$date = clone $date;
				$date->modify('+1 day');
			}
		}
	}

	/**
	 * Возвращает флаг указывающий на то, существует ли такая дата в календаре
	 *
	 * @param DateTime дата
	 * @return boolean
	 * @access public
	 */
	public function itemExists(DateTime $date){
		return isset($this->index[$date->format('Y-m-d')]);
	}
	
	/**
	 * Возвращает обїект календарного дня по индексу
	 * 
	 * @return CalendarItem
	 * @access public
	 */
	public function getItemByIndex($row, $day){
	    $result = null;
	    if (isset($this->calendar[$row][$day])) {
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
    	parent::__construct();
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