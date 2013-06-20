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

    // путь FTP начиная от FTP root для загрузки alt-файлов
    const IMAGE_ALT_CACHE = 'resizer/w[width]-h[height]/[upl_path]';

    /**
     * Внутренний идентификатор репозитария
     *
     * @var int
     */
    protected $id;

    /**
     * Базовый путь к репозитрию
     *
     * @var string
     */
    protected $base;

    /**
     * Объект FTP для загрузки файлов
     *
     * @var FTP
     */
    protected $ftp_media;

    /**
     * Объект FTP для загрузки alts
     *
     * @var FTP
     */
    protected $ftp_alts;

    /**
     * Конструктор класса
     *
     * @param int $id
     * @param string $base
     * @throws SystemException
     */
    public function __construct($id, $base) {

        $this->setId($id);
        $this->setBase($base);

        $cfg_ftp_media = E()->getConfigValue('repositories.ftp.' . $base . '.media');
        $cfg_ftp_alts = E()->getConfigValue('repositories.ftp.' . $base . '.alts');

        if (empty($cfg_ftp_media)) {
            throw new SystemException('ERR_MISSING_MEDIA_FTP_CONFIG');
        }

        if (empty($cfg_ftp_alts)) {
            throw new SystemException('ERR_MISSING_ALTS_FTP_CONFIG');
        }

        $this->ftp_media = new FTP(
            $cfg_ftp_media['server'],
            $cfg_ftp_media['port'],
            $cfg_ftp_media['username'],
            $cfg_ftp_media['password']
        );

        $this->ftp_alts = new FTP(
            $cfg_ftp_alts['server'],
            $cfg_ftp_alts['port'],
            $cfg_ftp_alts['username'],
            $cfg_ftp_alts['password']
        );
    }

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
     * Метод установки базового пути репозитария (upl_path)
     *
     * @param string $base
     * @return IFileRepository
     */
    public function setBase($base) {
        $this->base = $base;
        return $this;
    }

    /**
     * Метод получения базового пути репозитария (upl_path)
     *
     * @return string
     */
    public function getBase() {
        return $this->base;
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

    /**
     * Метод загрузки media-файла в хранилище
     *
     * @param string $sourceFilename
     * @param string $destFilename
     * @return boolean|object
     * @throws SystemException
     */
    public function uploadFile($sourceFilename, $destFilename) {

        try {
            $base = $this->getBase() . '/';
            if (strpos($destFilename, $base) === 0) {
                $destFilename = substr($destFilename, strlen($base));
            }
            $this->ftp_media->connect();
            $result = $this->ftp_media->uploadFile($sourceFilename, $destFilename);
            $fi = false;
            if ($result) {
                $fi = $this->analyze($sourceFilename);
            }
            $this->ftp_media->disconnect();
            return $fi;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Метод загрузки alt-файла в хранилище
     *
     * @param string $sourceFilename
     * @param string $destFilename
     * @param int $width
     * @param int $height
     * @return boolean|object
     * @throws SystemException
     */
    public function uploadAlt($sourceFilename, $destFilename, $width, $height) {

        $destFilename = str_replace(
            array('[width]', '[height]', '[upl_path]'),
            array($width, $height, $destFilename),
            self::IMAGE_ALT_CACHE
        );

        try {
            $this->ftp_alts->connect();
            $result = $this->ftp_alts->uploadFile($sourceFilename, $destFilename);
            $fi = false;
            if ($result) {
                $fi = $this->analyze($sourceFilename);
            }
            $this->ftp_alts->disconnect();
            return $fi;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Метод обновления ранее загруженного media-файла в хранилище
     *
     * @param string $sourceFilename
     * @param string $destFilename
     * @return boolean
     * @throws SystemException
     */
    public function updateFile($sourceFilename, $destFilename) {
        try {
            $base = $this->getBase() . '/';
            if (strpos($destFilename, $base) === 0) {
                $destFilename = substr($destFilename, strlen($base));
            }
            $this->ftp_media->connect();
            $result = $this->ftp_media->uploadFile($sourceFilename, $destFilename);
            $fi = false;
            if ($result) {
                $fi = $this->analyze($sourceFilename);
            }
            $this->ftp_media->disconnect();
            return $fi;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Метод обновления ранее загруженного alt-файла в хранилище
     *
     * @param string $sourceFilename
     * @param string $destFilename
     * @param int $width
     * @param int $height
     * @return boolean
     * @throws SystemException
     */
    public function updateAlt($sourceFilename, $destFilename, $width, $height) {
        throw new SystemException('ERR_UNIMPLEMENTED_YET');
    }

    /**
     * Метод удаления media-файла из хранилища
     *
     * @param string $filename имя файла
     * @return boolean
     * @throws SystemException
     */
    public function deleteFile($filename) {
        throw new SystemException('ERR_UNIMPLEMENTED_YET');
    }

    /**
     * Метод удаления alt-файла из хранилища
     *
     * @param string $filename имя файла
     * @param int $width
     * @param int $height
     * @return boolean
     * @throws SystemException
     */
    public function deleteAlt($filename, $width, $height) {
        throw new SystemException('ERR_UNIMPLEMENTED_YET');
    }

    /**
     * Возвращает объект с мета-информацией файла (mime-тип, размер и тп)
     *
     * @param $filename
     * @return object
     * @throws SystemException
     */
    public function analyze($filename) {
        $fi = E()->FileRepoInfo->analyze($filename, true);
        if (is_object($fi)) {
            $fi->ready = true;
        }
        return $fi;
    }

    /**
     * Метод создания директории в репозитарии
     *
     * @param string $dir
     * @return boolean
     * @throws Exception|SystemException
     */
    public function createDir($dir) {

        $initially_connected = $this->ftp_media->connected();

        try {

            $base = $this->getBase() . '/';
            if (strpos($dir, $base) === 0) {
                $dir = substr($dir, strlen($base));
            }

            if (!$initially_connected) {
                $this->ftp_media->connect();
            }

            $this->ftp_media->createDir($dir);

            if (!$initially_connected) {
                $this->ftp_media->disconnect();
            }
        } catch (Exception $e) {
            return false;
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
