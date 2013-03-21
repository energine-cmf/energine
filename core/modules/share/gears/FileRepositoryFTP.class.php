<?php

/**
 * Класс FileRepositoryFTP
 *
 * @package energine
 * @subpackage kernel
 * @author Andy Karpov <andy.karpov@gmail.com>
 * @copyright Energine 2013
 */


/**
 * Реализация интерфейса загрузчика файлов для удаленных FTP репозитариев.
 * Используется в случаях, когда загрузка файлов в репозитарий осуществляется средствами админки,
 * но хранилище удаленное, через FTP
 *
 * @package energine
 * @subpackage kernel
 * @author Andy Karpov <andy.karpov@gmail.com>
 */
class FileRepositoryFTP extends Object implements IFileRepository {

    /**
     * Внутренний идентификатор репозитария
     *
     * @var int
     */
    protected $id;

    /**
     * Ресурс соединения по FTP
     *
     * @var resource
     */
    protected $conn_id;

    /**
     * Метод получения внутреннего имени реализации
     *
     * @return string
     */
    public function getName() {
        return 'ftp';
    }

    /**
     * Метод установки идентификатора репозитария (upl_id)
     *
     * @param int $id
     * @return IFileRepository
     */
    public function setId($id) {
        $this->id = $id;
        return $this;
    }

    /**
     * Метод получения идентификатора репозитария (upl_id)
     *
     * @return int
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Возвращает true, если разрешено создание папок в репозитарии
     *
     * @return boolean
     */
    public function allowsCreateDir() {
        return true;
    }

    /**
     * Возвращает true, если разрешена загрузка файлов в репозитарий
     *
     * @return boolean
     */
    public function allowsUploadFile() {
        return true;
    }

    /**
     * Возвращает true, если разрешено редактирование папки в репозитарии
     *
     * @return boolean
     */
    public function allowsEditDir() {
        return true;
    }

    /**
     * Возвращает true, если разрешено редактирование файла в репозитарии
     *
     * @return boolean
     */
    public function allowsEditFile() {
        return true;
    }

    /**
     * Возвращает true, если разрешено удаление папки из репозитария
     *
     * @return boolean
     */
    public function allowsDeleteDir() {
        return true;
    }

    /**
     * Возвращает true, если разрешено удаление файла из репозитария
     *
     * @return boolean
     */
    public function allowsDeleteFile() {
        return true;
    }

    protected function connect() {

        $cfg = E()->getConfigValue('repositories.ftp');
        if (empty($cfg)) {
            throw new SystemException('ERR_MISSING_FTP_CONFIG');
        }

        $this->conn_id = ftp_connect($cfg['server'], $cfg['port']);
        if (!$this->conn_id) return false;

        $login_result = ftp_login($this->conn_id, $cfg['username'], $cfg['password']);
        if (!$login_result) return false;

        ftp_pasv($this->conn_id, true);

        return true;
    }

    protected function disconnect() {
        if ($this->connected()) {
            ftp_close($this->conn_id);
            $this->conn_id = false;
            return true;
        }
        return false;
    }

    protected function connected() {
        return is_resource($this->conn_id);
    }

    /**
     * Метод загрузки файла в хранилище
     *
     * @param string $filename имя файла
     * @param string $data данные
     * @return boolean
     * @throws SystemException
     */
    public function uploadFile($filename, $data) {

        if (!$this->connect()) return false;

        $source_file = FileRepository::getTmpFilePath($filename);
        file_put_contents($source_file, $data);

        $dirname = dirname($filename);
        $basename = basename($filename);

        if ($dirname) {
            $this->createDir($dirname);
        }

        $result = ftp_put($this->conn_id, $basename, $source_file, FTP_BINARY);

        unlink($source_file);

        $this->disconnect();

        return $result;
    }

    /**
     * Метод обновления ранее загруженного файла в хранилище
     *
     * @param string $filename имя файла
     * @param string $data данные
     * @return boolean
     * @throws SystemException
     */
    public function updateFile($filename, $data) {
        throw new SystemException('ERR_UNIMPLEMENTED_YET');
    }

    /**
     * Метод удаления файла из хранилища
     *
     * @param string $filename имя файла
     * @return boolean
     * @throws SystemException
     */
    public function deleteFile($filename) {
        throw new SystemException('ERR_UNIMPLEMENTED_YET');
    }

    /**
     * Метод создания директории в репозитарии
     *
     * @param string $dir
     * @return boolean
     * @throws SystemException
     */
    public function createDir($dir) {

        $initially_connected = $this->connected();

        if (!$initially_connected) {
            $this->connect();
        }

        // рекурсивно переходит (и создает отсутствующие директории) в заданную директорию на ftp
        if ($dir) {
            $dirs = explode('/', $dir);
            if ($dirs) {
                foreach($dirs as $d) {
                    if ($d) {
                        $list = ftp_nlist($this->conn_id, '.');
                        if (in_array($d, $list)) {
                            ftp_chdir($this->conn_id, $d);
                        } else {
                            ftp_mkdir($this->conn_id, $d);
                            ftp_chdir($this->conn_id, $d);
                        }
                    }
                }
            }
        }

        if (!$initially_connected) {
            $this->disconnect();
        }

        return true;
    }

    /**
     * Метод переименования директории в хранилище
     *
     * @param string $dir
     * @return boolean
     * @throws SystemException
     */
    public function renameDir($dir) {
        throw new SystemException('ERR_UNIMPLEMENTED_YET');
    }

    /**
     * Метод удаления директории из репозитария
     *
     * @param string $dir
     * @throws SystemException
     */
    public function deleteDir($dir) {
        throw new SystemException('ERR_UNIMPLEMENTED_YET');
    }
}
