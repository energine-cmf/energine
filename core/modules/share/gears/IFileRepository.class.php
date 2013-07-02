<?php

/**
 * Интерфейс IFileRepository
 *
 * @package energine
 * @subpackage kernel
 * @author Andy Karpov <andy.karpov@gmail.com>
 * @copyright Energine 2013
 */


/**
 * Интерфейс загрузчика файлов.
 *
 * @package energine
 * @subpackage kernel
 * @author Andy Karpov <andy.karpov@gmail.com>
 */
interface IFileRepository {

    /**
     * Конструктор класса
     *
     * @param int $id
     * @param string $base
     */
    public function __construct($id, $base);

    /**
     * Метод получения внутреннего имени реализации
     *
     * @return string
     */
    public function getName();

    /**
     * Метод установки идентификатора репозитария (upl_id)
     *
     * @param int $id
     * @return IFileRepository
     */
    public function setId($id);

    /**
     * Метод получения идентификатора репозитария (upl_id)
     *
     * @return int
     */
    public function getId();

    /**
     * Метод установки базового пути репозитария (upl_path)
     *
     * @param string $base
     * @return IFileRepository
     */
    public function setBase($base);

    /**
     * Метод получения базового пути репозитария (upl_path)
     *
     * @return string
     */
    public function getBase();

    /**
     * Возвращает true, если разрешено создание папок в репозитарии
     *
     * @return boolean
     */
    public function allowsCreateDir();

    /**
     * Возвращает true, если разрешена загрузка файлов в репозитарий
     *
     * @return boolean
     */
    public function allowsUploadFile();

    /**
     * Возвращает true, если разрешено редактирование папки в репозитарии
     *
     * @return boolean
     */
    public function allowsEditDir();

    /**
     * Возвращает true, если разрешено редактирование файла в репозитарии
     *
     * @return boolean
     */
    public function allowsEditFile();

    /**
     * Возвращает true, если разрешено удаление папки из репозитария
     *
     * @return boolean
     */
    public function allowsDeleteDir();

    /**
     * Возвращает true, если разрешено удаление файла из репозитария
     *
     * @return boolean
     */
    public function allowsDeleteFile();

    /**
     * Метод загрузки медиа-файла в хранилище
     *
     * @param string $sourceFilename
     * @param string $destFilename
     * @return boolean
     * @throws SystemException
     */
    public function uploadFile($sourceFilename, $destFilename);

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
    public function uploadAlt($sourceFilename, $destFilename, $width, $height);

    /**
     * Метод обновления ранее загруженного медиа-файла в хранилище
     *
     * @param string $sourceFilename
     * @param string $destFilename
     * @return boolean|object
     * @throws SystemException
     */
    public function updateFile($sourceFilename, $destFilename);

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
    public function updateAlt($sourceFilename, $destFilename, $width, $height);

    /**
     * Метод удаления media-файла из хранилища
     *
     * @param string $filename имя файла
     * @return boolean
     * @throws SystemException
     */
    public function deleteFile($filename);

    /**
     * Метод удаления alt-файла из хранилища
     *
     * @param string $filename имя файла
     * @param int $width
     * @param int $height
     * @return boolean
     * @throws SystemException
     */
    public function deleteAlt($filename, $width, $height);

    /**
     * Возвращает объект с мета-информацией файла (mime-тип, размер и тп)
     *
     * @param $filename
     * @return object
     * @throws SystemException
     */
    public function analyze($filename);

    /**
     * Метод создания директории в репозитарии
     *
     * @param string $dir
     * @return boolean
     * @throws SystemException
     */
    public function createDir($dir);

    /**
     * Метод переименования директории в хранилище
     *
     * @param string $dir
     * @return boolean
     * @throws SystemException
     */
    public function renameDir($dir);

    /**
     * Метод удаления директории из репозитария
     *
     * @param string $dir
     * @throws SystemException
     */
    public function deleteDir($dir);
}