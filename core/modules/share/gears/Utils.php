<?php
/**
 * @file
 * Contains Class Utils and functions inspect(), splitDate(), stop(), simple_log(), dump_log(), ddump_log(), simplifyDBResult(), inverseDBResult(), convertDBResult(), convertFieldNames(), arrayPush(), array_push_before(), array_push_after().
 * It contain the set of service utilities of the system.
 * @author pavka
 * @copyright Energine 2015
 */
namespace Energine\share\gears {
    /**
     * Class Utils
     * @package Energine\share\gears
     */
    class Utils {
        /**
         * @brief Inspect variables.
         * It directly prints the information about input arguments to the output stream formatted with HTML.
         */
        function inspect() {
            $args = func_get_args();
            ob_start();
            if (!$this->is_PHP_CLI()) {
                echo '<pre>';
                call_user_func_array('var_dump', $args);
                echo '</pre>';
            } else {
                print(PHP_EOL);
                call_user_func_array('var_dump', $args);
                print(PHP_EOL);
            }
            E()->getResponse()->write(ob_get_contents());
            ob_end_clean();
        }

        /**
         * @brief Split date.
         * It splits the date into the year, month, month day and time (hours, minutes, seconds).
         * @param string $date Date.
         * @return array
         */
        function splitDate($date) {
            $timeInfo =
            $dateInfo = ['', '', ''];
            $dateArray = explode(' ', $date);
            if (is_array($dateArray)) {
                $dateInfo = explode('-', $dateArray[0]);
                if (isset($dateArray[1])) {
                    $timeInfo = explode(':', $dateArray[1]);
                }
            }

            return [
                'year'  => $dateInfo[0],
                'month' => $dateInfo[1],
                'day'   => $dateInfo[2],
                'time'  => [
                    'h' => $timeInfo[0],
                    'm' => $timeInfo[1],
                    's' => $timeInfo[2]
                ]
            ];
        }

        /**
         * @brief Simple log function.
         * @param string $var Variable.
         */
        function simpleLog($var) {
            static $simpleLog;
            if (!isset($simpleLog)) {
                $simpleLog = 'logs/simple.log';
                file_put_contents($simpleLog, '');
            }

            if (file_exists($simpleLog)) {
                $flag = FILE_APPEND;
            }
            $flag = (file_exists($simpleLog)) ? FILE_APPEND : null;
            file_put_contents(
                $simpleLog,
                str_replace("\n", ' ', $var) . "\n",
                $flag
            );
        }

        /**
         * @brief Write the variable information into log file.
         * @param mixed $var Variable
         * @param boolean $append Append the log into the file? If @c false the file will be overwritten.
         */
        function dumpLog($var, $append = false) {
            $t = microtime(true);
            $micro = sprintf("%06d", ($t - floor($t)) * 1000000);
            $d = new \DateTime(date('Y-m-d H:i:s.' . $micro, $t));

            $flags = ($append ? FILE_APPEND : null);
            ob_start();
            var_dump($var);
            $data = ob_get_contents();
            ob_end_clean();
            file_put_contents(
                'logs/debug.log',
                "\ndate: " . $d->format('l dS of F Y h:i:s:u') . "\n\n" . $data . "\n",
                $flags
            );
            @chmod('logs/debug.log', 0666);
        }

        /**
         * @brief Write the variable information into log file and terminate program.
         * Log-file will be overwritten.
         */
        function ddumpLog() {
            $result = [];
            $args = func_get_args();
            foreach ($args as $arg) {
                $result[] = var_export($arg, true);
            }
            file_put_contents(
                'logs/debug.log',
                "\ndate: " . date("l dS of F Y h:i:s") . "\n\n" . implode("\n", $result) . "\n"
            );
            chmod('logs/debug.log', 0666);
            E()->getResponse()->commit();
        }


