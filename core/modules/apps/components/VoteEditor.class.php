<?php
/**
 * Содержит класс VoteEditor
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
class VoteEditor extends Grid {

    /**
     * @var VoteQuestionEditor
     */
    private $qEditor;

    public function __construct($name, $module, array $params = null) {
        parent::__construct($name, $module, $params);
        $this->setTableName('apps_vote');
    }

    /**
     * Делаем голосование активным по умолчанию
     */
    protected function add() {
        parent::add();
        $this->getData()->getFieldByName('vote_is_active')->setData(1, true);
    }

    /**
     * Создаем компонент для редактирования ответов
     * на вопрос.
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

    public function build() {
        if ($this->getState() == 'questionEditor') {
            $result = $this->qEditor->build();
        } else {
            $result = parent::build();
        }

        return $result;
    }

    /**
     * Привязывем все варианты ответов с
     * vote_id = NULL к текущему опросу.
     *
     * @return mixed
     */
    protected function saveData() {
        $voteID = parent::saveData();
        $this->dbh->modify('UPDATE apps_vote_question SET vote_id=%s WHERE (vote_id IS NULL) or (vote_id = %1$s)', $voteID);
        return $voteID;
    }
}