<?php
/**
 * @file
 * QueryResult
 *
 * Contains the definition to:
 * @code
class QueryResult;
 * @endcode
 *
 * @author dr.Pavka
 * @copyright Energine 2015
 *
 * @version 1.0.0
 */

namespace Energine\share\gears;


use Traversable;

class QueryResult implements \IteratorAggregate, \ArrayAccess {
    private $data = [];
    private $query = '';

    public function setSource(\PDOStatement $pdo){
        $this->query = $pdo->queryString;
    }
    public function getQuery() {
        return $this->query;
    }


    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Retrieve an external iterator
     * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Traversable An instance of an object implementing <b>Iterator</b> or
     * <b>Traversable</b>
     */
    public function getIterator() {
        return (!empty($this->data)) ? new \ArrayIterator($this->data) : new \EmptyIterator;
    }


    /**
     * Whether or not an data exists by key
     *
     * @param string An data key to check for
     * @access public
     * @return boolean
     * @abstracting ArrayAccess
     */
    public function __isset($key) {
        return isset($this->data[$key]);
    }


    /**
     * Assigns a value to the specified offset
     *
     * @param mixed The offset to assign the value to
     * @param mixed  The value to set
     * @access public
     * @abstracting ArrayAccess
     */
    public function offsetSet($offset, $value) {
        if (is_null($offset)) {
            $this->data[] = $value;
        } else {
            $this->data[$offset] = $value;
        }
    }

    /**
     * Whether or not an offset exists
     *
     * @param mixed An offset to check for
     * @access public
     * @return boolean
     * @abstracting ArrayAccess
     */
    public function offsetExists($offset) {
        return isset($this->data[$offset]);
    }

    /**
     * Unsets an offset
     *
     * @param mixed The offset to unset
     * @access public
     * @abstracting ArrayAccess
     */
    public function offsetUnset($offset) {
        if ($this->offsetExists($offset)) {
            unset($this->data[$offset]);
        }
    }

    /**
     * Returns the value at specified offset
     *
     * @param mixed The offset to retrieve
     * @access public
     * @return mixed
     * @abstracting ArrayAccess
     */
    public function &offsetGet($offset) {
        return $this->data[$offset];//$this->offsetExists($offset) ? $this->data[$offset] : null;
    }

    public function map(\Closure $callback) {
        array_walk($this->data, $callback);
    }
}