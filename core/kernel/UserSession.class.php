<?php

/**
 * Класс UserSession.
 *
 * @package energine
 * @subpackage kernel
 * @author dr.Pavka
 * @copyright Energine 2011
 */

/**
 * Класс управления сеансами пользователей.
 *
 * @package energine
 * @subpackage kernel
 * @author dr.Pavka
 * @final
 */
final class UserSession extends DBWorker {
    /**
     * Имя куки которое бросается при неудачной попытке входа
     * @see auth.php
     * Вообще то должо находиться в LoginForm
     * Но изза неудобства вызова перенесена сюда
     * @todo Все таки надо как то ей указать надлежащее место
     */
    const FAILED_LOGIN_COOKIE_NAME = 'failed_login';
    /**
     * Флаг не позволяющий вызвать объект UserSession напрямую
     * только через UserSession::start
     * а приватный конструктор  для
     * @var bool
     */
    private static $instance = false;
    /**
     * Имя сеанса по-умолчанию.
     */
    const DEFAULT_SESSION_NAME = 'NRGNSID';

    /**
     * Вероятность вызова сборщика мусора.
     * Вычисляется как DEFAULT_PROBABILITY / session.gc_divisor (defaults to 100).
     * Например, 10 / 100 означает 10%-вероятность вызова СМ.
     */
    const DEFAULT_PROBABILITY = 10;

    /**
     * @access private
     * @var string идентификатор сеанса
     */
    private $phpSessId;

    /**
     * Если период между запросами превышает эту величину, сеанс становится недействительным.
     *
     * @var int время ожидания
     * @access private
     */
    private $timeout;

    /**
     * Используется для настройки времени жизни cookie и сборки мусора.
     *
     * @var int максимальное время жизни сеанса
     * @access private
     */
    private $lifespan;

    /**
     * @access private
     * @var string пользовательский агент
     */
    private $userAgent;

    /**
     * Данные сеанса
     * false - сеанс существует но данных нет
     * string - сеанс существует и данные такие
     * null - сеанса нет существует
     *
     * @var string
     */
    private $data = null;
    /**
     * @access private
     * @var string имя таблицы сеансов в БД
     * @static
     */
    static private $tableName = 'share_session';


    /**
     * Конструктор класса.
     *
     * @access private
     * @return void
     */
    public function __construct($force = false) {
        if (!self::$instance) {
            throw new SystemException('ERR_NO_CONSTRUCTOR');
        }

        parent::__construct();
        $this->timeout = $this->getConfigValue('session.timeout');
        $this->lifespan = $this->getConfigValue('session.lifespan');
        $this->userAgent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'ROBOT';

        ini_set('session.gc_probability', self::DEFAULT_PROBABILITY);
        // устанавливаем обработчики сеанса
        session_set_save_handler(
            array($this, 'open'),
            array($this, 'close'),
            array($this, 'read'),
            array($this, 'write'),
            array($this, 'destroy'),
            array($this, 'gc')
        );
        session_name(self::DEFAULT_SESSION_NAME);
        $this->data = false;
        if ($this->phpSessId = self::isOpen()) {
            $this->data = self::isValid($this->phpSessId);
            //Если сессия валидная
            if (!is_null($this->data)) {
                E()->getDB()->modifyRequest('UPDATE ' . self::$tableName . ' SET session_last_impression = UNIX_TIMESTAMP(), session_expires = (UNIX_TIMESTAMP() + %s) WHERE session_native_id = %s', $this->lifespan, $this->phpSessId);
            } elseif ($force) {
                //создаем ее вручную
                $sessInfo = self::manuallyCreateSessionInfo();
                $this->phpSessId = $sessInfo[1];
            }
            //сессия невалидная
            else {
                $this->dbh->modify(QAL::DELETE, self::$tableName, null, array("session_native_id" => addslashes($this->phpSessId)));
                // удаляем cookie сеанса
                E()->getResponse()->deleteCookie(self::DEFAULT_SESSION_NAME);
                return;
            }
        } elseif ($force) {
            //создаем ее вручную
            $sessInfo = self::manuallyCreateSessionInfo();
            $this->phpSessId = $sessInfo[1];
        }
        else {
            return;
        }

        // устанавливаем время жизни cookie
        if ($this->getConfigValue('site.domain')) {
            $path = '/';
            $domain = '.' . $this->getConfigValue('site.domain');
        } else {
            $path = E()->getSiteManager()->getCurrentSite()->root;
            $domain = '';
        }
        session_set_cookie_params($this->lifespan, $path, $domain);
        session_id($this->phpSessId);
        session_start();
    }

