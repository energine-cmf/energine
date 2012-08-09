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
     * Ид раздела, с которого
     * будут выводиться теги
     *
     * @var int
     */
    private $filterId;

    /**
     * Компонент, к которому привязывается
     * TagCloud
     *
     * @var Component
     */
    private $bindedBlock;

    /**
     * Массив, содержащий ИД тагов
     * и информацию о их "весе"
     *
     * @var array
     */
    private $tagsInfo;

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
            $this->filterId = E()->getMap()->getParent($this->document->getID());
        }
        else {
            $this->filterId = $this->document->getID();
        }
        $this->tagsInfo = $this->getTagsInfo();
        $this->addFilterCondition(array('tag_id' => simplifyDBResult($this->tagsInfo, 'tag_id')));
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
     * @access private
     */
    private function getTagsInfo() {
        $result = $this->dbh->select('SELECT COUNT(ctl.' . $this->bindedBlock->getPK() . ') as frq,ctl.tag_id FROM '
                . $this->bindedBlock->getTableName()
                . '_tags ctl INNER JOIN ' . $this->bindedBlock->getTableName() . ' ct '
                . 'WHERE ct.smap_id = ' . $this->filterId . ' '
                . 'AND ctl.' . $this->bindedBlock->getPK() . ' = ct.'
                . $this->bindedBlock->getPK() . ' GROUP BY tag_id');
        return $result;
    }
}