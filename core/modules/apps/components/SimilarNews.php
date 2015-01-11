<?php
/**
 * @file
 * SimilarNews
 *
 * It contains the definition to:
 * @code
class SimilarNews;
@endcode
 *
 * @author Andrii A
 * @copyright eggmengroup.com
 *
 * @version 1.0.0
 */
namespace Energine\apps\components;
use Energine\share\components\DBDataSet, Energine\share\gears\SimpleBuilder, Energine\share\gears\QAL, Energine\share\gears\FieldDescription, Energine\share\gears\Field;

/**
 * Similar news.
 *
 * @code
class SimilarNews;
@endcode
 */
class SimilarNews extends DBDataSet {
    /**
     * Default component name for binding.
     */
    const DEFAULT_LINK_TO = 'news';
    /**
     * Separator of tag names.
     */
    const TAG_SEPARATOR = ',';

    /**
     * News ID.
     * @var int $newsID
     */
    private $newsID;

    /**
     * Component to which "Similar news" will be bounded.
     * @var Component $cp
     */
    private $cp;

    /**
     * @copydoc DBDataSet::__construct
     */
    public function __construct($name, $module, array $params = null) {
        parent::__construct($name, $module, $params);
        $this->setParam('onlyCurrentLang', true);
        $bindComponentName = ($this->getParam('bind'))? $this->getParam('bind'): self::DEFAULT_LINK_TO;
        $this->cp =
            E()->getDocument()->componentManager->getBlockByName($bindComponentName);
        if (!$this->cp || ($this->cp && $this->cp->getState() != 'view')) {
            $this->disable();
        }
        $this->setParam('recordsPerPage',false);
    }

    /**
     * @copydoc DBDataSet::createBuilder
     */
    protected function createBuilder() {
        return new SimpleBuilder();
    }

    /**
     * @copydoc DBDataSet::defineParams
     */
    // Определяет допустимые параметры компонента и их значения по-умолчанию в виде массива array(paramName => defaultValue).
    protected function defineParams() {
        $result = array_merge(parent::defineParams(),
            array(
                'limit' => 5,
                'bind' => ''
            ));
        return $result;
    }

    /**
     * @copydoc DBDataSet::main
     */
    protected function main() {
        $ap = $this->cp->getStateParams(true);
        $this->newsID = (int) $ap['newsID'];
        $this->setTableName($this->cp->getTableName());
        $similarNews = $this->getSimilarNewsIDs();

        if ($this->document->getRights() != ACCESS_FULL)
            $this->addFilterCondition(array(
                '(news_date<="' . date('Y-m-d H:i:s') . '")'));

        $this->addFilterCondition(array(
            $this->getTableName() . '.' . $this->getPK() => $similarNews));
        $this->setOrder(array('news_date' => QAL::DESC));

        parent::main();

        if ($this->getData()->getFieldByName('site_id')) {
            $this->getDataDescription()->getFieldDescriptionByName('site_id')->setType(FieldDescription::FIELD_TYPE_STRING);
            foreach ($f =
                             $this->getData()->getFieldByName('site_id') as $rowID => $siteID) {
                $f->setRowData($rowID, E()->getSiteManager()->getSiteById($siteID)->base);
            }
        } else {
            $fd = new Field('site_id');
            $fdd = new FieldDescription('site_id');
            $fdd->setType(FieldDescription::FIELD_TYPE_STRING);
            $this->getData()->addField($fd);
            $this->getDataDescription()->addFieldDescription($fdd);
            $urlPath =
                    E()->getSiteManager()->getSiteByID(E()->getMap()->getSiteID($this->document->getID()))->base;
            foreach ($f =
                             $this->getData()->getFieldByName('site_id') as $rowID => $siteID) {
                $f->setRowData($rowID, $urlPath);
            }
        }

        if ($this->getData()->getFieldByName('news_date')) {
            foreach ($f =
                             $this->getData()->getFieldByName('news_date') as $rowID => $date) {
                $date = splitDate($date);
                $f->setRowProperty($rowID, 'year', $date['year']);
                $f->setRowProperty($rowID, 'month', $date['month']);
                $f->setRowProperty($rowID, 'day', $date['day']);
            }
        }
    }

    /**
     * Get similar news IDs.
     *
     * @return array|false
     */
    private function getSimilarNewsIDs() {
        $tagIDs = $this->getNewsTagIDs();

        if ($tagIDs) {
            $result = simplifyDBResult($this->dbh->select(
                'SELECT DISTINCT sn.news_id news_id, sn.news_date FROM ' .
                        $this->getTableName() . ' AS sn LEFT JOIN ' .
                        $this->getTableName() . '_tags AS snt ' .
                        ' ON snt.news_id=sn.news_id WHERE snt.tag_id IN (' .
                        implode(',', $tagIDs) . ') ' .
                        ' ORDER BY sn.news_date DESC LIMIT 0,' .
                        intval($this->getParam('limit'))
            ), 'news_id');

            unset($result[array_search($this->newsID, $result)]);
            return $result;
        }
        return false;
    }

    /**
     * Get news tag IDs.
     *
     * @return array
     * */
    private function getNewsTagIDs() {
        $result = simplifyDBResult($this->dbh->select(
            'SELECT * FROM ' . $this->getTableName() .
                    '_tags WHERE news_id=%s', $this->newsID), 'tag_id');
        return $result;
    }
}