    /**
     * @static
     *
     */
    static public function isOpen() {
        return (isset($_COOKIE[self::DEFAULT_SESSION_NAME]) && !empty($_COOKIE[self::DEFAULT_SESSION_NAME]))?$_COOKIE[self::DEFAULT_SESSION_NAME]:false;
    }

    /**
     * Проверям , действителен ли сеанс с этим идентификатором
     * если да - то возвращает еще и данные сеанса
     * может не очень красиво, но выгоднее, чтоб секономить на запросах
     *
     * @param  $sessID
     * @return mixed | false
     * @static
     */
    static public function isValid($sessID) {
        // проверяем
        $res = E()->getDB()->select(
            'SELECT session_id, session_data FROM ' . self::$tableName .
                ' WHERE session_native_id = %s ' .
                ' AND session_expires >= UNIX_TIMESTAMP()',
            addslashes($sessID)
        );
        return (!is_array($res)) ? false : $res[0]['session_data'];
    }

    /**
     * Создает в БД информацию о новой сессии
     * возвращает информацию о куках для нее
     *
     * @static
     * @param  $UID int || false информация о идентификаторе пользователя
     * @param bool $expires
     *
     * @return array для Response::addCookie
     */
    public static function manuallyCreateSessionInfo($UID = false, $expires = false) {
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
        E()->getDB()->modify(QAL::INSERT, 'share_session', $data);
        $_COOKIE[self::DEFAULT_SESSION_NAME] = $data['session_native_id'];

        return array(self::DEFAULT_SESSION_NAME, $data['session_native_id'], $data['session_expires']);
    }

    /**
     * @static
     * @return void
     */
    public static function manuallyDeleteSessionInfo() {
        if (isset($_COOKIE[UserSession::DEFAULT_SESSION_NAME])) {
            $sessID = $_COOKIE[UserSession::DEFAULT_SESSION_NAME];
            E()->getDB()->modify(QAL::DELETE, 'share_session', null, array('session_native_id' => $sessID));
        }
    }

    /**
     * Стартует сеанс
     * На самом деле в Enrgine сеанс стартуется только как продолжение уже имеющегося(созданного в auth.php)
     * и если нет ифнормации о сеансе в куках или посте(исключение для флеш аплоадера)- то никакой сессии и не будет
     * но в captcha нам нужно стартовать принудительно
     *
     * @param $force bool создавать сессию даже когда в этом нет необходимости
     * @see index.php
     * @see auth.php
     * @access public
     * @return void
     */
    public static function start($force = false) {
        if (self::$instance) {
            throw new SystemException('ERR_SESSION_ALREADY_STARTED');
        }
        self::$instance = true;

        new UserSession($force);
    }

    public static function createIdentifier() {
        return sha1(time() + rand(0, 10000));
    }

    /**
     * Открывает сеанс.
     *
     * @access public
     * @param string $savePath
     * @param string $sessionName
     * @return boolean
     */
    public function open($savePath, $sessionName) {
        return true;
    }

    /**
     * Закрывает сеанс.
     *
     * @access public
     * @return bool
     */
    public function close() {
        return true;
    }

    /**
     * возвращает  данные сеанса.
     *
     * @access public
     * @see UserSession::data
     * @param string идентификатор сеанса
     * @return mixed
     */
    public function read($phpSessId) {
        return ($this->data);
    }

    /**
     * Записывает данные сеанса.
     *
     * @access public
     * @param string идентификатор сеанса
     * @param mixed данные
     * @return mixed
     */
    public function write($phpSessId, $data) {
        if (!empty($data)) {
            $this->data = $data;
            $data = array('session_data' => $data);
            if (isset($_SESSION['userID'])) {
                $data['u_id'] = (int)$_SESSION['userID'];
            }

            $this->dbh->modify(QAL::UPDATE, self::$tableName, $data, array('session_native_id' => $phpSessId));
        }
        return true;
    }

    /**
     * Уничтожает сеанс.
     *
     * @access public
     * @param string идентификатор сеанса
     * @return bool
     */
    public function destroy($phpSessId) {
        return $this->dbh->modify(QAL::DELETE, self::$tableName, null, array('session_native_id' => $phpSessId));
    }

    /**
     * Сборщик мусора.
     *
     * @access public
     * @param int максимальное время жизни сеанса
     * @return bool
     */
    public function gc($maxLifeTime) {
        $this->dbh->modify(
            QAL::DELETE,
            self::$tableName,
            null,
            'session_expires < UNIX_TIMESTAMP()'
        );
        return true;
    }
}
