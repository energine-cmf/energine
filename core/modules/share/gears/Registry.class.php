<?php
/**
 * @file
 * E(), Registry.
 *
 * It contains the definition to:
 * @code
function E();
final class Registry;
@endcode
 *
 * @author dr.Pavka
 * @copyright Energine 2013
 *
 * @version 1.0.0
 */

// Подключаем предка напрямую
require('Object.class.php');

/**
 * @fn E
 * @brief E[nergine].
 * Shortcut for Registry::getInstance.
 *
 * @return Registry
 */
function E() {
    return Registry::getInstance();
}

/**
 * Application registry.
 *
 * @code
final class Registry;
@endcode
 *
 * Such Registry & Service Locator hybrid.
 * Any injected object here become a singleton.
 * In addition there is a set of methods, that returns an objects for commonly used classes.
 *
 * @see Singleton
 *
 * @attention This is @b final class.
 */
final class Registry extends Object {
    /**
     * Instance of this class.
     * @var Registry $instance
     */
    static private $instance = null;

    /**
     * List of stored objects in the registry.
     * @var array $entities
     */
    private $entities = array();

    /**
     * Flag for imitation the private constructor.
     *
     * @var boolean $flag
     */
    private static $flag = null;

    /**
     * @throws SystemException
     */
    public function __construct() {
        if (is_null(self::$flag)) {
            throw new SystemException('ERR_PRIVATE_CONSTRUCTOR', SystemException::ERR_DEVELOPER);
        }
        self::$flag = null;
    }

    /**
     * Disable cloning.
     */
    private function __clone() {
    }

    /**
     * Get instance.
     *
     * @attention This is @b final method.
     *
     * @return Registry
     */
    final public static function getInstance() {
        if (is_null(self::$instance)) {
            self::$flag = true;
            self::$instance = new Registry();
        }
        return self::$instance;
    }

    /**
     * Magic get.
     *
     * @throws Exception 'Use Registry::getMap($siteID) instead.'
     *
     * @param string $className Class name.
     * @return FileRepoInfo|ComponentManager|mixed
     */
    public function __get($className) {
        if ($className == 'Sitemap') {
            throw new Exception('Use Registry::getMap($siteID) instead.');
        }
        return $this->get($className);
    }

    /**
     * Get class by name.
     *
     * @param string $className Class name.
     * @return mixed
     */
    private function get($className) {
        $result = null;
        if (isset($this->entities[$className])) {
            $result = $this->entities[$className];
        } //поскольку предполагается хранить синглтоны, пробуем создать соответствующий класс ориентируясь на имя
        else {
            $result = new $className();
            $this->entities[$className] = $result;
        }

        return $result;
    }

    /**
     * Magic set.
     *
     * @param string $className Class name.
     * @param mixed $object Object.
     */
    public function __set($className, $object) {
        if (!isset($this->entities[$className])) {
            $this->entities[$className] = $object;
        }
    }

    /**
     * Check if some entity name is set.
     *
     * @param string $entityName
     * @return bool
     */
    public function __isset($entityName) {
        return isset($this->entities[$entityName]);
    }

    /**
     * Disable manual unsetting.
     *
     * @param string $entityName Entity name.
     */
    public function __unset($entityName) {
    }

    /**
     * Get AuthUser.
     *
     * @return AuthUser
     */
    public function getAUser() {
        return $this->get('AuthUser');
    }

    /**
     * Set AuthUser.
     *
     * @throws Exception 'AuthUser object is already used. You can not substitute it here.'
     *
     * @param AuthUser $anotherAuthUserObject AuthUser object.
     */
    public function setAUser($anotherAuthUserObject) {
        if (isset($this->entities['AuthUser'])) {
            throw new Exception ('AuthUser object is already used. You can not substitute it here.');
        }
        $this->entities['AuthUser'] = $anotherAuthUserObject;
    }

    //todo VZ: remove this?
    /**
     * Пока непонятно что с ним делать
     *
     * public function substitute($object){
     * if(!($className = get_class($object))){
     * throw new Exception((string)$object.' is not an object');
     * }
     * if(isset($this->entities[$className])){
     * throw new Exception($className.' is already used. You can not substitute it here.');
     * }
     * return $this->get($className);
     * }*/

    /**
     * Get Request.
     *
     * @return Request
     */
    public function getRequest() {
        return $this->get('Request');
    }

    /**
     * Get Response.
     *
     * @return Response
     */
    public function getResponse() {
        return $this->get('Response');
    }

    /**
     * Get Document.
     *
     * @return Document
     */
    public function getDocument() {
        return $this->get('Document');
    }

    /**
     * Get OGObject.
     *
     * @return OGObject
     */
    public function getOGObject() {
        return $this->get('OGObject');
    }

    /**
     * Get Language.
     *
     * @return Language
     */
    public function getLanguage() {
        return $this->get('Language');
    }

    /**
     * Get SiteManager.
     *
     * @return SiteManager
     */
    public function getSiteManager() {
        return $this->get('SiteManager');
    }

    /**
     * Get Sitemap object.
     *
     * In fact, several objects of these class exist.
     *
     *
     * @param bool|int $siteID Site ID.
     * @return Sitemap
     */
    public function getMap($siteID = false) {
        if (!$siteID) $siteID = E()->getSiteManager()->getCurrentSite()->id;
        if (!isset($this->entities['Sitemap'][$siteID])) {
            $this->entities['Sitemap'][$siteID] = new Sitemap($siteID);
        }
        return $this->entities['Sitemap'][$siteID];
    }

    /**
     * Get DocumentController.
     *
     * @return DocumentController
     */
    public function getController() {
        return $this->get('DocumentController');
    }

    /**
     * Get QAL.
     *
     * @return QAL
     */
    public function getDB() {
        if (!isset($this->entities['QAL'])) {
            $this->entities['QAL'] = new QAL(
                sprintf('mysql:host=%s;port=%s;dbname=%s',
                    $this->getConfigValue('database.host'),
                    $this->getConfigValue('database.port'),
                    $this->getConfigValue('database.db')
                ),
                $this->getConfigValue('database.username'),
                $this->getConfigValue('database.password'),
                array(
                    PDO::ATTR_PERSISTENT => (bool)$this->getConfigValue('database.persistent'),
                    PDO::ATTR_EMULATE_PREPARES => true,
                    PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true
                )
            );
        }

        return $this->entities['QAL'];
    }

    /**
     * Get Cache.
     *
     * @return Cache
     */
    public function getCache() {
        return $this->get('Cache');
    }
}
