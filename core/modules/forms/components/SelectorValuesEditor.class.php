<?php
/**
 * Содержит класс SelectorValuesEditor
 * и функцию получения реальных _POST данных
 * реальные - имеется ввиду с точками в именах переменных не замененными на _
 *
 * @package energine
 * @subpackage forms
 * @author d.pavka
 * @copyright d.pavka@gmail.com
 */

/**
 * Редатор значений селекта
 * вызывается из
 * @see FormEditor
 * @package energine
 * @subpackage forms
 * @author d.pavka@gmail.com
 */
class SelectorValuesEditor extends Grid {
    private $mainTableName;

    /**
     * Конструктор класса
     *
     * @param string $name
     * @param string $module
     * @param array $params
     * @access public
     */
    public function __construct($name, $module, array $params = null) {
        /**
         * финт ушами связанный с заменой PHP
         * "." на "_"
         * поэтому мы получаем POST по хитрому
         */
        
        $_POST = getRealPOST();
        parent::__construct($name, $module, $params);
        $this->setTableName($this->getParam('table_name'));
    }

    protected function defineParams() {
        return array_merge(
            parent::defineParams(),
            array(
                 'table_name' => false
            )
        );
    }
}


function getRealPOST() {
    function parse_query_string_array(&$result, $k, $arrayKeys, $value) {
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

    function string_pair(&$a, $delim = '.', $default = false) {
        $n = strpos($a, $delim);
        if ($n === false)
            return $default;
        $result = substr($a, $n + strlen($delim));
        $a = substr($a, 0, $n);
        return $result;
    }

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
            if (preg_match('/^([^\[]*)(\[.*\])$/', $token, $matches)) {
                parse_query_string_array($urlVars, $matches[1], $matches[2], $value);
            } else {
                $urlVars[urldecode($token)] = urldecode($value);
            }
        }
        return $urlVars;
    }

    return parse_query_string(file_get_contents("php://input"), false);
}