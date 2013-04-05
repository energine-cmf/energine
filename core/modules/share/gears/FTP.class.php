<?php

/**
 * Класс FTP
 *
 * @package energine
 * @subpackage kernel
 * @author Andy Karpov <andy.karpov@gmail.com>
 * @copyright Energine 2013
 */


/**
 * Класс-обертка для работы со встроенными функциями php для заливки файлов на FTP
 *
 * @package energine
 * @subpackage kernel
 * @author Andy Karpov <andy.karpov@gmail.com>
 */
class FTP extends Object {

    /**
     * Ресурс соединения по FTP
     *
     * @var resource
     */
    protected $conn_id;

    /**
     * Адрес FTP сервера
     *
     * @var string
     */
    protected $server;

    /**
     * FTP порт
     *
     * @var int
     */
    protected $port;

    /**
     * FTP Логин
     *
     * @var string
     */
    protected $username;

    /**
     * FTP пароль
     *
     * @var string
     */
    protected $password;

    /**
     * Конструктор класса
     *
     * @param $server
     * @param $port
     * @param $username
     * @param $password
     */
    public function __construct($server, $port, $username, $password) {
        $this->server = $server;
        $this->port = $port;
        $this->username = $username;
        $this->password = $password;
    }

    /**
     * Метод соединения и авторизации на ftp-сервере
     *
     * @return bool
     * @throws SystemException
     */
    public function connect() {

        $this->conn_id = ftp_connect($this->server, $this->port);
        if (!$this->conn_id) throw new SystemException('ERR_CONNECT_FTP', SystemException::ERR_CRITICAL, $this->server);

        $login_result = ftp_login($this->conn_id, $this->username, $this->password);
        if (!$login_result) throw new SystemException('ERR_FRP_LOGIN_PASSWORD', SystemException::ERR_CRITICAL, $this->username);

        ftp_pasv($this->conn_id, true);

        return true;
    }

    /**
     * Метод отсоединения от ftp-сервера
     *
     * @return bool
     */
    public function disconnect() {
        if ($this->connected()) {
            ftp_close($this->conn_id);
            $this->conn_id = false;
            return true;
        }
        return false;
    }

    /**
     * Возвращает true, если соединение уже было установлено
     *
     * @return bool
     */
    public function connected() {
        return is_resource($this->conn_id);
    }

    /**
     * Метод загрузки файла
     *
     * @param string $sourceFilename
     * @param string $destFilename
     * @return boolean
     * @throws SystemException
     */
    public function uploadFile($sourceFilename, $destFilename) {

        if (!$this->connected()) throw new SystemException('ERR_FTP_NOT_CONNECTED', SystemException::ERR_CRITICAL);

        $dirname = dirname($destFilename);
        $basename = basename($destFilename);

        if ($dirname) {
            $this->createDir($dirname);
        }

        // delete existing file before ftp_put, if any
        $list = ftp_nlist($this->conn_id, '.');
        if (is_array($list) and in_array($basename, $list)) {
            ftp_delete($this->conn_id, $basename);
        }

        return ftp_put($this->conn_id, $basename, $sourceFilename, FTP_BINARY);
    }

    /**
     * Метод создания директории
     *
     * @param string $dir
     * @return boolean
     * @throws SystemException
     */
    public function createDir($dir) {

        if (!$this->connected()) throw new SystemException('ERR_FTP_NOT_CONNECTED', SystemException::ERR_CRITICAL);

        // рекурсивно переходит (и создает отсутствующие директории) в заданную директорию на ftp
        if ($dir and $dir != '.') {
            $dirs = explode('/', $dir);
            if ($dirs) {
                foreach($dirs as $d) {
                    if (!empty($d)) {
                        $list = ftp_nlist($this->conn_id, '.');
                        if (is_array($list) and in_array($d, $list)) {
                            ftp_chdir($this->conn_id, $d);
                        } else {
                            ftp_mkdir($this->conn_id, $d);
                            ftp_chdir($this->conn_id, $d);
                        }
                    }
                }
            }
        }

        return true;
    }
}