        /**
         * @brief Simplify data base result.
         * It selects the values at the defined field from the result of SELECT request.
         * @see array_column
         * @param mixed $dbResult Result of SELECT request.
         * @param string $fieldName Field name that will be selected from result.
         * @param boolean $singleRow Return only the first value at defined field name?
         * @return mixed
         */
        function simplify($dbResult, $fieldName, $singleRow = false) {
            $result = false;
            $fieldName = strtolower($fieldName);
            if (is_array($dbResult)) {
                if ($singleRow) {
                    $result = $dbResult[0][$fieldName];
                } else {
                    $result = array_column($dbResult, $fieldName);
                }
            }

            return $result;
        }

        /**
         * @brief Transpose 2D array.
         * Input:
         * @code
        array(
         * $n => array(
         * $fieldName => $fieldValue
         * )
         * )
         * @endcode
         * Output:
         *     array($fieldName => array($n => $fieldValue))
         * @code
        array(
         * $fieldName => array(
         * $n => $fieldValue
         * )
         * )
         * @endcode
         * @param array $dbResult 2D array.
         * @return array
         */
        function transpose(array $dbResult) {
            $result = [];
            foreach ($dbResult as $row) {
                foreach ($row as $fieldName => $fieldValue) {
                    $result[$fieldName][] = $fieldValue;
                }
            }

            return $result;
        }

        /**
         * @brief Convert data base result.
         * Reindexes array
         * In new array $pkNAme value become new index
         * @param mixed $result Data base result.
         * @param mixed $pkName Primary key.
         * @param boolean $deletePK Delete fields with primary key from the result?
         * @return array
         * @see QAL::select()
         * @throws SystemException 'ERR_DEV_BAD_DATA'
         */
        function reindex($result, $pkName, $deletePK = false) {
            $result = array_column($result, null, $pkName);
            if ($deletePK) {
                array_walk($result, function (&$row) use ($pkName) {
                    unset($row[$pkName]);
                });
            }

            return $result;
        }

        /**
         * @brief Convert field names to Camel Notation.
         * @param array $fields
         * @param string $prefix Prefix, that should be removed from the name.
         * @return array
         */
        function convertFieldNames(array $fields, $prefix = '') {
            $result = [];
            array_walk($fields, function ($f, $fieldName) use ($prefix, &$result) {
                if (($plen = strlen($prefix)) > 0 && strpos($fieldName, $prefix) === 0) {
                    $fieldName = substr($fieldName, $plen);
                }

                $fieldName = preg_replace_callback('/_(\w)/', function ($m) {
                    return strtoupper($m[1]);
                }, $fieldName);
                $result[$fieldName] = $f;
            });

            return $result;
        }

        //todo VZ: I think the using of $key is bad.
        /**
         * @brief Push the new array element at the end of the array.
         * The index of newly inserted element will be returned.
         * @param array $array Array.
         * @param mixed $var New array element
         * @param int $key Desired key value of the new element.
         * @return int
         * @see array_push()
         */
        function arrayPush(array &$array, $var, $key = null) {
            $newkey = 0;
            $keys = array_keys($array);
            if (!empty($keys)) {
                if (is_null($key)) {
                    rsort($keys);
                    $newkey = $keys[0] + 1;
                } else {
                    $newkey = $key;
                }
            }
            $array[$newkey] = $var;

            return $newkey;
        }

        /**
         * @brief Push the new array element before specific position.
         * @param array $array Array.
         * @param mixed $var New array element.
         * @param int|string $pos Position.
         * @return array
         */
        function arrayPushBefore(array $array, $var, $pos) {
            $result = [];
            if (!is_array($var)) {
                $var = [$var];
            }
            if (is_int($pos)) {
                $result = array_merge(
                    array_slice($array, 0, $pos),
                    $var,
                    array_slice($array, $pos)
                );
            } else {
                foreach ($array as $key => $value) {
                    if ($key == $pos) {
                        $result = array_merge($result, $var);
                    }
                    $result[$key] = $value;
                }
            }

            return $result;
        }

        /**
         * @brief Push the new array element after specific position.
         * @param array $src Array.
         * @param mixed $in New array element.
         * @param int|string $pos Position.
         * @return array
         */
        function arrayPushAfter($src, $in, $pos) {
            if (is_int($pos)) {
                $R = array_merge(array_slice($src, 0, $pos + 1), $in, array_slice($src, $pos + 1));
            } else {
                foreach ($src as $k => $v) {
                    $R[$k] = $v;
                    if ($k == $pos) {
                        $R = array_merge($R, $in);
                    }
                }
            }

            return $R;
        }

