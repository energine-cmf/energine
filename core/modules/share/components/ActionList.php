<?php
/**
 * @file
 * ActionList 
 *
 * Contains the definition to:
 * @code
class ActionList;
 * @endcode
 *
 * @author dr.Pavka
 * @copyright Energine 2015
 *
 * @version 1.0.0
 */

namespace Energine\share\components;


use Energine\share\gears\FieldDescription;
use Energine\share\gears\JSONCustomBuilder;
use Energine\share\gears\QAL;

class ActionList extends Grid{
    function __construct($name, array $params = NULL) {
        parent::__construct($name, $params);
        $this->setTableName('share_action_log');
        $this->setOrder(['al_date' => QAL::DESC]);
    }

    protected function view(){
        parent::view();
        $this->getDataDescription()->getFieldDescriptionByName('al_date')->setProperty('outputFormat', '%E');
        $this->getDataDescription()->getFieldDescriptionByName('al_data')->setType(FieldDescription::FIELD_TYPE_CODE);
        $this->getDataDescription()->getFieldDescriptionByName('al_classname')->setProperty('outputFormat', 'translate(%s)');

        $this->getData()->getFieldByName('al_data')->setRowData(0, var_export(unserialize($this->getData()->getFieldByName('al_data')->getRowData(0)), true));
    }

    protected function clear(){
        $this->dbh->modify(QAL::DELETE, $this->getTableName());
        $b = new JSONCustomBuilder();
        $b->setProperty('result', true)->setProperty('mode', 'clear');
        $this->setBuilder($b);
    }

}