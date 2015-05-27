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

    /**
     * Instance flag.
     * It denies to call directly UserSession, only over UserSession::start().
     * @var bool $instance
     */
    private static $instance = false;

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
     * - null - Session is not exist.
     * - false - Session is exist but there is no data.
     * - string - Session and data are exist.
     *
     * @var null|bool|string $data
     */
    private $data = NULL;
    /**
     * @var int
     */
    private $id = NULL;

    /**
     * Session table name in data base.
     * @var string $tableName
     */
    static private $tableName = 'share_session';

    /**
     * @param bool $force Force to create session?
     *
     * @throws \BadMethodCallException
     */
    private function __construct($force = false) {
        $this->timeout = Object::_getConfigValue('session.timeout');
        $this->lifespan = Object::_getConfigValue('session.lifespan');
        $this->userAgent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'ROBOT';

        ini_set('session.gc_probability', self::DEFAULT_PROBABILITY);
        // устанавливаем обработчики сеанса
        session_set_save_handler($this);
        session_name(self::DEFAULT_SESSION_NAME);
        $this->data = false;

        if ($this->phpSessId = self::isOpen()) {
            $this->data = self::isValid($this->phpSessId);
            //Если сессия валидная
            if (is_null($this->data) || $this->data) {
                $this->id = $this->dbh->getScalar(self::$tableName, 'session_id', ['session_native_id' => $this->phpSessId]);

                E()->getDB()->modify('UPDATE ' . self::$tableName . ' SET session_last_impression = UNIX_TIMESTAMP(), session_expires = (UNIX_TIMESTAMP() + %s) WHERE session_native_id = %s', $this->lifespan, $this->phpSessId);
            } elseif ($force) {
                //создаем ее вручную
                $cookieInfo = self::manuallyCreateSessionInfo();
                $this->phpSessId = $cookieInfo[1];

                if (($cookieInfo = UserSession::manuallyCreateSessionInfo())) {
                    call_user_func_array(
                        [E()->getResponse(), 'addCookie'],
                        $cookieInfo
                    );
                }
            } //сессия невалидная
            else {
                $this->dbh->modify(QAL::DELETE, self::$tableName, NULL, ["session_native_id" => addslashes($this->phpSessId)]);
                // удаляем cookie сеанса
                E()->getResponse()->deleteCookie(self::DEFAULT_SESSION_NAME);
                return;
            }
        } elseif ($force) {
            //создаем ее вручную
            $cookieInfo = self::manuallyCreateSessionInfo();
            $this->phpSessId = $cookieInfo[1];
            if (($cookieInfo = UserSession::manuallyCreateSessionInfo())) {
                call_user_func_array(
                    [E()->getResponse(), 'addCookie'],
                    $cookieInfo
                );
            }
        } else {
            self::$instance = false;
            return;
        }

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

    private function reload() {

        if (!$this->phpSessId) {
            if (($cookieInfo = UserSession::manuallyCreateSessionInfo())) {
                //var_dump($cookieInfo, session_status() == PHP_SESSION_NONE);
                $this->phpSessId = $cookieInfo[1];
                $this->id = $cookieInfo[3];

                if (session_status() == PHP_SESSION_NONE) {
                    session_id($this->phpSessId);
                    session_start();
                }
                call_user_func_array(
                    [E()->getResponse(), 'addCookie'],
                    $cookieInfo
                );
            }
        }
    }

    /**
     * Check if the session is opened.
     *
     * @return bool
     */
    static public function isOpen() {
        return (isset($_COOKIE[self::DEFAULT_SESSION_NAME]) && !empty($_COOKIE[self::DEFAULT_SESSION_NAME])) ? $_COOKIE[self::DEFAULT_SESSION_NAME] : false;
    }

    /**
     * Validate the session.
     * It checks the validity of the session with this ID. If true then session data will be returned.
     *
     * @param int $sessID Session ID.
     * @return mixed|false
     */
    static public function isValid($sessID) {
        return E()->getDB()->getScalar(
            'SELECT session_data FROM ' . self::$tableName .
            ' WHERE session_native_id = %s ' .
            ' AND session_expires >= UNIX_TIMESTAMP()',
            $sessID
        );
    }

    //todo VZ: Why not to use 0 as the default for arguments?
    /**
     * Create new session information.
     * It returns an information about cookie for new session for Response::addCookie().
     *
     * @param int|bool $UID User ID.
     * @param bool $expires
     * @return array
     */
    public static function manuallyCreateSessionInfo($UID = false, $expires = false) {
        if (($id = self::isOpen()) && (self::isValid($id) !== false)) {
            if ($UID) {
                $data['u_id'] = $UID;
                $data['session_last_impression'] = time();
                $data['session_data'] = 'userID|' . serialize($UID);
                $data['session_ip'] = E()->getRequest()->getClientIP(true);

                E()->getDB()->modify(QAL::UPDATE, 'share_session', $data, ['session_native_id' => $id]);
            }
            return false;
        }
        //Записали данные в БД
        $data['session_native_id'] = self::createIdentifier();
        $data['session_created'] = $data['session_last_impression'] = time();
        if (!$expires)
            $data['session_expires'] = $data['session_created'] + 15 * 60;
        else
            $data['session_expires'] = $expires;

        if ($UID) {
            $data['u_id'] = $UID;
            //Финт ушами поскольку стандартно в РНР сериализированные данные сессии содержат еще и имя переменной
            $data['session_data'] = 'userID|' . serialize($UID);
        }

        $data['session_ip'] = E()->getRequest()->getClientIP(true);
        $id = E()->getDB()->modify(QAL::INSERT, 'share_session', $data);
        $_COOKIE[self::DEFAULT_SESSION_NAME] = $data['session_native_id'];


        return [self::DEFAULT_SESSION_NAME, $data['session_native_id'], $data['session_expires'], $id];
    }

    /**
     * Delete session information.
     */
    public static function manuallyDeleteSessionInfo() {
        if (isset($_COOKIE[UserSession::DEFAULT_SESSION_NAME])) {
            $sessID = $_COOKIE[UserSession::DEFAULT_SESSION_NAME];
            E()->getDB()->modify(QAL::DELETE, 'share_session', NULL, ['session_native_id' => $sessID]);
        }
    }

    /**
     * Start session.
     * Actually the session in Energine starts as continuation of already existed session (created in auth.php).
     * If there are no information about the session in cookies or posts (there is an exception for flash uploader) then no session will start.
     * For captcha we need to force to start session.
     *
     * @param $force bool Force to create session if this is not necessary?
     *
     * @see index.php
     * @see auth.php
     *
     * @return UserSession
     */
    public static function start($force = false) {
        if (!self::$instance) {
            self::$instance = new UserSession($force);
        }

        if ($force) self::$instance->reload();
        return self::$instance;
    }

    public function getID() {
        return $this->id;
    }


    /**
     * Generate ID.
     *
     * @return string
     */
    public static function createIdentifier() {
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
            if (isset($_SESSION['userID'])) {
                $data['u_id'] = (int)$_SESSION['userID'];
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
}
