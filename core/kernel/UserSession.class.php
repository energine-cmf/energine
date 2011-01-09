<?php

/**
 * Класс UserSession.
 *
 * @package energine
 * @subpackage core
 * @author dr.Pavka
 * @copyright Energine 2011
 */

/**
 * Класс управления сеансами пользователей.
 *
 * @package energine
 * @subpackage core
 * @author dr.Pavka
 * @final
 */
final class UserSession extends DBWorker {
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
     * @access private
     * @var int идентификатор сеанса в БД
     */
    private $id = false;

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
     * @access private
     * @var string имя таблицы сеансов в БД
     */
    private $tableName;
    /**
     * Данные сеанса
     * false - сеанс существует но данных нет
     * mixed - сеанс существует и данные такие
     * null - сеанса нет существует
     *
     * @var mixed
     */
    private $data = null;

    private $isActive = false;

    /**
     * Конструктор класса.
     *
     * @access private
     * @return void
     */
    public function __construct() {
        parent::__construct();
        $this->timeout = $this->getConfigValue('session.timeout');
        $this->lifespan = $this->getConfigValue('session.lifespan');
        $this->userAgent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'ROBOT';
        $this->tableName = 'share_session';
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
        // устанавливаем время жизни cookie
        if ($this->getConfigValue('site.domain')) {
            $path = '/';
            $domain = '.' . $this->getConfigValue('site.domain');
        }
        else {
            $path = SiteManager::getInstance()->getCurrentSite()->root;
            $domain = '';
        }
        $expires = time();

        if (
            (isset($_COOKIE[self::DEFAULT_SESSION_NAME]))
            ||
            (isset($_POST[self::DEFAULT_SESSION_NAME]))
        ) {
            $this->phpSessId = (isset($_COOKIE[self::DEFAULT_SESSION_NAME])) ? $_COOKIE[self::DEFAULT_SESSION_NAME] : $_POST[self::DEFAULT_SESSION_NAME];
            $response = E()->getResponse();

            if ($this->data = $this->isSessionValid($this->phpSessId)) {

                $expires += $this->lifespan;
                $this->isActive = true;
                E()->getDB()->modifyRequest('UPDATE ' . $this->tableName . ' SET session_last_impression = UNIX_TIMESTAMP(), session_expires = %s WHERE session_native_id = %s', $this->phpSessId, $expires);
                session_id($this->phpSessId);
            }
            else {
                $this->dbh->modify(QAL::DELETE, $this->tableName, null, array("session_native_id" => addslashes($this->phpSessId)));
                // удаляем cookie сеанса
                $response->deleteCookie(self::DEFAULT_SESSION_NAME);
            }

        }
        session_set_cookie_params($expires, $path, $domain);
    }

    /**
     * Проверям , действителен ли сеанс с этим идентификатором
     * если да - то возвращает еще и данные сеанса
     * @param  $sessID
     * @return mixed | false
     */
    private function isSessionValid($sessID) {
        // проверяем
        $res = $this->dbh->selectRequest(
            'SELECT session_id, session_data FROM ' . $this->tableName .
                    ' WHERE session_native_id = %s ' .
                    ' AND session_expires >= UNIX_TIMESTAMP()'/*.
                ' AND session_user_agent = %s'*/,
            addslashes($sessID)
        );
        return (!is_array($res))?false:$res[0]['session_data'];
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
        $data['session_native_id'] = sha1(time() + rand(0, 10000));
        $data['session_created'] = $data['session_last_impression'] = time();
        if(!$expires)
            $data['session_expires'] = $data['session_created'] + 60;
        else
            $data['session_expires'] = $expires;

        if($UID) {
            $data['u_id'] = $UID;
            $data['session_data'] = serialize(array('userID'=> $UID));
        }

        $data['session_ip'] = E()->getRequest()->getClientIP(true);
        E()->getDB()->modify(QAL::INSERT, 'share_session', $data);
        $_COOKIE[self::DEFAULT_SESSION_NAME] = $data['session_native_id'];
        
        return array(self::DEFAULT_SESSION_NAME, $data['session_native_id'], $data['session_expires']);
    }

    /**
     * Стартует сеанс
     * На самом деле в Enrgine сеанс стартуется только как продолжение уже имеющегося(созданного в auth.php)
     * и если нет ифнормации о сеансе в куках или посте(исключение для флеш аплоадера)- то никакой сессии и не будет
     * но в captcha нам нужно стартовать принудительно
     *
     * @param $force bool создавать сессию даже когда в этом нет необходимости
     * @see index.php
     * @see captcha.php
     * @see auth.php
     * @access public
     * @return void
     */
    public static function start($force = false) {
        if($force){
            UserSession::manuallyCreateSessionInfo();
        }
        
        $sess = new UserSession();

        if ($sess->isActive || $force) {
            session_start();
        }


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
                $data['u_id'] = (int) $_SESSION['userID'];
            }

            $this->dbh->modify(QAL::UPDATE, $this->tableName, $data, array('session_native_id' => $phpSessId));
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
        return $this->dbh->modify(QAL::DELETE, $this->tableName, null, array('session_native_id' => $phpSessId));
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
            $this->tableName,
            null,
                '(session_created < (NOW() - ' . $this->lifespan . ')) OR (session_last_impression  < (NOW() - ' . $this->timeout . '))'
        );
        return true;
    }

    /**
     * Возвращает идентификатор сеанса.
     *
     * @access public
     * @return int
     */
    public function getID() {
        return $this->id;
    }
}
