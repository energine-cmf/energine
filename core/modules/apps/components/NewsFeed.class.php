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
     * определенному тэгу.
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
        $this->pager->setProperty('additional_url','tag/'.$tagID.'/');
    }
}