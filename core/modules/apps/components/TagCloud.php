<?php
/**
 * @file
 * TagCloud
 *
 * It contains the definition to:
 * @code
class TagCloud;
@endcode
 *
 * @author andrii.a
 * @copyright Energine 2012
 *
 * @version 1.0.0
 */
namespace Energine\apps\components;
use Energine\share\components\DBDataSet, Energine\share\gears\SystemException;
/**
 * Show the cloud of tags on the page.
 *
 * @code
class TagCloud;
@endcode
 */
class TagCloud extends DBDataSet {
    /**
     * Tags of parent section.
     */
    const ID_PARENT_FILTER = 'parent';

    /**
     * Tags from the whole site.
     */
    const ID_ALL_FILTER = 'all';

    /**
     * Section ID, from which the tags will be showed.
     * @var int $filterId
     */
    protected  $filterId;

    /**
     * Component, to which TagCloud will be bounded.
     * @var \Energine\share\gears\Component $bindedBlock
     */
    protected  $bindedBlock;

    /**
     * Array that holds tag IDs and their weight.
     * @var array $tagsInfo
     */
    private $tagsInfo;

    /**
     * @copydoc DBDataSet::__construct
     */
    public function __construct($name, $module, array $params = null) {
        parent::__construct($name, $module, $params);
        $this->setType(self::COMPONENT_TYPE_LIST);
        $this->setTableName('share_tags');
        $this->bindedBlock = $this->document->componentManager->getBlockByName($this->getParam('bind'));
        if (!$this->bindedBlock) {
            throw new SystemException('ERR_TAGCLOUD_NOT_BINDED', SystemException::ERR_DEVELOPER);
        }
        if ($filerId = intval($this->getParam('id'))) {
            $this->filterId = $filerId;
        }
        else if($this->getParam('id') == self::ID_PARENT_FILTER) {
            $map = E()->getMap();
            $this->filterId = $map->getParent($this->document->getID());
            $this->setProperty('template', $map->getURLByID($this->filterId));
        }
        else if($this->getParam('id') != self::ID_ALL_FILTER) {
            $this->filterId = $this->document->getID();
        }
        $this->tagsInfo = $this->getTagsInfo();
        $this->addFilterCondition(array($this->getTableName().'.tag_id' => simplifyDBResult($this->tagsInfo, 'tag_id')));
        $this->addTranslation('TXT_TAGS');
    }

    /**
     * @copydoc DBDataSet::defineParams
     */
    // Добавлен параметр bind
    protected function defineParams() {
        return array_merge(
            parent::defineParams(),
            array(
                'id' => false,
                'recordsPerPage' => 50,
                'bind' => ''
            )
        );
    }

    /**
     * @copydoc DBDataSet::createData
     */
    // Добавлена проперти с частотой появления тега.
    protected function createData() {
        $result = parent::createData();
        if (is_array($this->tagsInfo)) {
            $tagsFrq = array();
            foreach ($this->tagsInfo as $tag) {
                $tagsFrq[$tag['tag_id']] = $tag['frq'];
            }
            $f = $result->getFieldByName('tag_id');
            for ($i = 0; $i < $result->getRowCount(); $i++) {
                $f->setRowProperty($i, 'frequency', $tagsFrq[$f->getRowData($i)]);
            }
        }
        return $result;
    }

    /**
     * Get tags information.
     * This is tag IDs and their frequency of occurrence.
     *
     * @return array
     */
    protected function getTagsInfo() {
        $smapFilter = '';
        if(!empty($this->filterId)) {
            $smapFilter = ' AND ct.smap_id = ' . $this->filterId;
        }
        $result = $this->dbh->select('SELECT COUNT(ctl.' . $this->bindedBlock->getPK() . ') as frq,ctl.tag_id FROM '
                . $this->bindedBlock->getTableName()
                . '_tags ctl INNER JOIN ' . $this->bindedBlock->getTableName() . ' ct '
                . 'WHERE ctl.' . $this->bindedBlock->getPK() . ' = ct.' . $this->bindedBlock->getPK()
                .  $smapFilter
                . ' GROUP BY tag_id');
        return $result;
    }
}