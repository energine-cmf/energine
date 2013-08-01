<?php
/**
 * Copyright 2011 Facebook, Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may
 * not use this file except in compliance with the License. You may obtain
 * a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations
 * under the License.
 */

require_once "base_facebook.php";

/**
 * Extends the BaseFacebook class with the intent of using
 * PHP sessions to store user ids and access tokens.
 */
class FBOAuth extends BaseFacebook {
    const STORAGE_TABLE_NAME = 'user_fb_storage';
    private $localStorage = array();

    /**
     * Identical to the parent constructor, except that
     * we start a PHP session to store the user ID and
     * access token if during the course of execution
     * we discover them.
     *
     * @param Array $config the application configuration.
     * @see BaseFacebook::__construct in facebook.php
     */
    public function __construct($config) {
        parent::__construct($config);
    }

    protected static $kSupportedKeys =
            array('state', 'code', 'access_token', 'user_id');

    /**
     * Provides the implementations of the inherited abstract
     * methods.  The implementation uses PHP sessions to maintain
     * a store for authorization codes, user ids, CSRF states, and
     * access tokens.
     */
    protected function setPersistentData($key, $value) {
        if (!in_array($key, self::$kSupportedKeys)) {
            self::errorLog('Unsupported key passed to setPersistentData.');
            return;
        }

        $session_var_name = $this->constructSessionVariableName($key);
        $this->$session_var_name = $value;
    }

    protected function getPersistentData($key, $default = false) {
        if (!in_array($key, self::$kSupportedKeys)) {
            self::errorLog('Unsupported key passed to getPersistentData.');
            return $default;
        }
        $session_var_name = $this->constructSessionVariableName($key);
        return isset($this->$session_var_name) ?
                $this->$session_var_name : $default;
    }

    protected function clearPersistentData($key) {
        if (!in_array($key, self::$kSupportedKeys)) {
            self::errorLog('Unsupported key passed to clearPersistentData.');
            return;
        }

        $session_var_name = $this->constructSessionVariableName($key);
        unset($this->$session_var_name);
    }

    protected function clearAllPersistentData() {
        foreach (self::$kSupportedKeys as $key) {
            $this->clearPersistentData($key);
        }
    }

    protected function constructSessionVariableName($key) {
        return implode('_', array('fb',
            $this->getAppId(),
            $key));
    }

    public function __get($varName) {
        if (isset($this->localStorage[$varName])) return $this->localStorage[$varName];

        if(isset($_COOKIE[$varName])) {
            return $_COOKIE[$varName];
        }
        else {
            return false;
        }

        /*$result = E()->getDB()->select(self::STORAGE_TABLE_NAME, 'fb_var_value', array('fb_var_name' => $varName));

        if (!is_array($result)) return ($this->localStorage[$varName] = null);

        return ($this->localStorage[$varName] = simplifyDBResult($result, 'fb_var_value', true));*/

    }

    public function __set($varName, $varValue) {
        $this->localStorage[$varName] = $varValue;
        /*E()->getDB()->modify(QAL::INSERT_IGNORE, self::STORAGE_TABLE_NAME, array('fb_var_name' => $varName, 'fb_var_value' => $varValue));*/
        setcookie($varName, $varValue, time() + 3600);
    }

    public function __unset($varName) {
        unset($this->localStorage[$varName]);
        //E()->getDB()->modify(QAL::DELETE, self::STORAGE_TABLE_NAME, null, array( 'fb_var_name' => $varName));
        setcookie($varName, '', time() - 3600);
    }

    public function __isset($varName) {
        if(isset($this->localStorage[$varName]) && !is_null($this->localStorage[$varName])) return true;
        return isset($_COOKIE[$varName]);
        /*$result = false;
        $res = E()->getDB()->select(self::STORAGE_TABLE_NAME, 'fb_var_value', array('fb_var_name' => $varName));
        if (is_array($res)) {
            $this->localStorage[$varName] = simplifyDBResult($res, 'fb_var_value', true);
            $result = true;
        }
        return $result;*/
    }

    /**
     * Prints to the error log if you aren't in command line mode.
     *
     * @param string $msg Log message
     */
    protected static function errorLog($msg) {
        // disable error log if we are running in a CLI environment
        // @codeCoverageIgnoreStart
        if (php_sapi_name() != 'cli') {
            error_log(date('d.m.Y h:i:s') . ' [ ' . $_SERVER['REMOTE_ADDR'] .  ' ]: ' . $msg, 3, '/home/dexter/projects/logs/ufo/fb.error.log' . "\n");
        }
        // uncomment this if you want to see the errors on the page
        //print 'error_log: '.$msg."\n";
        // @codeCoverageIgnoreEnd
    }
}
