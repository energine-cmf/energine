<?php
/**
 * Содержит класс TagCloud
 *
 * @package energine
 * @subpackage apps
 * @author andrii.a
 * @copyright Energine 2012
 * @version $Id$
 */

/**
 * Содержит класс, позволяющий вывести облако тегов на страницу
 *
 * @package energine
 * @subpackage apps
 * @author andrii.a
 */
class TagCloud extends DBDataSet {

    /**
     * Вывод тегов для родительского раздела
     */
    const ID_PARENT_FILTER = 'parent';

    /**
     * Вывод тегов со всего сайта
     */
    const ID_ALL_FILTER = 'all';

    /**
     * Ид раздела, с которого
     * будут выводиться теги
     *
     * @var int
     */
    protected  $filterId;

    /**
     * Компонент, к которому привязывается
     * TagCloud
     *
     * @var Component
     */
    protected  $bindedBlock;

    /**
     * Массив, содержащий ИД тагов
     * и информацию о их "весе"
     *
     * @var array
     */
    private $tagsInfo;

    /**
     * @param string $name
     * @param string $module
     * @param array $params
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
     * Добавлен
     * параметр bind
     *
     * @return array
     * @access protected
     */
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
     * Добавлена проперти с частотой появления
     * тега.
     *
     * @return Data
     * @access protected
     */
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
     * Возвращает информацию о ИД тегов
     * и частоте их появления.
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