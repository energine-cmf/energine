<?php
/**
 * @file
 * DBWorker.
 *
 * @code
abstract class DBWorker;
 * @endcode
 *
 * @author 1m.dm
 * @copyright Energine 2006
 *
 * @version 1.0.0
 */
namespace Energine\share\gears;
/**
 * @property-read QAL $dbh
 *
 * @method string translate()
 * @method string dateToString()
 *
 *
 * Class DBWorker
 * @package Energine\share\gears
 */
trait DBWorker {
    /**
     * @param $name
     * @param $args
     * @return mixed
     *
     * @throws \OutOfBoundsException
     */
    public function __call($name, $args) {
        if (in_array($name, ['translate', 'dateToString'])) {
            return call_user_func_array($name, $args);
        }
        throw \OutOfBoundsException();
    }

    /**
     * @param $name
     * @return \Energine\share\gears\QAL|\QAL
     */
    public function __get($name) {
        if ($name == 'dbh') return E()->getDB();
    }
}
