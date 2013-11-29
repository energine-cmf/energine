<?php
/**
 * Содержит класс SimilarNews
 *
 * @package energine
 * @subpackage apps
 * @author Andrii A
 * @copyright eggmengroup.com
 */

/**
 *
 * @package energine
 * @subpackage apps
 * @author Andrii A
 */
class SimilarNews extends DBDataSet {

    /**
     * Дефолтное имя компонента, к которому
     * следует биндится.
     *
     * @var string
     * @access private
     */

    const DEFAULT_LINK_TO = 'news';
    /**
     * Разделитель имен тэгов
     *
     * @var string
     * @access private
     */
    const TAG_SEPARATOR = ',';

    /**
     * ИД новостей
     *
     * @var int
     * @access private
     */
    private $newsID;

    /**
     * Компонент, к которому производится
     * bind компонента "Похожие новости"
     *
     * @var Component
     * @access private
     */
    private $cp;


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
     * Перевизначаємо тип builder'а
     *
     * @access protected
     * @return SimpleBuilder
     *
     */
    protected function createBuilder() {
        return new SimpleBuilder();
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
                'limit' => 5,
                'bind' => ''
            ));
        return $result;
    }

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
     * Отримуємо IDs новин, які позначені тими тегами, що й новина з id = $this->newsID
     * @return array or false
     */
    private function getSimilarNewsIDs() {
        $tagIDs = $this->getNewsTagIDs();

        if ($tagIDs) {
            $result = simplifyDBResult($this->dbh->selectRequest(
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
     * Отримуємо теги новини
     * @param int
     * @return array
     * */
    private function getNewsTagIDs() {
        $result = simplifyDBResult($this->dbh->selectRequest(
            'SELECT * FROM ' . $this->getTableName() .
                    '_tags WHERE news_id=%s', $this->newsID), 'tag_id');
        return $result;
    }
}