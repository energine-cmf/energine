<?php
/**
 * Содержит класс VoteQuestionEditor
 *
 * @package energine
 * @subpackage apps
 * @author andrii a
 * @copyright Energine 2013
 */

/**
 * Редактор голосовалки
 *
 * @package energine
 * @subpackage apps
 * @author andrii a
 */
class VoteQuestionEditor extends Grid {

    /**
     * На вход параметром получаем ID голосования,
     * к которому следует привязать вариант ответа.
     *
     * @param string $name
     * @param string $module
     * @param array|null $params
     */
    public function __construct($name, $module, array $params = null) {
        parent::__construct($name, $module, $params);
        $this->setTableName('apps_vote_question');
        $filter = ' (vote_id IS NULL) ';
        if ($this->getParam('voteID')) {
            $filter .= ' OR (vote_id =' . $this->getParam('voteID') . ')';
        }
        $filter = '(' . $filter . ')';

        $this->setFilter($filter);
    }

    /**
     * добавлен параметр voteID - ид голосования
     *
     * @return array
     */
    protected function defineParams() {
        return array_merge(
            parent::defineParams(),
            array(
                'voteID' => false,
            )
        );
    }


    /**
     * Значение для филда vote_id устанавливается
     * компонентом VoteEditor.
     *
     * @return array
     */
    protected function loadDataDescription() {
        $result = parent::loadDataDescription();
        if (in_array($this->getState(), array('add', 'edit', 'save'))) {
            unset($result['vote_id']);
        }
        return $result;
    }

    protected function add() {
        parent::add();
        $this->getData()->getFieldByName('vote_question_counter')->setData(0, true);
    }
}