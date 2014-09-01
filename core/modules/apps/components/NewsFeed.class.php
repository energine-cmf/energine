<?php
/**
 * @file
 * NewsFeed
 *
 * It contains the definition to:
 * @code
class NewsFeed;
 * @endcode
 *
 * @author dr.Pavka
 * @copyright Energine 2007
 *
 * @version 1.0.0
 */
namespace Energine\apps\components;
use Energine\share\gears\QAL, Energine\share\gears\SystemException, Energine\share\gears\AttachmentManager, Energine\share\gears\FieldDescription, Energine\share\gears\SimpleBuilder, Energine\share\gears\Data, Energine\share\gears\TagManager;
/**
 * News line.
 *
 * @code
class NewsFeed;
 * @endcode
 */
class NewsFeed extends ExtendedFeed {
    /**
     * Calendar.
     * @var Calendar $calendar
     */
    private $calendar;

    /**
     * @copydoc ExtendedFeed::__construct
     */
    // Жестко привязываемя к таблице новостей
    public function __construct($name, $module, array $params = null) {
        parent::__construct($name, $module, $params);
        $this->setTableName('apps_news');
        $this->setOrder(array('news_date' => QAL::DESC));
        if ($this->document->getRights() < ACCESS_EDIT) {
            $this->addFilterCondition(array('news_is_active' => true));
        }
        if (!$this->document->getProperty('single') && $this->getParam('hasCalendar'))
            $this->createCalendar();
    }

    /**
     * @copydoc ExtendedFeed::defineParams
     */
    // Определяет допустимые параметры компонента и их значения по-умолчанию в виде массива array(paramName => defaultValue).
    protected function defineParams() {
        $result = array_merge(parent::defineParams(),
            array(
                'hasCalendar' => false
            ));
        return $result;
    }

    /**
     * @copydoc \Energine\apps\components\ExtendedFeed::createBuilder
     */
    protected function createBuilder() {
        return new SimpleBuilder();
    }

    /**
     * @copydoc ExtendedFeed::createData
     */
    // Все новости которые имеют будущую дату выводятся только админам
    protected function createData() {
        if ($this->document->getRights() < ACCESS_EDIT) {
            $this->addFilterCondition(
                'news_date <= NOW()'
            );
        }

        $res = parent::createData();
        return $res;
    }

    /**
     * @copydoc ExtendedFeed::main
     */
    /*
     * Перед вызовом родителя добавляем ограничения
     * После вызова - добавляем в пейджер
     */
    protected function main() {
        $ap = $this->getStateParams(true);

        foreach ($dateArr = array(
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
        if ($this->pager) {
            $additionalURL = array();
            foreach (array_keys($dateArr) as $parameterName) {
                if (isset($ap[$parameterName])) {
                    array_push($additionalURL, $ap[$parameterName]);
                } else {
                    break;
                }
            }
            if ($additionalURL) {
                unset($ap['pageNumber']);
                $pageTitle = $this->translate('TXT_NEWS_BY_DATE') . ': ' . implode('/', array_reverse($ap));
                E()->getDocument()->componentManager->getBlockByName('breadCrumbs')->addCrumb(null, $pageTitle);
                E()->getDocument()->setProperty('title', $pageTitle);
                $additionalURL = implode('/', $additionalURL) . '/';
                $this->pager->setProperty('additional_url', $additionalURL);
            }
        }
    }


    /**
     * @copydoc ExtendedFeed::view
     *
     * @throws SystemException 'ERR_404'
     */
    // Переписан под специфический сегмент УРЛ
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
        } else {
            throw new SystemException('ERR_404', SystemException::ERR_404);
        }

        $this->addToolbar($this->createToolbar());
        $this->js = $this->buildJS();

        foreach ($this->getDataDescription() as $fieldDescription) {
            $fieldDescription->setMode(FieldDescription::FIELD_MODE_READ);
        }
        $am = new AttachmentManager($this->getDataDescription(), $this->getData(), $this->getTableName(), true);
        $am->createFieldDescription();
        $am->createField();

    }

