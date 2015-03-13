<?php
/**
 * @file
 * FeedbackList
 *
 * It contains the definition to:
 * @code
class FeedbackList;
@endcode
 *
 * @author dr.Pavka
 * @copyright Energine 2007
 *
 * @version 1.6.0
 */
namespace Energine\apps\components;
use Energine\share\components\Grid, Energine\share\gears\QAL;

/**
 * List of feedback messages.
 *
 * @code
class FeedbackList;
@endcode
 */
class FeedbackList extends Grid {
    /**
     * @copydoc Grid::__construct
     */
    public function __construct($name, $module, array $params = null) {
        parent::__construct($name, $module, $params);
        $this->setOrder(array('feed_date' => QAL::DESC));
    }

    /**
     * @copydoc Grid::defineParams
     */
    protected function defineParams() {
        $result = array_merge(
            parent::defineParams(),
            array(
                 'tableName' => 'apps_feedback',
            )
        );
        return $result;
    }
}