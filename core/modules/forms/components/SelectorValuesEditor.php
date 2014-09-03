<?php
/**
 * @file
 * SelectorValuesEditor, getRealPOST()
 *
 * It contains the definition to:
 * @code
class SelectorValuesEditor;
function getRealPOST();
@endcode
 *
 * @author d.pavka
 * @copyright d.pavka@gmail.com
 *
 * @version 1.0.0
 */
namespace Energine\forms\components;
use Energine\share\components\Grid;
/**
 * Editor of selector values.
 *
 * @code
class SelectorValuesEditor;
@endcode
 *
 * @note It is used from FormEditor.
 */
class SelectorValuesEditor extends Grid {
    /**
     * @copydoc Grid::__construct
     */
    public function __construct($name, $module, array $params = null) {
        /*
         * финт ушами связанный с заменой PHP
         * "." на "_"
         * поэтому мы получаем POST по хитрому
         */

        $_POST = getRealPOST();
        parent::__construct($name, $module, $params);
        $this->setTableName($this->getParam('table_name'));
        //stop(file_get_contents("php://input"), $_POST, $_POST[$this->getTableName()]);
    }

    /**
     * @copydoc Grid::defineParams
     */
    protected function defineParams() {
        return array_merge(
            parent::defineParams(),
            array(
                 'table_name' => false
            )
        );
    }
    
}

/**
 * @fn getRealPOST()
 * @brief Get real POST data.
 * @return array|bool
 *
 * @note Under "real" data means that variable names have point instead of underscore.
 */
function getRealPOST() {
    //todo VZ: For what are this functions?
    /*
     * @param $result
     * @param $k
     * @param $arrayKeys
     * @param $value
     * @return string
     */
    function parse_query_string_array(&$result, $k, $arrayKeys, $value) {
        $value = urldecode($value);
        if (!preg_match_all('/\[([^\]]*)\]/', $arrayKeys, $matches))
            return $value;
        if (!isset($result[$k])) {
            $result[urldecode($k)] = array();
        }
        $temp =& $result[$k];
        $last = urldecode(array_pop($matches[1]));
        foreach ($matches[1] as $k) {
            $k = urldecode($k);
            if ($k === "") {
                $temp[] = array();
                $temp =& $temp[count($temp) - 1];
            } else if (!isset($temp[$k])) {
                $temp[$k] = array();
                $temp =& $temp[$k];
            }
        }
        if ($last === "") {
            $temp[] = $value;
        } else {
            $temp[urldecode($last)] = $value;
        }
    }
    /*
     * @param  $a
     * @param string $delim
     * @param bool $default
     * @return bool|string
     */
    function string_pair(&$a, $delim = '.', $default = false) {
        $n = strpos($a, $delim);
        if ($n === false)
            return $default;
        $result = substr($a, $n + strlen($delim));
        $a = substr($a, 0, $n);
        return $result;
    }
    /*
     * @param  $url
     * @param bool $qmark
     * @return array|bool
     */
    function parse_query_string($url, $qmark = false) {
        if ($qmark) {
            $pos = strpos($url, "?");
            if ($pos !== false) {
                $url = substr($url, $pos + 1);
            }
        }
        if (empty($url))
            return false;
        $tokens = explode("&", $url);
        $urlVars = array();

        foreach ($tokens as $token) {
            $value = string_pair($token, "=", "");
            $token = urldecode($token);
            if (preg_match('/^([^\[]*)(\[.*\])$/', $token, $matches)) {
                parse_query_string_array($urlVars, $matches[1], $matches[2], $value);
            } else {
                $urlVars[$token] = urldecode($value);
            }
        }
        return $urlVars;
    }

    return parse_query_string(file_get_contents("php://input"), false);
}