<?php
/**
 * @file
 * Vote
 *
 * It contains the definition to:
 * @code
class Vote;
@endcode
 *
 * @author andrii a
 * @copyright Energine 2013
 *
 * @version 1.0.0
 */
namespace Energine\apps\components;
use Energine\share\components\DataSet, Energine\share\gears\EmptyBuilder, Energine\share\gears\SimpleBuilder, Energine\share\gears\AbstractBuilder, Energine\share\gears\FieldDescription, Energine\share\gears\Data, Energine\share\gears\DataDescription;
/**
 * Voting by discussions.
 *
 * @code
class Vote;
@endcode
 */
class Vote extends DataSet {
    //todo VZ: Used?
    /**
     * Vote ID.
     * @var int $voteID
     */
    private $voteID;

    /**
     * Vote prefix for cookie.
     */
    const VOTED_COOKIE_PREFIX = 'nrgn_voted_';

    /**
     * Cookie lifespan.
     * 24 hours.
     */
    const COOKIE_LIFETIME = 86400;

    /**
     * @copydoc DataSet::__construct
     */
    public function __construct($name, $module, array $params = null) {
        $params['active'] = true;
        parent::__construct($name, $module, $params);
        $this->setProperty('recordsPerPage', false);
    }

    /**
     * @copydoc DataSet::main
     */
    protected function main() {
        if ($voteId
            = $this->dbh->getScalar('SELECT vote_id FROM apps_vote WHERE vote_is_active ORDER BY vote_date DESC LIMIT 0,1')
        ) {
            $this->setProperty('vote_id', $voteId);
            $this->setBuilder(new EmptyBuilder());
            $this->js = $this->buildJS();
        } else {
            $this->disable();
        }
    }

    /**
     * @copydoc DataSet::createBuilder
     */
    protected function createBuilder() {
        return new SimpleBuilder();
    }

    /**
     * Get vote.
     */
    protected function getVote() {

        $sp = $this->getStateParams(true);
        $voteID = (int)$sp['vid'];
        $this->prepare();
        if ($this->isUserCanVote($voteID)) {
            $this->getData()->load($this->dbh->select('SELECT vote_question_id, vote_question_title  FROM `apps_vote_question` LEFT JOIN apps_vote_question_translation USING(vote_question_id)
        WHERE lang_id=%s AND vote_id= %s ORDER BY vote_question_order_num', $this->document->getLang(), $voteID));
            $this->setProperty('question', $this->dbh->getScalar('apps_vote_translation', 'vote_name', array('vote_id' => $voteID, 'lang_id' => $this->document->getLang())));
            $this->setProperty('date', AbstractBuilder::enFormatDate($this->dbh->getScalar('apps_vote', 'vote_date', array('vote_id' => $voteID)), '%E'));
            $this->setProperty('count', $this->dbh->getScalar   ('apps_vote_question', 'SUM(vote_question_counter', array('vote_id' => $voteID)));
            $this->setProperty('canVote', 1);
        } else {
            $this->setProperty('canVote', 0);
            $this->getVoteResults($voteID);
        }


    }

    /**
     * Check if the user can vote.
     *
     * @param int|string $voteID Vote ID.
     * @return bool
     */
    private function isUserCanVote($voteID) {
        $code = true;
//        if ($uid = $this->document->getUser()->getID()) {
            if (isset($_COOKIE[self::VOTED_COOKIE_PREFIX . $voteID])) {
                $code = false;
            }
        /*} else {
            $code = false;
        }*/
        return $code;
    }

    /**
     * Vote.
     */
    protected function vote() {
        $sp = $this->getStateParams(true);
        $QID = (int)$sp['qid'];
        $voteID = $this->dbh->getScalar('apps_vote_question', 'vote_id', array('vote_question_id' => $QID));

        if ($this->isUserCanVote($voteID)) {
            $this->dbh->modify('UPDATE apps_vote_question SET vote_question_counter = vote_question_counter+1 WHERE vote_question_id=%s', $QID);
            $this->response->addCookie(self::VOTED_COOKIE_PREFIX . $voteID, 'voted', time() + self::COOKIE_LIFETIME, E()->getSiteManager()->getCurrentSite()->host);
        }
        $this->setProperty('canVote', 0);
        $this->getVoteResults($voteID);
    }

    /**
     * Get vote result.
     * @param int|string $voteID Vote ID.
     */
    private function getVoteResults($voteID) {
        $this->setProperty('count', $counter = $this->dbh->getScalar('apps_vote_question', 'SUM(vote_question_counter)', array('vote_id' => $voteID)));
        $this->prepare();
        $data = $this->dbh->select('SELECT vote_question_id, vote_question_title, ROUND(100*vote_question_counter/%s) as percent FROM `apps_vote_question` LEFT JOIN apps_vote_question_translation USING(vote_question_id) WHERE lang_id=%s AND vote_id= %s ORDER BY vote_question_order_num', $counter, $this->document->getLang(), $voteID);

        if ($data && is_array($data))
            $this->getData()->load($data);
        $this->setProperty('question', $this->dbh->getScalar('apps_vote_translation', 'vote_name', array('vote_id' => $voteID, 'lang_id' => $this->document->getLang())));
        $this->setProperty('date', AbstractBuilder::enFormatDate($this->dbh->getScalar('apps_vote', 'vote_date', array('vote_id' => $voteID)), '%E'));
        $fd = new FieldDescription('percent');
        $fd->setType(FieldDescription::FIELD_TYPE_INT);
        $this->getDataDescription()->addFieldDescription($fd);
    }

    /**
     * @copydoc DataSet::prepare
     */
    protected function prepare() {
        $data = new Data();
        $dataDescription = new DataDescription();
        $dataDescription->load(
            array(
                'vote_question_id' => array(
                    'key' => true,
                    'nullable' => false,
                    'type' => FieldDescription::FIELD_TYPE_INT,
                    'length' => 10,
                    'index' => 'PRI'
                ),
                'vote_question_title' => array(
                    'key' => false,
                    'nullable' => false,
                    'type' => FieldDescription::FIELD_TYPE_STRING,
                    'length' => 255,
                    'index' => false
                )
            )
        );
        $this->setData($data);
        $this->setDataDescription($dataDescription);
        E()->getController()->getTransformer()->setFileName('../../../../core/modules/apps/transformers/single_vote.xslt');
        $this->setBuilder($this->createBuilder());
    }
}