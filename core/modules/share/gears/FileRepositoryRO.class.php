<?php

/**
 * Класс FileRepositoryRO
 *
 * @package energine
 * @subpackage kernel
 * @author Andy Karpov <andy.karpov@gmail.com>
 * @copyright Energine 2013
 */


/**
 * Реализация интерфейса загрузчика файлов для репозитариев типа Read-Only.
 * Используется в случаях, когда загрузка файлов в репозитарий осуществляется сторонними скриптами,
 * а интерфейс служит лишь для навигации по репозитарию
 *
 * @package energine
 * @subpackage kernel
 * @author Andy Karpov <andy.karpov@gmail.com>
 */
class FileRepositoryRO extends Object implements IFileRepository {

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
        return 'ro';
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
        return false;
    }

    /**
     * Возвращает true, если разрешена загрузка файлов в репозитарий
     *
     * @return boolean
     */
    public function allowsUploadFile() {
        return false;
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
        return false;
    }

    /**
     * Возвращает true, если разрешено удаление файла из репозитария
     *
     * @return boolean
     */
    public function allowsDeleteFile() {
        return false;
    }

    /**
     * Метод загрузки media-файла в хранилище
     *
     * @param string $sourceFilename
     * @param string $destFilename
     * @return boolean
     * @throws SystemException
     */
    public function uploadFile($sourceFilename, $destFilename) {
        throw new SystemException('ERR_REPOSITORY_READ_ONLY', SystemException::ERR_WARNING, $destFilename);
    }

    /**
     * Метод загрузки alt-файла в хранилище
     *
     * @param string $sourceFilename
     * @param string $destFilename
     * @param int $width
     * @param int $height
     * @return boolean
     * @throws SystemException
     */
    public function uploadAlt($sourceFilename, $destFilename, $width, $height) {
        throw new SystemException('ERR_REPOSITORY_READ_ONLY', SystemException::ERR_WARNING, $destFilename);
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
        throw new SystemException('ERR_REPOSITORY_READ_ONLY', SystemException::ERR_WARNING, $destFilename);
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
        throw new SystemException('ERR_REPOSITORY_READ_ONLY', SystemException::ERR_WARNING, $destFilename);
    }

    /**
     * Метод удаления media-файла из хранилища
     *
     * @param string $filename имя файла
     * @return boolean
     * @throws SystemException
     */
    public function deleteFile($filename) {
        throw new SystemException('ERR_REPOSITORY_READ_ONLY', SystemException::ERR_WARNING, $filename);
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
        throw new SystemException('ERR_REPOSITORY_READ_ONLY', SystemException::ERR_WARNING, $filename);
    }

    /**
     * Возвращает объект с мета-информацией файла (mime-тип, размер и тп)
     *
     * @param $filename
     * @return object
     * @throws SystemException
     */
    public function analyze($filename) {
        throw new SystemException('ERR_REPOSITORY_READ_ONLY', SystemException::ERR_WARNING, $filename);
    }

    /**
     * Метод создания директории в репозитарии
     *
     * @param string $dir
     * @return boolean
     * @throws SystemException
     */
    public function createDir($dir) {
        throw new SystemException('ERR_REPOSITORY_READ_ONLY', SystemException::ERR_WARNING, $dir);
    }

    /**
     * Метод переименования директории в хранилище
     *
     * @param string $dir
     * @return boolean
     * @throws SystemException
     */
    public function renameDir($dir) {
        throw new SystemException('ERR_REPOSITORY_READ_ONLY', SystemException::ERR_WARNING, $dir);
    }

    /**
     * Метод удаления директории из репозитария
     *
     * @param string $dir
     * @throws SystemException
     */
    public function deleteDir($dir) {
        throw new SystemException('ERR_REPOSITORY_READ_ONLY', SystemException::ERR_WARNING, $dir);
    }

}
