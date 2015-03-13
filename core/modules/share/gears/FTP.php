<?php
/**
 * @file
 * FTP.
 *
 * It contains the definition to:
 * @code
class FTP;
@endcode
 *
 * @author Andy Karpov <andy.karpov@gmail.com>
 * @copyright Energine 2013
 *
 * @version 1.0.0
 */

namespace Energine\share\gears;
/**
 * Upload files over FTP.
 *
 * This is class-wrapper with build-in PHP functions for uploading files over FTP.
 *
 * @code
class FTP;
@endcode
 */
class FTP extends Object {
    /**
     * Connection resource over FTP.
     * @var resource $conn_id
     */
    protected $conn_id;

    /**
     * FTP-Server address.
     * @var string $server
     */
    protected $server;

    /**
     * FTP port.
     * @var int $port
     */
    protected $port;

    /**
     * FTP username.
     * @var string $username
     */
    protected $username;

    /**
     * FTP password.
     * @var string $password
     */
    protected $password;

    /**
     * @param string $server FTP address.
     * @param string $port Port.
     * @param string $username Username.
     * @param string $password Password.
     */
    public function __construct($server, $port, $username, $password) {
        $this->server = $server;
        $this->port = $port;
        $this->username = $username;
        $this->password = $password;
    }

    //todo VZ: Why only true is returned?
    /**
     * Connect to FTP.
     * It connects and authorize the user on the FTP-server.
     *
     * @return bool
     *
     * @throws SystemException 'ERR_CONNECT_FTP'
     * @throws SystemException 'ERR_FRP_LOGIN_PASSWORD'
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
     * Disconnect FTP-Server.
     * It returns false if the server was not connected.
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
     * Check if the server is connected.
     *
     * @return bool
     */
    public function connected() {
        return is_resource($this->conn_id);
    }

    /**
     * Upload file.
     *
     * @param string $sourceFilename Source filename.
     * @param string $destFilename Destination filename.
     * @return boolean
     *
     * @throws SystemException 'ERR_FTP_NOT_CONNECTED'
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

    //todo VZ: Why only true is returned?
    /**
     * Create directory.
     *
     * @param string $dir Directory name.
     * @return boolean
     *
     * @throws SystemException 'ERR_FTP_NOT_CONNECTED'
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
