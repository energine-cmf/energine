<?php
/**
 * @file
 * VoteQuestionEditor
 *
 * It contains the definition to:
 * @code
class VoteQuestionEditor;
@endcode
 *
 * @author andrii a
 * @copyright Energine 2013
 *
 * @version 1.0.0
 */
namespace Energine\apps\components;
use Energine\share\components\Grid;
/**
 * Vote question editor.
 *
 * @code
class VoteQuestionEditor;
@endcode
 */
class VoteQuestionEditor extends Grid {
    /**
     * @copydoc Grid::__construct
     */
    // На вход параметром получаем ID голосования, к которому следует привязать вариант ответа.
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
     * @copydoc Grid::defineParams
     */
    // добавлен параметр voteID - ид голосования
    protected function defineParams() {
        return array_merge(
            parent::defineParams(),
            array(
                'voteID' => false,
            )
        );
    }


    /**
     * @copydoc Grid::loadDataDescription
     *
     */
    // Значение для филда vote_id устанавливается компонентом VoteEditor.
    protected function loadDataDescription() {
        $result = parent::loadDataDescription();
        if (in_array($this->getState(), array('add', 'edit', 'save'))) {
            unset($result['vote_id']);
        }
        return $result;
    }

    /**
     * @copydoc Grid::add
     */
    protected function add() {
        parent::add();
        $this->getData()->getFieldByName('vote_question_counter')->setData(0, true);
    }
}