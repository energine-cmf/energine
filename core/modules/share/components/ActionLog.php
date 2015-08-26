<?php
/**
 * @file
 * ActionLog
 * Contains the definition to:
 * @code
class ActionLog;
 * @endcode
 * @author dr.Pavka
 * @copyright Energine 2015
 * @version 1.0.0
 */

namespace Energine\share\components;


use Energine\share\gears\Data;
use Energine\share\gears\DBWorker;
use Energine\share\gears\Primitive;
use Energine\share\gears\QAL;

class ActionLog extends Primitive {
    use DBWorker;
    private $className = null;
    private $objectName = null;
    private $tableName = 'share_action_log';

    function __construct($className, $objectName) {
        $this->className = $className;
        $this->objectName = $objectName;
    }

    /**
     * @param $type
     * @param $data Data|int
     * @throws \Energine\share\gears\SystemException
     */
    function write($type, $data){
        $this->dbh->modify(QAL::INSERT, $this->tableName, ['u_id' => E()->getUser()->getID(), 'al_classname' => $this->prepareClassNameConst($this->className), 'al_objectname' => $this->objectName, 'al_action' => $type, 'al_data' => serialize($data)]
        );
    }

    private function prepareClassNameConst($className){
        return 'CLASS_'.strtoupper(str_replace('\\', '_', $className));
    }
}