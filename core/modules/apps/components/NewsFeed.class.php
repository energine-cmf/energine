<?php
/**
 * Содержит класс NewsFeed
 *
 * @package energine
 * @subpackage share
 * @author dr.Pavka
 * @copyright Energine 2007
 * @version $Id$
 */

/**
 * Лента новостей
 *
 * @package energine
 * @subpackage share
 * @author dr.Pavka
 */
class NewsFeed extends ExtendedFeed {

    /**
     * Календарь
     *
     * @access private
     * @var Calendar
     */
    private $calendar;

    /**
     * Конструктор класса
     * Жестко привязываемя к таблице новостей
     *
     * @param string $name
     * @param string $module

     * @param array $params
     * @access public
     */
    public function __construct($name, $module, array $params = null) {
        parent::__construct($name, $module, $params);
        $this->setTableName('apps_news');
        $this->setOrder(array('news_date' => QAL::DESC));
        if (!$this->document->getProperty('single') && $this->getParam('hasCalendar'))
            $this->createCalendar();
    }
    /**
     * Определяет допустимые параметры компонента и их значения по-умолчанию
     * в виде массива array(paramName => defaultValue).
     *
     * @access protected
     * @return array
     */
    protected function defineParams() {
        $result = array_merge(parent::defineParams(),
            array(
                'hasCalendar' => false
            ));
        return $result;
    }

    protected function createBuilder(){
        return new SimpleBuilder();
    }
    /**
     * Все новости которые имеют будущую дату выводятся только админам
     *
     * @return Data
     */
    protected function createData() {
        if ($this->document->getRights() < ACCESS_EDIT) {
            $this->addFilterCondition(
                'news_date <= NOW()'
            );
        }

        $res = parent::createData();
        return $res;
    }

    protected function main() {
        $ap = $this->getStateParams(true);
        foreach (array(
            'year' => 'YEAR(%s)',
            'month' => 'MONTH(%s)',
            'day' => 'DAY(%s)'
        ) as $parameterName => $SQLFuncName) {
            if (isset($ap[$parameterName])) {
                $this->addFilterCondition(
                    array(
                        sprintf($SQLFuncName, 'news_date') => $ap[$parameterName]
                    )
                );
            }
        }
        parent::main();
    }


    /**
     * Переписан под специфический сегмент УРЛ
     *
     * @return void
     * @access protected
     */

    protected function view() {
        $ap = $this->getStateParams(true);

        $this->addFilterCondition(
            array(
                $this->getTableName() . '.' . $this->getPK() => $ap['id'],
                'news_segment' => $ap['segment'],
            )
        );
        $this->setType(self::COMPONENT_TYPE_FORM);
        $this->setDataDescription($this->createDataDescription());
        $this->setBuilder($this->createBuilder());
        $this->createPager();
        $this->setData($this->createData());
        if (!$this->getData()->isEmpty()) {
            list($newsTitle) = $this->getData()->getFieldByName('news_title')->getData();
            $this->document->componentManager->getBlockByName('breadCrumbs')->addCrumb('', $newsTitle);
        }
        else {
            throw new SystemException('ERR_404', SystemException::ERR_404);
        }

        $this->addToolbar($this->createToolbar());

        foreach ($this->getDataDescription() as $fieldDescription) {
            $fieldDescription->setMode(FieldDescription::FIELD_MODE_READ);
        }
        $am = new AttachmentManager($this->getDataDescription(), $this->getData(), $this->getTableName());
        $am->createFieldDescription();
        $am->createField();

    }

    /**
     * Выводим новости, соответствующие
     * определенному тэгу. Если задан несуществующий тег,
     * то убираем все ранее полученные данные.
     *
     * @return void
     * @access protected
     */

    protected function tag(){
        $tagID = $this->getStateParams(true);
        $tagID = (int)$tagID['tagID'];
        $newsIDs = $this->dbh->select($this->getTableName().'_tags','news_id',array('tag_id' => $tagID));
        if(is_array($newsIDs)){
            $newsIDs = array_keys(convertDBResult($newsIDs,'news_id',true));
            $this->addFilterCondition(array($this->getTableName().'.news_id' => $newsIDs));
            $tagName = simplifyDBResult(
                $this->dbh->select('share_tags','tag_name',array('tag_id' => $tagID)),
                'tag_name',
                true);
            $pageTitle = $this->translate('TXT_NEWS_BY_TAG').': '.$tagName;
            E()->getDocument()->componentManager->getBlockByName('breadCrumbs')->addCrumb(null,$pageTitle);
            E()->getDocument()->setProperty('title',$pageTitle);
        }
        $this->main();
        if($newsIDs===true){
            $this->setData(new Data());
        }
        $this->pager->setProperty('additional_url','tag/'.$tagID.'/');
    }

    /**
     * Функция для генерация компонента календарь,
     * который помогает осуществлять навигацию по новостям.
     *
     * @access private
     * @var Calendar
     */
    protected function createCalendar() {
        $calendarParams = array();
        $ap = $this->getStateParams(true);
        if ($this->getState() == 'main') {
            if (isset($ap['year']) && isset($ap['month']) &&
                    isset($ap['day'])
            ) {
                if ($this->getParam('hasCalendar')) {
                    $calendarParams['month'] = $ap['month'];
                    $calendarParams['year'] = $ap['year'];
                    $calendarParams['date'] =
                            DateTime::createFromFormat('Y-m-d',
                                    $ap['year'] . '-' . $ap['month'] . '-' .
                                            $ap['day']);
                }

                $additionalFilter =
                        'DAY(news_date) = "' . $ap['day'] .
                                '" AND MONTH(news_date) = "' .
                                $ap['month'] .
                                '" AND YEAR(news_date) = "' .
                                $ap['year'] . '"';
            }
            elseif (isset($ap['year']) && isset($ap['month'])) {
                if ($this->getParam('hasCalendar')) {
                    $calendarParams['month'] = $ap['month'];
                    $calendarParams['year'] = $ap['year'];
                }
                $additionalFilter =
                        'MONTH(news_date) = "' . $ap['month'] .
                                '" AND YEAR(news_date) = "' .
                                $ap['year'] . '"';
            }
            elseif (isset($ap['year'])) {
                if ($this->getParam('hasCalendar')) {
                    $calendarParams['year'] = $ap['year'];
                }
                $additionalFilter =
                        'YEAR(news_date) = "' . $ap['year'] . '"';
            }

            if ($this->getParam('tags')) {

                $filteredIDs = TagManager::getFilter($this->getParam('tags'), $this->tagsTableName);

                if (!empty($filteredIDs)) {
                    $this->addFilterCondition(array('trku_news.news_id' => $filteredIDs));

                }
                else {
                    $this->addFilterCondition(array('trku_news.news_id' => 0));
                }
            }
        }
        elseif (($this->getState() == 'view') &&
                ($this->getParam('hasCalendar'))
        ) {
            $calendarParams['month'] = $ap['month'];
            $calendarParams['year'] = $ap['year'];
            $calendarParams['date'] = DateTime::createFromFormat('Y-m-d',
                    $ap['year'] . '-' . $ap['month'] . '-' . $ap['day']);
        }

        if ($this->getParam('hasCalendar')) {
            $calendarParams['filter'] = $this->getFilter();
            $calendarParams['tableName'] = $this->getTableName();
            //Создаем компонент календаря новостей
            $this->document->componentManager->addComponent(
                $this->calendar =
                        $this->document->componentManager->createComponent('calendar', 'apps', 'NewsCalendar', $calendarParams)
            );
        }

        if (isset($additionalFilter)) {
            $this->addFilterCondition($additionalFilter);
        }
    }
}