        /**
         * Optimized for large strings str_replace
         * @param $from char
         * @param $to char
         * @param $src string
         * @return string
         */
        function strReplaceOpt($from, $to, $src) {
            for ($i = 0; $i < strlen($src); $i++) {
                if ($src[$i] == $from) {
                    $src[$i] = $to;
                }
            }

            return $src;
        }

        /**
         * Get the translation of the text constant.
         * Get the translation of the text constant from the translation table for specific language.
         * If the language not provided, then current language will be used.
         * @param string $const Text constant
         * @param int $langId Language ID.
         * @return string
         */
        function translate($const, $langId = null) {
            static $translationsCache, $findTranslationSQL;
            if (empty($const)) {
                return $const;
            }
            if (is_null($findTranslationSQL)) {
                $findTranslationSQL = E()->getDB()->getPDO()->prepare('SELECT trans.ltag_value_rtf AS translation FROM share_lang_tags ltag  LEFT JOIN share_lang_tags_translation trans ON trans.ltag_id = ltag.ltag_id  WHERE (ltag.ltag_name = ?) AND (lang_id = ?)');
            }

            $const = strtoupper($const);
            if (is_null($langId)) {
                $langId = intval(E()->getLanguage()->getCurrent());
            }
            $result = $const;

            //Мы еще не обращались за этим переводом
            if (!isset($translationsCache[$langId][$const])) {
                //Если что то пошло не так - нет смысл генерить ошибку, отдадим просто константу
                if ($findTranslationSQL->execute([$const, $langId])) {
                    //записали в кеш
                    if ($result = $findTranslationSQL->fetchColumn()) {
                        $translationsCache[$langId][$const] = $result;
                    } else {
                        $result = $translationsCache[$langId][$const] = $const;
                    }
                }

            } //За переводом уже обращались  Он есть
            elseif ($translationsCache[$langId][$const]) {
                $result = $translationsCache[$langId][$const];
            }
            //Неявный случай - за переводом уже обращались но его нету
            //Отдаем константу

            return $result;
        }

