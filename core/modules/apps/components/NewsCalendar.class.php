<?php 
/**
 * @file
 * NewsCalendar
 *
 * It contains the definition to:
 * @code
class NewsCalendar;
@endcode
 *
 * @author andrii.a
 * @copyright eggmengroup.com
 *
 * @version 1.0.0
 */
namespace Energine\apps\components;
use Energine\calendar\gears\CalendarObject, Energine\share\gears\SystemException, Energine\calendar\components\Calendar;
/**
 * Calendar for news.
 *
 * @code
class NewsCalendar;
@endcode
 */
class NewsCalendar extends Calendar {
    /**
     * @copydoc Calendar::__construct
     */
    public function __construct($name, $module, array $params = null) {
        parent::__construct($name, $module, $params);
        $this->setCalendar(new CalendarObject($this->getParam('month'), $this->getParam('year')));

        //Отмечаем использованные даты календаря
        $range = $this->calendar->getRange();
        $tableName = $this->getParam('tableName');
        $translationTableName = $this->dbh->getTranslationTablename($tableName);

        $existingDates = simplifyDBResult(
            $this->dbh->selectRequest(
                'SELECT DATE_FORMAT(news_date, "%Y-%c-%e") as news_date FROM ' . $tableName .
                ' LEFT JOIN ' . $translationTableName . ' ON ' . $translationTableName . '.news_id = ' . $tableName . '.news_id ' .
                $this->dbh->buildWhereCondition(
                    array_merge(
                        array(
                             'lang_id' => $this->document->getLang(),
                             'news_date>=' .
                             $range->start->format('"Y-m-d"') .
                             ' AND news_date<=' .
                             $range->end->format('"Y-m-d"')),
                        $this->getParam('filter')
                    )
                )
            ),
            'news_date'
        );

        if (is_array($existingDates)) {
            foreach ($existingDates as $date) {
                if ($date = $this->calendar->getItemByDate(\DateTime::createFromFormat('Y-m-d', $date)))
                    $date->setProperty('selected', 'selected');
            }
        }
        if ($date = $this->calendar->getItemByDate($this->getParam('date')))
            $date->setProperty('marked', 'marked');
    }

    /**
     * @copydoc Calendar::defineParams
     */
    protected function defineParams() {
        return array_merge(
            parent::defineParams(),
            array(
                 'month' => false,
                 'year' => false,
                 'date' => new \DateTime(),
                 'filter' => array(),
                 //'template' => 'news',
                 'tableName' => false,
            )
        );
    }

    /**
     * @copydoc Calendar::setParam
     *
     * @throws SystemException 'ERR_404'
     */
    protected function setParam($name, $value) {


        if ($name == 'year') {
            if (!is_numeric($value)) {
                throw new SystemException('ERR_404', SystemException::ERR_404);
            }
            if ($value > (date('Y') + 1)) {
                throw new SystemException('ERR_404', SystemException::ERR_404);
            }
        }
        elseif ($name == 'month') {
            if (!is_numeric($value)) {
                throw new SystemException('ERR_404', SystemException::ERR_404);
            }
            if (($value > 12) || ($value < 1)) {
                throw new SystemException('ERR_404', SystemException::ERR_404);
            }
        }
        elseif($name == 'date'){
            if($value === false){
                throw new SystemException('ERR_404', SystemException::ERR_404);
            }
        }
        parent::setParam($name, $value);
    }


}