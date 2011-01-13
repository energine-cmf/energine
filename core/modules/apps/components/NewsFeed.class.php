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

//require_once('core/modules/share/components/DBDataSet.class.php');

/**
 * Лента новостей
 *
 * @package energine
 * @subpackage share
 * @author dr.Pavka
 */
class NewsFeed extends Feed {
    /**
     * Конструктор класса
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

    protected function defineParams() {
        return array_merge(
            parent::defineParams(),
            array(
                'editable' => true,
            )
        );
    }

    protected function createData() {
        $this->addFilterCondition(
            array('smap_id' => $this->document->getID())
        );
        if ($this->document->getRights() < ACCESS_EDIT) {
            $this->addFilterCondition(
                'news_date <= NOW()'
            );
        }
        return parent::createData();
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
     * View
     *
     * @return type
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

    }
}