        /**
         * Format date.
         * Pseudo modifiers:
         * - %E - Today|Yesterday|Tomorrow|After tomorrow|Weekday_abbreviation $Date $month_name, $time[$Year(if not current)]
         * - %f - Weekday name $Date $month_name, $time[$Year(if not current)]
         * - %o - [Today,] $Date $month_name, $time[$Year(if not current)]
         * - %q
         * @param int $date Timestamp.
         * @param string $format Format.
         * @param string $type Type.
         * @return string
         */
        public function formatDate($date, $format, $type = FieldDescription::FIELD_TYPE_DATE) {
            if (!$date) {
                return '';
            }
            if($type == FieldDescription::FIELD_TYPE_STRING){
                $result = $date;
                if($format == 'translate(%s)'){
                    $result = E()->Utils->translate($date);
                }
                return $result;
            }
            $date = strtotime($date);
            if (!in_array($format, ['%E', '%f', '%o', '%q'])) {
                $result = @strftime($format, $date);
                if (!$result) {
                    $result = $date;
                }
            } else {
                $result = '';
                $today = strtotime("midnight");
                $tomorrow = strtotime("midnight +1 day");
                $dayAfterTomorrow = strtotime("midnight +2 day");
                $tomorrowPlus3 = strtotime("midnight +3 day");
                $yesterday = strtotime("midnight -1 day");
                $beforeYesterday = strtotime("midnight -2 day");
                switch ($format) {
                    case '%E':
                        if ($date >= $today and $date < $tomorrow) {
                            $result .= translate('TXT_TODAY');
                        } elseif ($date < $today and $date >= $yesterday) {
                            $result .= translate('TXT_YESTERDAY');
                        } elseif ($date < $yesterday and $date >= $beforeYesterday) {
                            $result .= translate('TXT_BEFORE_YESTERDAY');
                        } elseif ($date >= $tomorrow && $date < $dayAfterTomorrow) {
                            $result .= translate('TXT_TOMORROW');
                        } elseif ($date >= $dayAfterTomorrow && $date < $tomorrowPlus3) {
                            $result .= translate('TXT_AFTER_TOMORROW');
                        } else {
                            $dayNum = date('w', $date);
                            if ($dayNum == 0) {
                                $dayNum = 7;
                            }
                            $result .= translate('TXT_WEEKDAY_SHORT_' . $dayNum);
                        }
                        $result .= ', ' . date('j', $date) . ' ' . (translate('TXT_MONTH_' . date('n', $date)));
                        if (date('Y', $date) != date('Y')) {
                            $result .= ' ' . date('Y', $date);
                        }
                        break;
                    case '%f':
                        $dayNum = date('w', $date);
                        if ($dayNum == 0) {
                            $dayNum = 7;
                        }
                        $result .= translate('TXT_WEEKDAY_' . $dayNum) . ', ' . date('j',
                                $date) . ' ' . (translate('TXT_MONTH_' . date('n', $date)));
                        if (date('Y', $date) != date('Y')) {
                            $result .= ' ' . date('Y', $date);
                        }
                        break;
                    case '%o':
                        if ($date >= $today and $date < $tomorrow) {
                            $result .= translate('TXT_TODAY') . ', ';
                        }
                        $result .= date('j', $date) . ' ' . (translate('TXT_MONTH_' . date('n', $date)));

                        if (date('Y', $date) != date('Y')) {
                            $result .= ' ' . date('Y', $date);
                        }
                        break;
                    case '%q':
                        $result .= date('j', $date) . ' ' . (translate('TXT_MONTH_' . date('n', $date)));

                        if (date('Y', $date) != date('Y')) {
                            $result .= ' ' . date('Y', $date);
                        }
                        break;
                }
                //Если часы и минуты = 0, считаем что это просто дата, без времени
                if (in_array($type, [
                    FieldDescription::FIELD_TYPE_DATETIME,
                    FieldDescription::FIELD_TYPE_TIME,
                    FieldDescription::FIELD_TYPE_HIDDEN
                ])) {
                    $result .= ', ';
                    $result .= date('G', $date) . ':' . date('i', $date);
                }
            }

            return $result;
        }

        public function is_PHP_CLI() {
            return (php_sapi_name() === 'cli' OR defined('STDIN'));
        }
    }
}

namespace {
    /**
     * @fn inspect()
     * @brief Inspect variables.
     * It directly prints the information about input arguments to the output stream formatted with HTML.
     */
    function inspect() {
        call_user_func_array([E()->Utils, 'inspect'], func_get_args());
    }

