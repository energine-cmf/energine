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
     * @param Document $document
     * @param array $params
     * @access public
     */
    public function __construct($name, $module, Document $document, array $params = null) {
        parent::__construct($name, $module, $document, $params);

        $this->setProperty('template', $this->getParam('template'));
        $this->setCalendar(new CalendarObject($this->getParam('month'), $this->getParam('year')));

        //Отмечаем использованные даты календаря
        $range = $this->calendar->getRange();

        $conditions = array_merge(
            array(
                'post_created>=' .
                        $range->start->format('"Y-m-d"') .
                        ' AND post_created<=' .
                        $range->end->format('"Y-m-d"')),
            $this->getParam('filter')
        );
        if($blogId = (int)$this->getParam('blog_id')){
            $conditions['blog_id'] = $blogId;
        }
        $existingDates = simplifyDBResult(
            $this->dbh->selectRequest(
                'SELECT DATE_FORMAT(post_created, "%X-%c-%e") as post_date FROM blog_post'.
                $this->dbh->buildWhereCondition($conditions)
            ),
            'post_date'
        );

        if (is_array($existingDates)) {
            foreach ($existingDates as $date) {
                $this->calendar->getItemByDate(DateTime::createFromFormat('Y-m-d', $date))->setProperty('selected', 'selected');
            }
        }
    }

    protected function defineParams() {
        return array_merge(
            parent::defineParams(),
            array(
                'month' => false,
                'year' => false,
                'date' => new DateTime(),
                'filter' => array(),
                'blog_id' => false,
                'template'=> 'blogs/'
            )
        );
    }


}