<?php
/**
 * Содержит класс Feed
 *
 * @package energine
 * @subpackage share
 * @author dr.Pavka
 * @copyright Energine 2007
 * @version $Id$
 */

/**
 * Абстрактный класс предок для компонентов основывающихся на структуре сайта
 *
 * @package energine
 * @subpackage share
 * @author dr.Pavka
 */
class Feed extends DBDataSet
{
    /**
     * @var array | int
     * фильтр по идентификаторам
     */
    protected $filterID;

    /**
     * Конструктор класса
     *
     * @param string $name
     * @param string $module

     * @param array $params
     * @access public
     */
    public function __construct($name, $module, array $params = null)
    {
        parent::__construct($name, $module, $params);
        $this->setProperty('title', $this->translate(
            'TXT_' . strtoupper($this->getName())));
        $this->setProperty('exttype', 'feed');
        $this->setParam('onlyCurrentLang', true);
        if ($this->getParam('editable') && $this->document->isEditable()) {
            $this->setProperty('editable', 'editable');
        }
        if ($this->getState() == 'main') {
            if (!($this->filterID = $this->getParam('id'))) {
                $this->filterID = $this->document->getID();
            }

            if ($this->getParam('showAll')) {
                $descendants = array_keys(
                    E()->getMap()->getTree()->getNodeById($this->filterID)->getDescendants()->asList(false)
                );
                $this->filterID = array_merge(array($this->filterID), $descendants);
            }
            $this->addFilterCondition(array('smap_id' => $this->filterID));
            if ($limit = $this->getParam('limit')) {
                $this->setLimit(array(0, $limit));
                $this->setParam('recordsPerPage', false);
            }
        }
    }

    /**
     * Убираем smap_id
     *
     * @access protected
     * @return DataDescription
     */
    protected function createDataDescription()
    {
        $result = parent::createDataDescription();
        if ($smapField = $result->getFieldDescriptionByName('smap_id')) {
            $result->removeFieldDescription($smapField);
        }

        return $result;
    }

    /**
     * Добавляем крошку
     *
     * @return void
     * @access protected
     */

    protected function view()
    {
        parent::view();
        $this->addFilterCondition(array('smap_id' => $this->document->getID()));
        $this->document->componentManager->getBlockByName('breadCrumbs')->addCrumb();
    }

    /**
     * Делаем компонент активным
     *
     * @return array
     * @access protected
     */

    protected function defineParams()
    {
        return array_merge(
            parent::defineParams(),
            array(
                 'active' => true,
                 'showAll' => false,
                 'id' => false,
                 'limit' => false,
                 'editable' => false
            )
        );
    }
}