    /**
     * @fn splitDate($date)
     * @brief Split date.
     * It splits the date into the year, month, month day and time (hours, minutes, seconds).
     * @param string $date Date.
     * @return array
     */
    function splitDate($date) {
        return E()->Utils->splitDate($date);
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
    function simple_log($var) {
        E()->Utils->simpleLog($var);
    }

    /**
     * @fn dump_log($var, $append = false)
     * @brief Write the variable information into log file.
     * @param mixed $var Variable
     * @param boolean $append Append the log into the file? If @c false the file will be overwritten.
     */
    function dump_log($var, $append = false) {
        E()->Utils->dumpLog($var, $append);
    }

    /**
     * @fn ddump_log()
     * @brief Write the variable information into log file and terminate program.
     * Log-file will be overwritten.
     */
    function ddump_log() {
        call_user_func_array([E()->Utils, 'ddumpLog'], func_get_args());
    }


    /**
     * @fn simplifyDBResult($dbResult, $fieldName, $singleRow = false)
     * @brief Simplify data base result.
     * It selects the values at the defined field from the result of SELECT request.
     * @see array_column
     * @param mixed $dbResult Result of SELECT request.
     * @param string $fieldName Field name that will be selected from result.
     * @param boolean $singleRow Return only the first value at defined field name?
     * @return mixed
     */
    function simplifyDBResult($dbResult, $fieldName, $singleRow = false) {
        return call_user_func_array([E()->Utils, 'simplify'], func_get_args());
    }

    /**
     * @fn transpose(array $dbResult)
     * @brief Transpose 2D array.
     * Input:
     * @code
    array(
     * $n => array(
     * $fieldName => $fieldValue
     * )
     * )
     * @endcode
     * Output:
     *     array($fieldName => array($n => $fieldValue))
     * @code
    array(
     * $fieldName => array(
     * $n => $fieldValue
     * )
     * )
     * @endcode
     * @param array $r 2D array.
     * @return array
     */
    function transpose(array $r) {
        return E()->Utils->transpose($r);
    }

    /**
     * @fn convertDBResult($dbResult, $pkName, $deletePK = false)
     * @brief Convert data base result.
     * @param mixed $dbResult Data base result.
     * @param mixed $pkName Primary key.
     * @param boolean $deletePK Delete fields with primary key from the result?
     * @return array
     * @see QAL::select()
     * @deprecated
     */
    function convertDBResult(array $dbResult, $pkName, $deletePK = false) {
        return call_user_func_array([E()->Utils, 'reindex'], func_get_args());
    }

    /**
     * @fn convertFieldNames(array $fields, $prefix = '')
     * @brief Convert field names to Camel Notation.
     * @param array $fields
     * @param string $prefix Prefix, that should be removed from the name.
     * @return array
     * @deprecated
     */
    function convertFieldNames(array $fields, $prefix = '') {
        return E()->Utils->convertFieldNames($fields, $prefix);
    }

//todo VZ: I think the using of $key is bad.
    /**
     * @fn arrayPush(array &$array, $var, $key = null)
     * @brief Push the new array element at the end of the array.
     * The index of newly inserted element will be returned.
     * @param array $array Array.
     * @param mixed $var New array element
     * @param int $key Desired key value of the new element.
     * @return int
     * @see array_push()
     * @deprecated
     */
    function arrayPush(array &$array, $var, $key = null) {
        return E()->Utils->arrayPush($array, $var, $key);
    }

    /**
     * @fn array_push_before(array $array, $var, $pos)
     * @brief Push the new array element before specific position.
     * @param array $array Array.
     * @param mixed $var New array element.
     * @param int|string $pos Position.
     * @return array
     * @deprecated
     */
    function array_push_before(array $array, $var, $pos) {
        return E()->Utils->arrayPushBefore($array, $var, $pos);
    }

    /**
     * @fn array_push_after($src,$in,$pos)
     * @brief Push the new array element after specific position.
     * @param array $src Array.
     * @param mixed $in New array element.
     * @param int|string $pos Position.
     * @return array
     * @deprecated
     */
    function array_push_after($src, $in, $pos) {
        return E()->Utils->arrayPushAfter($src, $in, $pos);
    }

    /**
     * Optimized for large strings str_replace
     * @param $from char
     * @param $to char
     * @param $src string
     * @return string
     * @deprecated
     */
    function str_replace_opt($from, $to, $src) {
        return E()->Utils->strReplaceOpt($from, $to, $src);
    }

    /**
     * @param $fullyQualifiedClassName string
     * @return string
     */
    function simplifyClassName($fullyQualifiedClassName) {
        $className = explode('\\', $fullyQualifiedClassName);

        return array_pop($className);
    }

    /**
     * Get the translation of the text constant.
     * Get the translation of the text constant from the translation table for specific language.
     * If the language not provided, then current language will be used.
     * @param string $const Text constant
     * @param int $langId Language ID.
     * @return string
     * @deprecated
     */
    function translate($const, $langId = null) {
        return E()->Utils->translate($const, $langId);
    }

    /**
     * Transform date to the string.
     * @param int $year Year value.
     * @param int $month Month value.
     * @param int $day Day value
     * @return string
     */
    function dateToString($year, $month, $day) {
        $result = (int)$day . ' ' . translate('TXT_MONTH_' . (int)$month) . ' ' . $year;

        return $result;
    }
}