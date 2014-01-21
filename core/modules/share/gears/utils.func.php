<?php
/**
 * @file
 * Utilities.
 * It contain the set of service utilities of the system.
 *
 * @author 1m.dm
 * @copyright Energine 2006
 *
 * @version 1.0.0
 */


/**
 * @fn inspect()
 * @brief Inspect variables.
 * It directly prints the information about input arguments to the output stream formatted with HTML.
 */
function inspect() {
    $args = func_get_args();
    if(php_sapi_name() != 'cli'){
        echo '<pre>';
        call_user_func_array('var_dump', $args);
        echo '</pre>';
    }
    else {
        print(PHP_EOL);
        call_user_func_array('var_dump', $args);
        print(PHP_EOL);
    }

}


/**
 * @fn splitDate($date)
 * @brief Split date.
 * It splits the date into the year, month, month day and time (hours, minutes, seconds).
 *
 * @param string $date Date.
 * @return array
 */
function splitDate($date) {
    $timeInfo =
        $dateInfo = array('','','');
    $dateArray = explode(' ',$date);
    if(is_array($dateArray)){
        $dateInfo = explode('-',$dateArray[0]);
        if(isset($dateArray[1])){
            $timeInfo = explode(':',$dateArray[1]);
        }
    }
    return array(
                'year' => $dateInfo[0],
                'month' => $dateInfo[1],
                'day' => $dateInfo[2],
                'time' => array(
                    'h' => $timeInfo[0],
                    'm' => $timeInfo[1],
                    's' => $timeInfo[2]
                )
            );
}

/**
 * @fn stop()
 * @brief Terminate program.
 * This is an analogue of inspect() with program termination.
 */
function stop() {
    $args = func_get_args();
    call_user_func_array('inspect', $args);
    die();
}

/**
 * @fn simple_log($var)
 * @brief Simple log function.
 * @param string $var Variable.
 */
function simple_log($var){
	static $simpleLog;
	if(!isset($simpleLog)){
		$simpleLog = 'logs/simple.log';
		file_put_contents($simpleLog,'');
	}
	
	if(file_exists($simpleLog)){
	   $flag = 	FILE_APPEND;
	}
	$flag = (file_exists($simpleLog))? FILE_APPEND : null;
	file_put_contents(
	   $simpleLog,
	   str_replace("\n", ' ', $var)."\n",
	   $flag
	);
}

/**
 * @fn dump_log($var, $append = false)
 * @brief Write the variable information into log file.
 *
 * @param mixed $var Variable
 * @param boolean $append Append the log into the file? If @c false the file will be overwritten.
 */
function dump_log($var, $append = false) {
    $flags = ($append ? FILE_APPEND : null);
    ob_start();
    var_dump($var);
    $data = ob_get_contents();
    ob_end_clean();
    file_put_contents(
        'logs/debug.log',
        "\ndate: ".date('l dS of F Y h:i:s')."\n\n".$data."\n",
        $flags
    );
    chmod('logs/debug.log', 0666);
}

/**
 * @fn ddump_log()
 * @brief Write the variable information into log file and terminate program.
 * Log-file will be overwritten.
 */
function ddump_log() {
    $result = array();
    $args = func_get_args();
    foreach ($args as $arg) {
    	$result[] = var_export($arg, true);
    }
    file_put_contents(
        'logs/debug.log',
        "\ndate: ".date("l dS of F Y h:i:s")."\n\n".implode("\n", $result)."\n"
    );
    chmod('logs/debug.log', 0666);
    E()->getResponse()->commit();
}


/**
 * @fn simplifyDBResult($dbResult, $fieldName, $singleRow = false)
 * @brief Simplify data base result.
 * It selects the values at the defined field from the result of SELECT request.
 *
 * @param mixed $dbResult Result of SELECT request.
 * @param string $fieldName Field name that will be selected from result.
 * @param boolean $singleRow Return only the first value at defined field name?
 * @return mixed
 */
function simplifyDBResult($dbResult, $fieldName, $singleRow = false) {
    $result = false;
    $fieldName = strtolower($fieldName);
    if (is_array($dbResult)) {
        if ($singleRow) {
            $result = $dbResult[0][$fieldName];
        }
        else {
            foreach ($dbResult as $row) {
                if (array_key_exists($fieldName, $row)) {
                    $result[] = $row[$fieldName];
                }
            }
        }
    }
    return $result;
}

//todo VZ: This is called 'Transpose'. There is also not necessary to bind the function name with DB, it has general implementation.
/**
 * @fn inverseDBResult(array $dbResult)
 * @brief Transpose 2D array.
 *
 * Input:
 * @code
array(
    $n => array(
        $fieldName => $fieldValue
    )
)
@endcode
 *
 * Output:
 *     array($fieldName => array($n => $fieldValue))
 * @code
array(
    $fieldName => array(
        $n => $fieldValue
    )
)
@endcode
 *
 * @param array $dbResult 2D array.
 * @return array
 */
