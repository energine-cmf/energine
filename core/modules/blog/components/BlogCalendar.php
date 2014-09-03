<?php 
/**
 * Содержит класс BlogCalendar
 *
 * @package energine
 * @subpackage stb
 * @author d.pavka
 * @copyright d.pavka@gmail.com
 */

/**
 * Новостной календарь
 *
 * Посты опубликованные в будущем - не публикуются
 *
 * @package energine
 * @subpackage blog
 * @author d.pavka@gmail.com
 */
class BlogCalendar extends Calendar {
    /**
     * Конструктор класса
     *
     * @param string $name
     * @param string $module

     * @param array $params
     * @access public
     */
    public function __construct($name, $module,  array $params = null) {
        parent::__construct($name, $module, $params);
        $this->setCalendar(new CalendarObject($this->getParam('month'), $this->getParam('year')));

        //Отмечаем использованные даты календаря
        $range = $this->calendar->getRange();

        $dateFormat = '"Y-m-d"';
        // Если диапазон календаря заканчивается в будущем - отсекаем до текущего момента
        if($range->end->getTimestamp() > time()){
                $endRange = date($dateFormat);
        }
        else{
            $endRange = $range->end->format($dateFormat);
        }

        $conditions = array_merge(
            array(
                'post_created>=' .
                        $range->start->format($dateFormat) .
                        ' AND post_created<=' .
                        $endRange),
            $this->getParam('filter')
        );

        if($blogId = (int)$this->getParam('blog_id')){
            $conditions['blog_id'] = $blogId;
        }
        $existingDates = simplifyDBResult(
            $this->dbh->selectRequest(
                'SELECT DISTINCT DATE_FORMAT(post_created, "%Y-%c-%e") as post_date FROM blog_post'.
                $this->dbh->buildWhereCondition($conditions)
            ),
            'post_date'
        );

        if (is_array($existingDates)) {
            foreach ($existingDates as $date) {
                $this->calendar->getItemByDate(\DateTime::createFromFormat('Y-m-d', $date))->setProperty('selected', 'selected');
            }
        }
    }

    protected function defineParams() {
        return array_merge(
            parent::defineParams(),
            array(
                'month' => false,
                'year' => false,
                'date' => new \DateTime(),
                'filter' => array(),
                'blog_id' => false,
                'template'=> 'blogs'
            )
        );
    }


}