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


    private $isStarted = false;

    /**
     * Session ID.
     * @var string $phpSessId
     */
    private $phpSessId;

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
     * @throws \ErrorException
     */
    public function __construct() {
        if (session_status() == PHP_SESSION_DISABLED) throw new \ErrorException('Session must be enabled');

        $this->lifespan = Primitive::getConfigValue('session.lifespan');
        $this->userAgent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'NOBODY';
        ini_set('session.gc_probability', self::DEFAULT_PROBABILITY);
    }

    /**
     * Session initialization
     * if valid session data exists  - PHP session started
     * otherwise - session is not started
     *
     * @return $this
     * @throws SystemException
     */
    public function init() {
        if (!$this->isStarted && ($id = self::getSessionID())) {
            if ($this->load($id)) {
                $this->launch();
                $this->dbh->modify('UPDATE ' . self::$tableName . ' SET session_last_impression = UNIX_TIMESTAMP(), session_expires = (UNIX_TIMESTAMP() + %s) WHERE session_native_id = %s', $this->lifespan, $this->phpSessId);
            } else {
                $this->kill();
            }
        }

        return $this;
    }

    /**
     * Real PHP session start procedure
     */
    private function launch() {
        session_set_save_handler($this);
        session_name(self::DEFAULT_SESSION_NAME);

        // устанавливаем время жизни cookie
        if (Primitive::getConfigValue('site.domain')) {
            $path = '/';
            // bySD проверка на наличие порта в адресе. не обрабатывает ipv6
            if (substr_count(Primitive::getConfigValue('site.domain'),":")==1) {
	      $domain = '';
	    } else {
	      $domain = '.' . Primitive::getConfigValue('site.domain');	      
            }            
        } else {
            $path = E()->getSiteManager()->getCurrentSite()->root;
            $domain = '';
        }
        session_set_cookie_params($this->lifespan, $path, $domain);
        session_id($this->phpSessId);
        session_start();
        $this->isStarted = true;
    }

    /**
     * @param null $UID User ID - if NULL existing UID rewrited
     * @return $this
     * @throws SystemException
     */
    public function start($UID = NULL) {
        //if session not started
        if (!$this->init()->isStarted) {
            //Inserts session info into DB
            $data['session_created'] = $data['session_last_impression'] = time();
            $data['session_expires'] = $data['session_last_impression'] + $this->lifespan;
            $data['session_ip'] = E()->getRequest()->getClientIP(true);
            $this->phpSessId = $data['session_native_id'] = self::createIdentifier();
            $this->id = $this->dbh->modify(QAL::INSERT, self::$tableName, $data);
            //start PHP session
            $this->launch();
        }

        if ($UID != $this->UID) $_SESSION['UID'] = $UID;

        return $this;
    }


    /**
     * Check if the session is opened.
     *
     * @return bool
     */
    static private function getSessionID() {
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
        $this->UID = $this->data = $this->id = $this->phpSessId = $result = NULL;
        if ($sessionID) {
            $result = $this->dbh->select(
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

                $this->isStarted = $result = true;
            }
        }

        return $result;
    }


    /**
     * Delete session information.
     */
    public function kill() {
        if (isset($_COOKIE[UserSession::DEFAULT_SESSION_NAME])) {
            $sessionID = $_COOKIE[UserSession::DEFAULT_SESSION_NAME];
            unset($_COOKIE[UserSession::DEFAULT_SESSION_NAME]);
            E()->Response->deleteCookie(UserSession::DEFAULT_SESSION_NAME);
        }
        if (session_status() == PHP_SESSION_ACTIVE) {
            session_destroy();
        } elseif (isset($sessionID)) {
            $this->dbh->modify(QAL::DELETE, self::$tableName, NULL, ['session_native_id' => $sessionID]);
        }

        $this->isStarted = false;
    }

    /**
     * Creating session identifier
     * @return string
     */
    public static function createIdentifier() {
        return sha1(time() + rand(0, 10000));
    }

    /**
     *
     * @return int Session ID
     */
    public function getID() {
        return $this->id;
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
}