function inverseDBResult(array $dbResult) {
    $result = array();
    foreach ($dbResult as $row) {
        foreach ($row as $fieldName => $fieldValue) {
            $result[$fieldName][] = $fieldValue;
        }
    }
    return $result;
}

/**
 * @fn convertDBResult($dbResult, $pkName, $deletePK = false)
 * @brief Convert data base result.
 *
 * @param mixed $dbResult Data base result.
 * @param mixed $pkName Primary key.
 * @param boolean $deletePK Delete fields with primary key from the result?
 * @return array
 *
 * @todo написать подробное описание!
 *
 * @see QAL::select()
 *
 * @throws SystemException 'ERR_DEV_BAD_DATA'
 */
function convertDBResult($dbResult, $pkName, $deletePK = false) {
    $result = false;
    if (is_array($dbResult) && !empty($dbResult)) {
        if (is_string($pkName)) {
            foreach ($dbResult as $key => $row) {
                if (isset($row[$pkName])) {
                    $result[$row[$pkName]] = $row;
                    if ($deletePK) {
                        unset($result[$row[$pkName]][$pkName]);
                    }
                }
                else {
                    throw new SystemException('ERR_DEV_BAD_DATA', SystemException::ERR_DEVELOPER);
                }
            }
        }
        elseif (is_array($pkName) && sizeof($pkName) == 2) {
            foreach ($dbResult as $key => $row) {
                $result[$row[$pkName[0]]][$row[$pkName[1]]] = $row;
                if ($deletePK) {
                    unset($result[$row[$pkName[0]]][$row[$pkName[1]]][$pkName[0]]);
                    unset($result[$row[$pkName[0]]][$row[$pkName[1]]][$pkName[1]]);
                }
            }
        }
    }
    return $result;
}

/**
 * @fn convertFieldNames(array $fields, $prefix = '')
 * @brief Convert field names to Camel Notation.
 *
 * @param array $fields
 * @param string $prefix Prefix, that should be removed from the name.
 * @return array
 */
function convertFieldNames(array $fields, $prefix = '') {
    $result = array();
    foreach ($fields as $fieldName => $fieldValue) {
        if (($plen = strlen($prefix)) > 0 && strpos($fieldName, $prefix) === 0) {
            $fieldName = substr($fieldName, $plen);
        }
        //$fieldName = preg_replace('/_(\w)/e', 'strtoupper(\'$1\')', $fieldName);
        $fieldName = preg_replace_callback('/_(\w)/', function($m){ return strtoupper($m[1]);}, $fieldName);

        $result[$fieldName] = $fieldValue;
    }
    return $result;
}

//todo VZ: I think the using of $key is bad.
/**
 * @fn arrayPush(array &$array, $var, $key = null)
 * @brief Push the new array element at the end of the array.
 * The index of newly inserted element will be returned.
 *
 * @param array $array Array.
 * @param mixed $var New array element
 * @param int $key Desired key value of the new element.
 * @return int
 *
 * @see array_push()
 */
function arrayPush(array &$array, $var, $key = null) {
    $newkey = 0;
    $keys = array_keys($array);
    if (!empty($keys)) {
        if (is_null($key)) {
            rsort($keys);
            $newkey = $keys[0] + 1;
        }
        else {
            $newkey = $key;
        }
    }
    $array[$newkey] = $var;
    return $newkey;
}

/**
 * @fn array_push_before(array $array, $var, $pos)
 * @brief Push the new array element before specific position.
 *
 * @param array $array Array.
 * @param mixed $var New array element.
 * @param int|string $pos Position.
 * @return array
 */
function array_push_before(array $array, $var, $pos) {
    $result = array();
    if (!is_array($var)) {
        $var = array($var);
    }
    if (is_int($pos)) {
        $result = array_merge(
            array_slice($array, 0, $pos),
            $var,
            array_slice($array, $pos)
        );
    }
    else {
        foreach ($array as $key => $value){
            if ($key == $pos) {
                $result = array_merge($result, $var);
            }
            $result[$key] = $value;
        }
    }
    return $result;
}
/**
 * @fn array_push_after($src,$in,$pos)
 * @brief Push the new array element after specific position.
 *
 * @param array $src Array.
 * @param mixed $in New array element.
 * @param int|string $pos Position.
 * @return array
*/
function array_push_after($src,$in,$pos){
    if(is_int($pos)) $R=array_merge(array_slice($src,0,$pos+1), $in, array_slice($src,$pos+1));
    else{
        foreach($src as $k=>$v){
            $R[$k]=$v;
            if($k==$pos)$R=array_merge($R,$in);
        }
    }return $R;
}

/*function is_assoc($array) {
    return (is_array($array) && (0 !== count(array_diff_key($array, array_keys(array_keys($array)))) || count($array)==0));
}*/
/**
 * @fn file_get_contents_stripped($fileName)
 * @brief Get stripped contents.
 * @param string $fileName Filename.
 * @return string
 */
function file_get_contents_stripped($fileName){
    return stripslashes(trim(file_get_contents($fileName)));
}