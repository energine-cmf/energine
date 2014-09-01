<?php
/**
 * @file
 * Calendar
 *
 * It contains the definition to:
 * @code
class Calendar;
@endcode
 *
 * @author d.pavka
 * @copyright d.pavka@gmail.com
 *
 * @version 1.0.0
 */
namespace Energine\calendar\components;
use Energine\share\components\DataSet, Energine\calendar\gears\CalendarObject, CalendarBuilder, Energine\share\gears\DataDescription, Energine\share\gears\FieldDescription, Energine\share\gears\Toolbar, Energine\share\gears\Link, Energine\share\gears\Data, Energine\share\gears\Field;
/**
 * Calendar component.
 *
 * @code
class Calendar;
@endcode
 */
 class Calendar extends DataSet {
    /**
     * Calendar.
     * @var CalendarObject $calendar
     */
     protected $calendar;
    /**
     * @copydoc DataSet::__construct
     */
    public function __construct($name, $module,   array $params = null) {
        parent::__construct($name, $module,  $params);
        $this->setProperty('exttype', 'calendar');
    }
    /**
     * @copydoc DataSet::createBuilder
     */
    protected function createBuilder() {
        return new CalendarBuilder();
    }
    /**
      * Get calendar object.
      * 
      * @return CalendarObject
      */
    public function getCalendar(){
        if(!isset($this->calendar)) {
        	$this->calendar = new CalendarObject();
        }
        return $this->calendar;
    }
    /**
      * Set calendar object.
      * 
      * @param CalendarObject $calendar Calendar object.
      */
    public function setCalendar(CalendarObject  $calendar){
        $this->calendar = $calendar;
    }
    /**
      * @copydoc DataSet::createDataDescription
      */
    // Создаем описание данных - по сути просто семь дней
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
      * @copydoc DataSet::createData
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
      * @copydoc DataSet::createToolbar
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