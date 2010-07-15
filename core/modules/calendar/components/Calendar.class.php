<?php 
/**
 * Содержит класс Calendar
 *
 * @package energine
 * @subpackage calendar
 * @author d.pavka
 * @copyright d.pavka@gmail.com
 */

 /**
  * Компонент календаря
  *
  * @package energine
  * @subpackage calendar
  * @author d.pavka@gmail.com
  */
 class Calendar extends DataSet {
    /**
     * Календарь
     * 
     * @access protected
     * @var CalendarObject 
     */
     protected $calendar;
    /**
     * Конструктор класса
     *
     * @param string $name
     * @param string $module
     * @param Document $document
     * @param array $params
     * @access public
     */
    public function __construct($name, $module, Document $document,  array $params = null) {
        parent::__construct($name, $module, $document,  $params);
        $this->setProperty('exttype', 'calendar');
        //$this->setBuilder(new CalendarBuilder());
    }
    /**
      * Возвращаем объект календаря
      * 
      * @return CalendarObject
      * @access public
      */
    public function getCalendar(){
        if(!isset($this->calendar)) {
        	$this->calendar = new CalendarObject();
        }
        return $this->calendar;
    }
    /**
      * Устаналиваем объект календаря
      * 
      * @param CalendarObject
      * @return void
      * @access public
      */
    public function setCalendar(CalendarObject  $calendar){
        $this->calendar = $calendar;
    }
    /**
      * Создаем описание данных - по сути просто семь дней
      * 
      * @return DataDescription
      * @access protected
      */
    protected function createDataDescription(){
        $result = new DataDescription();
        
        for($i = 0; $i < 7; $i++) {
            $fd = new FieldDescription('DOW_'.$i);
            $fd->setType(FieldDescription::FIELD_TYPE_STRING);
            $result->addFieldDescription($fd);
        }
        return $result;
    }
    
    /**
      * Загружаем данные 
      * 
      * @return Data
      * @access protected
      */
    protected function createData(){
        $result = new Data();
        $cal = $this->getCalendar();
        for($i = 0; $i < 7; $i++) {
        	$f = new Field('DOW_'.$i);
        	for ($row = 0; $row < 5; $row++) {
        		$ci = $cal->getItemByIndex($row, $i);
        		$f->setRowData($row, $ci);
        		foreach ($ci as $propertyName => $propertyValue) {
        			$f->setRowProperty($row, $propertyName, $propertyValue);
        		}
        	}
        	$result->addField($f);
        }
        return $result;
    }
    
    /**
      * Добавляем тулбар
      * 
      * @return void
      * @access protected
      */
    protected function main(){
        parent::main();
        $toolbar = new Toolbar('navigation');
        foreach(array(
            CalendarObject::PERIOD_CURRENT, CalendarObject::PERIOD_PREVIOUS, CalendarObject::PERIOD_NEXT
        ) as $periodType) {
        	$period = $this->getCalendar()->getPeriod($periodType);
            $control = new Link($periodType);
	        $control->setAttribute('month', $period->month);
	        $control->setAttribute('monthName', $period->monthName);
	        $control->setAttribute('year', $period->year);    
            $toolbar->attachControl($control);    	
        }
        $this->addToolbar(
            $toolbar
        );
    }
    
}