    /**
     * Show news that correspond to specific tag.
     *
     * @note If tag is not exist then clean all previously received data.
     */
    protected function tag() {
        $tagID = $this->getStateParams(true);
        $tagID = (int)$tagID['tagID'];
        $newsIDs = $this->dbh->select($this->getTableName() . '_tags', 'news_id', array('tag_id' => $tagID));
        if (is_array($newsIDs)) {
            $newsIDs = array_keys(convertDBResult($newsIDs, 'news_id', true));
            $this->addFilterCondition(array($this->getTableName() . '.news_id' => $newsIDs));
            $tagName = $this->dbh->getScalar('SELECT tag_name FROM share_tags LEFT JOIN share_tags_translation USING(tag_id) WHERE (lang_id = %s) AND (tag_id = %s)', $this->document->getLang(), $tagID);
            $pageTitle = $this->translate('TXT_NEWS_BY_TAG') . ': ' . $tagName;
            E()->getDocument()->componentManager->getBlockByName('breadCrumbs')->addCrumb(null, $pageTitle);
            E()->getDocument()->setProperty('title', $pageTitle);
        }
        $this->main();
        if ($newsIDs === true) {
            $this->setData(new Data());
        }
        if ($this->pager) {
            $this->pager->setProperty('additional_url', 'tag/' . $tagID . '/');
        }
    }

    /**
     * Create calendar.
     * Calendar helps to navigate over the news.
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
                        \DateTime::createFromFormat('Y-m-d',
                            $ap['year'] . '-' . $ap['month'] . '-' .
                            $ap['day']);
                }

                $additionalFilter =
                    'DAY(news_date) = "' . $ap['day'] .
                    '" AND MONTH(news_date) = "' .
                    $ap['month'] .
                    '" AND YEAR(news_date) = "' .
                    $ap['year'] . '"';
            } elseif (isset($ap['year']) && isset($ap['month'])) {
                if ($this->getParam('hasCalendar')) {
                    $calendarParams['month'] = $ap['month'];
                    $calendarParams['year'] = $ap['year'];
                }
                $additionalFilter =
                    'MONTH(news_date) = "' . $ap['month'] .
                    '" AND YEAR(news_date) = "' .
                    $ap['year'] . '"';
            } elseif (isset($ap['year'])) {
                if ($this->getParam('hasCalendar')) {
                    $calendarParams['year'] = $ap['year'];
                }
                $additionalFilter =
                    'YEAR(news_date) = "' . $ap['year'] . '"';
            }

            if ($this->getParam('tags')) {

                $filteredIDs = TagManager::getFilter($this->getParam('tags'), $this->tagsTableName);

                if (!empty($filteredIDs)) {
                    $this->addFilterCondition(array($this->getTableName() . '.news_id' => $filteredIDs));

                } else {
                    $this->addFilterCondition(array($this->getTableName() . '.news_id' => 0));
                }
            }
        } elseif (($this->getState() == 'view') &&
            ($this->getParam('hasCalendar'))
        ) {
            $calendarParams['month'] = $ap['month'];
            $calendarParams['year'] = $ap['year'];
            $calendarParams['date'] = \DateTime::createFromFormat('Y-m-d',
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

    protected function rss() {
        $this->setParam('recordsPerPage', self::RECORD_PER_PAGE);
        $this->main();
        if ($this->getData()->getFieldByName('news_date')) {
            foreach ($field = $this->getData()->getFieldByName('news_date') as $key => $value) {
                $field->setRowData($key, date('D, d M Y H:i:m +0300', strtotime($value)));
            }
        }
        if ($this->getData()->getFieldByName('news_announce_rtf')) {
            foreach ($field = $this->getData()->getFieldByName('news_announce_rtf') as $key => $value) {
                $field->setRowData($key, strip_tags($value));
            }
        }
        $this->pager->setRecordsCount(self::RECORD_PER_PAGE);

        E()->getController()->getTransformer()->setFileName('core/modules/apps/transformers/rss.xslt', true);
        E()->getResponse()->setHeader('Content-Type', 'text/xml; charset=utf-8');
    }
}