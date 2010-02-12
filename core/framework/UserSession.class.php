<?php

/**
 * Класс UserSession.
 *
 * @package energine
 * @subpackage core
 * @author dr.Pavka
 * @copyright Energine 2006
 * @version $Id$
 */

//require_once('core/framework/DBWorker.class.php');

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
     * @static
     * @var UserSession единый для всей системы экземпляр класса UserSession
     */
    private static $instance;

    /**
     * @access private
     * @var string пользовательский агент
     */
    private $userAgent;

    /**
     * @access private
     * @var string имя сеанса
     */
    private $name;

    /**
     * @access private
     * @var string имя таблицы сеансов в БД
     */
    private $tableName;

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
        $this->userAgent = isset($_SERVER['HTTP_USER_AGENT'])?$_SERVER['HTTP_USER_AGENT']:'ROBOT';
        $this->name = self::DEFAULT_SESSION_NAME;
        $this->tableName = 'share_session';
        ini_set('session.gc_probability', self::DEFAULT_PROBABILITY);

        // устанавливаем обработчики сеанса
        session_set_save_handler(
            array(&$this, 'open'),
            array(&$this, 'close'),
            array(&$this, 'read'),
            array(&$this, 'write'),
            array(&$this, 'destroy'),
            array(&$this, 'gc')
        );
        //register_shutdown_function('session_write_close');

        session_name($this->name);

        // устанавливаем время жизни cookie
        session_set_cookie_params($this->lifespan, $this->getConfigValue('site.root'));

        // проверяем существование cookie и корректность его данных
        if (isset($_COOKIE[$this->name])) {
            $this->phpSessId = $_COOKIE[$this->name];
            // проверяем, действителен ли текущий сеанс
            $res = $this->dbh->selectRequest(
                "SELECT session_id FROM {$this->tableName}".
                ' WHERE session_native_id = %s'.
                ' AND (NOW() - session_created) < %s'.
                ' AND (NOW() - session_last_impression) <= %s'.
                ' AND session_user_agent = %s',
                $this->phpSessId,
                $this->lifespan,
                $this->timeout,
                $this->userAgent
            );
            $response = Response::getInstance();
            if (is_array($res)) {
                $response->setCookie(
                    $this->name,
                    $this->phpSessId,
                    (time() + $this->lifespan),
                    $this->getConfigValue('site.root')
                );
            }
            else {
                $this->dbh->modify(QAL::DELETE, $this->tableName, null, "session_native_id = '{$this->phpSessId}'");
                // удаляем cookie сеанса
                $response->deleteCookie($this->name, $this->getConfigValue('site.root'));
            }
        }
        session_start();
    }

    /**
     * Возвращает единый для всей системы экземпляр класса UserSession.
     *
     * @access public
     * @static
     * @return UserSession
     */
    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new UserSession;
        }
        return self::$instance;
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
	 * Читает данные сеанса.
	 * Поскольку данный метод вызыватся сразу же после установки
	 * идентификатора сеанса, он используется вместо метода open.
	 *
	 * @access public
	 * @param string идентификатор сеанса
	 * @return mixed
	 */
    public function read($phpSessId) {
        $result = '';
        //dump_log(array($this->phpSessId, $phpSessId), true);
        $this->phpSessId = $phpSessId;

        $res = $this->dbh->select(
            $this->tableName,
            array('session_id', 'session_data'),
            array('session_native_id' => $this->phpSessId)
        );

        // если указанный сеанс существует в БД
        if (is_array($res)) {
            $res = $res[0];
            $this->id = $res['session_id'];
            $result = $res['session_data'];
        }
        // если такого сеанса в БД не существует
        else {
            try {
                // создаем новую запись сеанса
                $this->id = $this->dbh->modifyRequest(
                    "INSERT INTO {$this->tableName} (session_native_id, session_created, session_user_agent) VALUES(%s, NOW(), %s)",
                    $this->phpSessId, $this->userAgent
                );
            }
            catch (Exception $e){
                //dummy exception
            }
        }

        return $result;
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
        $this->dbh->modify(QAL::UPDATE, $this->tableName, array('session_data' => $data), array('session_native_id' => $phpSessId));
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
            '((NOW() - session_created) > '.$this->lifespan.') OR ((NOW() - session_last_impression) > '.$this->timeout.')'
        );
        return true;
    }

    /**
     * Стартует сеанс.
     *
     * @access public
     * @return void
     */
    public function start() {
        if ($this->phpSessId) {
        	$this->dbh->modifyRequest('UPDATE '.$this->tableName.' SET session_last_impression = NOW() WHERE session_native_id = %s', $this->phpSessId);
        }
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
