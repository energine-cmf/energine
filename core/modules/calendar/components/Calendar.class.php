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

     * @param array $params
     * @access public
     */
    public function __construct($name, $module,   array $params = null) {
        parent::__construct($name, $module,  $params);
        $this->setProperty('exttype', 'calendar');
    }
    /**
     * Построитель календаря более простой чем обычно
     * 
     * @return CalendarBuilder
     */
    protected function createBuilder() {
        return new CalendarBuilder();
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

        $fields = array();
        for($i=0; $i<7; $i++) $fields[$i] = new Field('DOW_'.$i);
        foreach ($this->calendar as $row=>$rowItems){
            for($i = 0; $i < 7; $i++) {
                if($ci = $cal->getItemByIndex($row, $i)){
                    $fields[$i]->setRowData($row, $ci);
                    foreach ($ci as $propertyName => $propertyValue) {
                        $fields[$i]->setRowProperty($row, $propertyName, $propertyValue);
                    }
                }
            }
        }
        array_map(function($f) use($result) {$result->addField($f);},
            $fields
        );
        return $result;
    }
    
    /**
      * Добавляем тулбар
      * 
      * @return Toolbar[]
      * @access protected
      */
    protected function createToolbar(){
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
        return array($toolbar);
    }
    
}