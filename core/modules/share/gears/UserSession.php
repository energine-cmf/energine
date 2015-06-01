<?php
/**
 * @file
 * UserSession.
 *
 * It contains the definition to:
 * @code
final class UserSession;
 * @endcode
 *
 * @author dr.Pavka
 * @copyright Energine 2011
 *
 * @version 1.0.0
 */
namespace Energine\share\gears;
/**
 * Manager of user sessions.
 *
 * @code
final class UserSession;
 * @endcode
 *
 * @final
 */
final class UserSession implements \SessionHandlerInterface {
    use DBWorker;
    /**
     * Cookie name for failed login.
     * @var string FAILED_LOGIN_COOKIE_NAME
     *
     * @see auth.php
     *
     * @note In general this should be in LoginForm, but of the inconvenience call it was moved here.
     *
     * @todo Все таки надо как то ей указать надлежащее место
     */
    const FAILED_LOGIN_COOKIE_NAME = 'failed_login';

    /**
     * Default session name.
     * @var string DEFAULT_SESSION_NAME
     */
    const DEFAULT_SESSION_NAME = 'NRGNSID';

    /**
     * Default probability to call garbage collector.
     * It calculates as follows: <tt> DEFAULT_PROBABILITY / session.gc_divisor</tt>, where <tt>session.gc_divisor</tt> is 100 by defaults.
     * Example: <tt>10 / 100</tt> means that garbage collector will called with probability of 10%.
     */
    const DEFAULT_PROBABILITY = 10;

    const STATUS_NONE = 0;
    const STATUS_CREATED = 1;
    const STATUS_INITIALIZED = 2;
    const STATUS_LAUNCHED = 4;
    const STATUS_STARTED = 6;

    private $status = self::STATUS_NONE;

    /**
     * Session ID.
     * @var string $phpSessId
     */
    private $phpSessId;

    /**
     * Timeout.
     * If time difference between requests is bigger than this value then the session become invalid.
     * @var int $timeout
     */
    private $timeout;

    /**
     * Maximal session lifespan.
     * It is used to setup the lifespan of cookie and garbage collector.
     * @var int $lifespan
     */
    private $lifespan;

    /**
     * User agent.
     * @var string $userAgent
     */
    private $userAgent;

    /**
     * Session data.
     *
     *
     * @var null|bool|string $data
     */
    private $data = NULL;
    /**
     * @var int
     */
    private $id = NULL;

    private $UID = NULL;

    /**
     * Session table name in data base.
     * @var string $tableName
     */
    static private $tableName = 'share_session';

    /**
     *
     */
    public function __construct() {
        $this->timeout = Object::_getConfigValue('session.timeout');
        $this->lifespan = Object::_getConfigValue('session.lifespan');
        $this->userAgent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'ROBOT';
        ini_set('session.gc_probability', self::DEFAULT_PROBABILITY);
    }

    public function init() {
        session_set_save_handler($this);
        session_name(self::DEFAULT_SESSION_NAME);
        $this->status = self::STATUS_INITIALIZED;

        return $this;
    }

    public function launch() {
        if ($this->status < self::STATUS_INITIALIZED) {
            $this->init();
        } elseif ($this->status < self::STATUS_LAUNCHED) {
            if ($this->load(self::getSessionID())) {
                E()->getDB()->modify('UPDATE ' . self::$tableName . ' SET session_last_impression = UNIX_TIMESTAMP(), session_expires = (UNIX_TIMESTAMP() + %s) WHERE session_native_id = %s', $this->lifespan, $this->phpSessId);
            } else {
                $this->refuse();
            }

        }
        $this->status = self::STATUS_LAUNCHED;
        return $this;
    }

    /**
     * @param null $UID
     * @return array|bool
     * @throws \Energine\share\gears\SystemException
     */
    public function create($UID = NULL) {
        $data['session_last_impression'] = time();
        $data['session_expires'] = $data['session_last_impression'] + 15 * 60;

        if ($this->status == self::STATUS_STARTED) {
            if ($UID != $this->UID) {
                $this->UID = $data['u_id'] = $UID;
                $data['session_data'] = 'UID|' . serialize($UID);
                $data['session_ip'] = E()->getRequest()->getClientIP(true);
            }
            E()->getDB()->modify(QAL::UPDATE, self::$tableName, $data, ['session_id' => $this->id]);
        } else {
            $this->phpSessId = $data['session_native_id'] = self::createIdentifier();
            $data['session_created'] = $data['session_last_impression'];
            if ($UID) {
                $this->UID = $data['u_id'] = $UID;
                $data['session_data'] = 'UID|' . serialize($UID);
            }
            $data['session_ip'] = E()->getRequest()->getClientIP(true);
            $this->id = E()->getDB()->modify(QAL::INSERT, self::$tableName, $data);
            $_COOKIE[self::DEFAULT_SESSION_NAME] = $this->phpSessId;
        }
        $this->status = self::STATUS_CREATED;
        E()->Response->addCookie(self::DEFAULT_SESSION_NAME, $this->phpSessId, $data['session_expires']);
    }

