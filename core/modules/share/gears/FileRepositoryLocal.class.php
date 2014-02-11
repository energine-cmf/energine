<?php

/**
 * Класс FileRepositoryLocal
 *
 * @package energine
 * @subpackage kernel
 * @author Andy Karpov <andy.karpov@gmail.com>
 * @copyright Energine 2013
 */


/**
 * Реализация интерфейса загрузчика файлов для локальных репозитариев.
 * Используется в случаях, когда загрузка файлов в репозитарий осуществляется средствами админки
 *
 * @package energine
 * @subpackage kernel
 * @author Andy Karpov <andy.karpov@gmail.com>
 */
class FileRepositoryLocal extends Object implements IFileRepository {

    //Путь к кешу для альтернативных картинок
    const IMAGE_ALT_CACHE = 'uploads/alts/resizer/w[width]-h[height]/[upl_path]';

    /**
     * Внутренний идентификатор репозитария
     *
     * @var int
     */
    protected $id;

    /**
     * Базовый путь к репозитарию
     *
     * @var string
     */
    protected $base;

    /**
     * Конструктор класса
     *
     * @param int $id
     * @param string $base
     */
    public function __construct($id, $base) {
        $this->setId($id);
        $this->setBase($base);
    }

    /**
     * Метод получения внутреннего имени реализации
     *
     * @return string
     */
    public function getName() {
        return 'local';
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
     * Метод загрузки медиа-файла в хранилище
     *
     * @param string $sourceFilename
     * @param string $destFilename
     * @return boolean
     * @throws SystemException
     */
    public function uploadFile($sourceFilename, $destFilename) {

        $dir = dirname($destFilename);
        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }
        if(!is_writable($dir)){
            throw new SystemException('ERR_DIR_WRITE', SystemException::ERR_CRITICAL, $dir);
        }
        if (!copy($sourceFilename, $destFilename)) {
            throw new SystemException('ERR_COPY_UPLOADED_FILE', SystemException::ERR_CRITICAL, $destFilename);
        }

        return $this->analyze($destFilename);
    }

    /**
     * Метод загрузки alts-файла в хранилище
     *
     * @param string $sourceFilename
     * @param string $destFilename
     * @param int $width
     * @param int $height
     * @return boolean
     * @throws SystemException
     */
    public function uploadAlt($sourceFilename, $destFilename, $width, $height) {
        $destFilename = str_replace(
            array('[width]', '[height]', '[upl_path]'),
            array($width, $height, $destFilename),
            self::IMAGE_ALT_CACHE
        );
        return $this->uploadFile($sourceFilename, $destFilename);
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
        if (!copy($sourceFilename, $destFilename)) {
            return false;
        }
        return true;
    }

    /**
     * Метод обновления ранее загруженного alts-файла в хранилище
     *
     * @param string $sourceFilename
     * @param string $destFilename
     * @param int $width
     * @param int $height
     * @return boolean
     * @throws SystemException
     */
    public function updateAlt($sourceFilename, $destFilename, $width, $height) {
        $destFilename = str_replace(
            array('[width]', '[height]', '[upl_path]'),
            array($width, $height, $destFilename),
            self::IMAGE_ALT_CACHE
        );
        return $this->updateFile($sourceFilename, $destFilename);
    }

    /**
     * Метод удаления файла из хранилища
     *
     * @param string $filename имя файла
     * @return boolean
     * @throws SystemException
     */
    public function deleteFile($filename) {
        if (!@unlink($filename)) {
            return false;
        }
        return true;
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
        return $this->deleteFile($filename);
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
     * @throws SystemException
     */
    public function createDir($dir) {
        if (file_exists($dir)) return true;
        $dirs = array_filter(explode('/', $dir));
        array_pop($dirs);
        $parentDir = implode('/', $dirs);
        if(!file_exists($parentDir) || !is_writable($parentDir)){
            throw new SystemException('ERR_DIR_CREATE', SystemException::ERR_CRITICAL, $parentDir);
        }
        return mkdir($dir);
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

    /**
     * @copydoc IFileRepository::prepare
     */
    public function prepare(&$data) {
        return $data;
    }

    /**
     * @copydoc IFileRepository::setPrepareFunction
     * @throws SystemException 'ERR_NOT_USED'
     */
    public function setPrepareFunction($func) {
        throw new SystemException('ERR_NOT_USED');
    }
}
