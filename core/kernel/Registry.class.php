<?php
/**
 * Содержит функцию E[nergine]
 * и класс Registry
 */
require('Object.class.php');
/**
 * Shortcut для Registry::getInstance
 * @return Registry
 */
function E() {
    return Registry::getInstance();
}

/**
 * Реестр приложения
 *
 * Такой себе гибрид Registry & Service Locator
 * Любой объект помещенный в него становится синглтоном
 * Кроме того есть набор методов возвращающих объекты для часто используемых классов
 *
 * @throws SystemException
 * @see Singleton
 *
 */

final class Registry extends Object {
    /**
     * Инстанс этого класса
     * @var Registry
     */
    static private $instance = null;
    /**
     * перечень хранящихся в реестре объектов
     * @var array
     */
    private $entities = array();
    /**
     * Флаг использующийся для имитации приватного конструктора
     *
     * @access private
     * @var boolean
     * @static
     */
    private static $flag = null;

    public function __construct() {
        if (is_null(self::$flag)) {
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
    private function __clone() {
    }

    /**
     *
     * @access public
     * @return Registry
     * @static
     * @final
     */
    final public static function getInstance() {
        if (is_null(self::$instance)) {
            self::$flag = true;
            self::$instance = new Registry();
        }
        return self::$instance;
    }

    /**
     * @param  $className string
     * @return mixed
     */
    public function __get($className) {
        return $this->get($className);
    }

    private function get($className) {
        $result = null;
        if (isset($this->entities[$className])) {
            $result = $this->entities[$className];
        }
            //поскольку предполагается хранить синглтоны, пробуем создать соответствующий класс ориентируясь на имя
        else {
            $result = $this->entities[$className] = new $className();
        }


        return $result;
    }

    public function __set($className, $object) {
        if (!isset($this->entities[$className])) {
            $this->entities[$className] = $object;
        }
    }

    /**
     * @param  $entityName string
     * @return
     */
    public function __isset($entityName) {
        return isset($this->entities[$entityName]);
    }

    /**
     * Убирать вручную из реестра ничего нельзя
     *
     * @param  $entityName string
     * @return void
     */
    public function __unset($entityName) {

    }

    /**
     * @return AuthUser
     */
    public function getAUser() {
        return $this->get('AuthUser');
    }

    public function setAUser( /*AuthUser */
        $anotherAuthUserObject) {
        if (isset($this->entities['AuthUser'])) {
            throw new Exception ('AuthUser object is already used. You can not substitute it here.');
        }
        $this->entities['AuthUser'] = $anotherAuthUserObject;
    }

    /**
     * Пока непонятно что с ним делать
     *
     * public function substitute($object){
    if(!($className = get_class($object))){
    throw new Exception((string)$object.' is not an object');
    }
    if(isset($this->entities[$className])){
    throw new Exception($className.' is already used. You can not substitute it here.');
    }
    return $this->get($className);
    }*/

    /**
     * @return Request
     */
    public function getRequest() {
        return $this->get('Request');
    }

    /**
     * @return Response
     */
    public function getResponse() {
        return $this->get('Response');
    }

    /**
     * @return Document
     */
    public function getDocument() {
        return $this->get('Document');
    }

    /**
     * @return Language
     */
    public function getLanguage() {
        return $this->get('Language');
    }

    /**
     * @return DocumentController
     */
    public function getController() {
        return $this->get('DocumentController');
    }

   
    /**
     * @return QAL
     */
    public function getDB() {
        if (!isset($this->entities['QAL'])) {
            $this->entities['QAL'] = new QAL(
                'mysql:' . $this->getConfigValue('database.master.dsn'),
                $this->getConfigValue('database.master.username'),
                $this->getConfigValue('database.master.password'),
                array(
                    PDO::ATTR_PERSISTENT => false,
                    PDO::ATTR_EMULATE_PREPARES => true,
                    PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true
                )
            );
        }

        return $this->entities['QAL'];
    }

    /**
     *
     * @return Memcacher
     */
    public function getCache() {
        return $this->get('Memcacher');
    }
}
