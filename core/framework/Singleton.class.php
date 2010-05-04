<?php
/**
 * Содержит класс Singleton
 *
 * @package energine
 * @subpackage core
 * @author d.pavka
 * @copyright d.pavka@gmail.com
 */

 /**
  * Абстрактный класс предок для всех синглтонов
  *
  * @package energine
  * @subpackage core
  * @author d.pavka@gmail.com
  */
abstract class Singleton extends DBWorker {

     /**
      * Флаг использующийся для имитации приватного конструктора
      * 
      * @access private
      * @var boolean
      * @static 
      */
      private static $flag;
    /**
     * Конструктор класса
     *
     * @access public
     */
    public function __construct() {
        if(is_null(self::$flag)){
            throw new SystemException('ERR_PRIVATE_CONSTRUCTOR', SystemException::ERR_DEVELOPER);
        }
        parent::__construct();
        self::$flag = null;        
    }
    
    /**
      * Закрываем возможность клонирования 
      * 
      * @return void
      * @access private
      */
    private function __clone(){}
    
    /**
     * 
     * @access public
     * @return self
     * @static
     * @final 
     */
    final public static function getInstance(){
    	static $instance = array(); 
    	$calledClassName = get_called_class();
    	 
        self::$flag = true;
        if(!isset($instance[$calledClassName])){
            $instance[$calledClassName] = new $calledClassName();
        }
        return $instance[$calledClassName];
    }
}