    private function forceStart() {
        // устанавливаем время жизни cookie
        if (Object::_getConfigValue('site.domain')) {
            $path = '/';
            $domain = '.' . Object::_getConfigValue('site.domain');
        } else {
            $path = E()->getSiteManager()->getCurrentSite()->root;
            $domain = '';
        }
        session_set_cookie_params($this->lifespan, $path, $domain);
        session_id($this->phpSessId);
        session_start();
    }

    public function start() {
        if ($this->status < self::STATUS_LAUNCHED) {
            $this->launch();
        }

        if ($this->status < self::STATUS_STARTED) {
            $this->forceStart();
            $this->status = self::STATUS_STARTED;
        }

        return $this;
    }


    /**
     * Check if the session is opened.
     *
     * @return bool
     */
    static public function getSessionID() {
        return (isset($_COOKIE[self::DEFAULT_SESSION_NAME]) && !empty($_COOKIE[self::DEFAULT_SESSION_NAME])) ? $_COOKIE[self::DEFAULT_SESSION_NAME] : false;
    }

    /**
     * Validate the session.
     * It checks the validity of the session with this ID. If true then session data will be returned.
     *
     * @param int $sessionID Session ID.
     * @return mixed|false
     */
    public function load($sessionID) {
        $result = NULL;
        if ($sessionID) {
            $result = E()->getDB()->select(
                'SELECT * FROM ' . self::$tableName .
                ' WHERE session_native_id = %s ' .
                ' AND session_expires >= UNIX_TIMESTAMP()',
                $sessionID
            );

            if ($result) {
                $this->phpSessId = $sessionID;
                list($row) = $result;
                $this->id = $row['session_id'];
                $this->data = $row['session_data'];
                $this->UID = $row['u_id'];

                $result = true;
            } else {
                $this->phpSessId = NULL;
                $result = false;
            }
        }

        return $result;
    }


    /**
     * Delete session information.
     */
    public function refuse() {
        if (isset($_COOKIE[UserSession::DEFAULT_SESSION_NAME])) {
            $sessID = $_COOKIE[UserSession::DEFAULT_SESSION_NAME];
            E()->getDB()->modify(QAL::DELETE, 'share_session', NULL, ['session_native_id' => $sessID]);
            E()->Response->deleteCookie(UserSession::DEFAULT_SESSION_NAME);
        }
    }

    public function getID() {
        return $this->id;
    }


    /**
     * Generate ID.
     *
     * @return string
     */
    private static function createIdentifier() {
        return sha1(time() + rand(0, 10000));
    }

    /**
     * Open session.
     *
     * @param string $savePath Save path.
     * @param string $sessionName Session name.
     * @return boolean
     */
    public function open($savePath, $sessionName) {
        return true;
    }

    //todo VZ: Why for closing true is returned?
    /**
     * Close session.
     *
     * @return bool
     */
    public function close() {
        return true;
    }

    //todo VZ: input argument is not used.
    /**
     * Read session data.
     *
     * @param string $phpSessId Sesion ID.
     * @return mixed
     *
     * @see UserSession::data
     */
    public function read($phpSessId) {
        return ($this->data);
    }

    /**
     * Write session data.
     *
     * @param string $phpSessId Session ID.
     * @param mixed $data Data.
     * @return mixed
     */
    public function write($phpSessId, $data) {
        if (!empty($data)) {
            $this->data = $data;
            $data = ['session_data' => $data];
            if ($this->UID) {
                $data['u_id'] = $this->UID;
            }

            $this->dbh->modify(QAL::UPDATE, self::$tableName, $data, ['session_native_id' => $phpSessId]);
        }
        return true;
    }

    /**
     * Destroy session.
     *
     * @param string $phpSessId Session ID.
     * @return bool
     */
    public function destroy($phpSessId) {
        return $this->dbh->modify(QAL::DELETE, self::$tableName, NULL, ['session_native_id' => $phpSessId]);
    }

    //todo VZ: input argument is not used.
    //todo VZ: Why true is returned?
    /**
     * Garbage collector.
     *
     * @param int $maxLifeTime Maximal session lifespan.
     * @return bool
     */
    public function gc($maxLifeTime) {
        $this->dbh->modify(
            QAL::DELETE,
            self::$tableName,
            NULL,
            'session_expires < UNIX_TIMESTAMP()'
        );
        return true;
    }

    public function __get($var) {
        $result = NULL;
        if ($this->status < self::STATUS_LAUNCHED) {
            $this->launch();
        }
        if ($var == 'UID') {
            $result = $this->UID;
        } elseif (isset($this->data[$var])) {
            $result = $this->data[$var];
        }

        return $result;
    }

    function __set($name, $value) {
        if ($this->status < self::STATUS_LAUNCHED) {
            $this->launch();
        }
        $_SESSION[$name] = $value;
    }

    function __unset($name) {
        if ($this->status < self::STATUS_LAUNCHED) {
            $this->launch();
        }
        unset($_SESSION[$name]);
    }

}
