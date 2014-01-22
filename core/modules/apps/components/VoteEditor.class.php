<?php
/**
 * @file
 * VoteEditor
 *
 * It contains the definition to:
 * @code
class VoteEditor;
@endcode
 *
 * @author andrii a
 * @copyright Energine 2013
 *
 * @version 1.0.0
 */

/**
 * Vote editor.
 *
 * @code
class VoteEditor;
@endcode
 */
class VoteEditor extends Grid {
    /**
     * Question editor.
     * @var VoteQuestionEditor $qEditor
     */
    private $qEditor;

    /**
     * @copydoc Grid::__construct
     */
    public function __construct($name, $module, array $params = null) {
        parent::__construct($name, $module, $params);
        $this->setTableName('apps_vote');
    }

    /**
     * @copydoc Grid::add
     */
    // Делаем голосование активным по умолчанию
    protected function add() {
        parent::add();
        $this->getData()->getFieldByName('vote_is_active')->setData(1, true);
    }

    /**
     * Create component for editing the answers to the question.
     */
    protected function questionEditor() {
        $sp = $this->getStateParams(true);
        $params = array('config' => 'core/modules/apps/config/VoteQuestionEditor.component.xml');

        if (isset($sp['vote_id'])) {
            $this->request->shiftPath(2);
            $params['voteID'] = $sp['vote_id'];

        } else {
            $this->request->shiftPath(1);
        }
        $this->qEditor = $this->document->componentManager->createComponent('qEditor', 'apps', 'VoteQuestionEditor', $params);
        $this->qEditor->run();
    }

    /**
     * @copydoc Grid::build
     */
    public function build() {
        if ($this->getState() == 'questionEditor') {
            $result = $this->qEditor->build();
        } else {
            $result = parent::build();
        }

        return $result;
    }

    /**
     * @copydoc Grid::saveData
     */
    // Привязывем все варианты ответов с vote_id = NULL к текущему опросу.
    protected function saveData() {
        $voteID = parent::saveData();
        $this->dbh->modify('UPDATE apps_vote_question SET vote_id=%s WHERE (vote_id IS NULL) or (vote_id = %1$s)', $voteID);
        return $voteID;
    }
}