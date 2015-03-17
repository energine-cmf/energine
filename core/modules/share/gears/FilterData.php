<?php
/**
 *
 */
namespace Energine\share\gears;

/**
 * Class FilterFieldGroup
 * @package Energine\share\gears
 * @param FilterFieldGroup []|FilterField[]
 */
class FilterData implements \Iterator {
    /**
     * @var FilterData[]|FilterField[]
     */
    private $children = [];
    private $operator = 'OR';
    private $index =0 ;

    private function __construct() {

    }

    /**
     * @param $data
     * @return array
     */
    static private function clearPOSTData($data) {
        $result = [];
        foreach ($data as $key => $value) {
            if (!is_null($value)) {
                if (is_array($value)) {
                    $value = self::clearPOSTData($value);
                }
                $result[$key] = $value;
            }
        }

        return $result;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Return the current element
     * @link http://php.net/manual/en/iterator.current.php
     * @return FilterField
     */
    public function current() {
        return $this->children[$this->index];
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Move forward to next element
     * @link http://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     */
    public function next() {
        $this->index++;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Return the key of the current element
     * @link http://php.net/manual/en/iterator.key.php
     * @return mixed scalar on success, or null on failure.
     */
    public function key() {
        return $this->index;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Checks if current position is valid
     * @link http://php.net/manual/en/iterator.valid.php
     * @return boolean The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     */
    public function valid() {
        return isset($this->children[$this->index]);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Rewind the Iterator to the first element
     * @link http://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     */
    public function rewind() {
        $this->index = 0;
    }

    /**
     * @param $child
     * @return int
     */
    public function add($child) {
        return array_push($this->children, $child);
    }

    /**
     * @return FilterData|null
     */
    public static function createFromPOST() {
        if (
            !isset($_POST[Filter::TAG_NAME])
            ||
            !($result = json_decode($_POST[Filter::TAG_NAME], true))

        ) return null;

        return FilterData::createFrom(self::clearPOSTData($result));
    }

    /**
     * @param $data
     * @return FilterData
     */
    public static function createFrom($data) {
        $result = new FilterData();
        if (isset($data['children']) && is_array($data['children'])) {
            foreach ($data['children'] as $child) {
                if (array_key_exists('children', $child)) {
                    $child = new FilterData($child);
                } else {
                    $child = FilterField::createFrom($child);
                }
                $result->add($child);
            }
        }
        if (isset($data['operator'])) {
            $result->setOperator($data['operator']);
        }
        return $result;
    }

    /**
     * @param $operator
     */
    public function setOperator($operator) {
        $operator = strtoupper($operator);

        if (in_array($operator, ['OR', 'AND']))
            $this->operator = $operator;
    }

    /**
     * return filter conditions as SQL string
     *
     * @return string
     */
    public function __toString() {
        $result = array_reduce($this->children, function ($result, $filter) {
            return $result . ' (' . (string)$filter . ') ' . $this->operator;
        });

        return (string)substr($result, 0, -sizeof($this->operator)-2);
    }
}