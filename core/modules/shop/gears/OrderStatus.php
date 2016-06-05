<?php
/**
 * @file
 * OrderStatus
 *
 * Contains the definition to:
 * @code
class OrderStatus;
 * @endcode
 *
 * @author dr.Pavka
 * @copyright Energine 2015
 *
 * @version 1.0.0
 */

namespace Energine\shop\gears;


use Energine\share\gears\DBWorker;
use Energine\share\gears\Primitive;

class OrderStatus extends Primitive
{
    use DBWorker;
    private $statuses;

    /**
     * OrderStatus constructor.
     */
    public function __construct()
    {
        $res = $this->dbh->select('SELECT * FROM shop_order_statuses s LEFT JOIN shop_order_statuses_translation st USING(status_id) WHERE lang_id=%s ORDER BY status_order_num', E()->getLanguage()->getCurrent());

        foreach ($res as $row) {
            $this->statuses[$row['status_sysname']] = new OrderStatusItem($row);
        }

    }
    public function getInitial(){
        $result = $this->statuses;
        return array_shift($result);
    }
    public function getUnFinished(){
        return [$this->statuses['new'], $this->statuses['valid']];
    }

    public function getValid(){
        return $this->statuses['valid'];
    }
}

class OrderStatusItem extends Primitive {
    private $data;
    /**
     * OrderStatusItem constructor.
     */
    public function __construct($row)
    {
        unset($row['lang_id'], $row['status_order_num']);
        $this->data = E()->Utils->convertFieldNames($row, 'status');
    }

    function __toString()
    {
        return (string)$this->data['Id'];
    }


}